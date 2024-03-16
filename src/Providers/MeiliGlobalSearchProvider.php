<?php

namespace CruxAllez\FilamentMeiliBoost\Providers;

use CruxAllez\FilamentMeiliBoost\Contracts\HasGlobalSearch;
use CruxAllez\FilamentMeiliBoost\Contracts\ResultsProvider;
use Filament\Facades\Filament;
use Filament\GlobalSearch\Contracts\GlobalSearchProvider;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use SplFileInfo;

class MeiliGlobalSearchProvider implements GlobalSearchProvider
{
    /**
     * @throws \Exception
     */
    public function getResults(string $query): ?GlobalSearchResults
    {
        $resources = collect(Filament::getResources())
            ->filter(function (string $resource): bool {
                /** @var Resource $resource */
                return $resource::canGloballySearch();
            })->toArray();

        $builder = GlobalSearchResults::make();

        $alreadySearchedModels = [];

        foreach ($resources as $resource) {
            /** @var Model $model */
            $model = $resource::getModel();
            $alreadySearchedModels[] = $model;
            $modelReflection = new \ReflectionClass($model);

            if (!$modelReflection->hasMethod('search')) {
                $resourceResults = $resource::getGlobalSearchResults($query);

                if (!$resourceResults->count()) {
                    continue;
                }

                $builder->category($resource::getPluralModelLabel(), $resourceResults);
                continue;
            }

            $results = $this->_getResultsForModel($model, $query, $resource);

            if (count($results) <= 0) {
                continue;
            }

            /** @var HasGlobalSearch $model */
            $group = $model::getGlobalSearchGroupName() ?? $resource::getPluralModelLabel();
            $builder->category($group, $results);
        }

        $models = $this->getSearchAbleModels($alreadySearchedModels);

        return $builder;
    }

    private function getSearchAbleModels(array $ignore)
    {
        $files = File::allFiles(app()->basePath() . '/app/Models');
        // to get all the model classes
        return collect($files)
            ->map(function (SplFileInfo $file) {
                $filename = $file->getRelativePathname();

                // assume model name is equal to file name
                /* making sure it is a php file*/
                if (!str_ends_with($filename, '.php')) {
                    return null;
                }
                // removing .php
                return substr($filename, 0, -4);

            })->filter(function (?string $classname) use($ignore) {
                if ($classname === null) {
                    return false;
                }

                $modelName = $this->modelNamespacePrefix() . $classname;

                if (in_array($modelName, $ignore)) {
                    return false;
                }

                // using reflection class to obtain class info dynamically
                $reflection = new \ReflectionClass($modelName);

                // making sure the class extended eloquent model
                $isModel = $reflection->isSubclassOf(Model::class);


                $searchable = $reflection->hasMethod('search');

                // filter model that has the searchable trait and not in exclude array
                return $isModel && $searchable;

            })->values();


    }

    /**
     * @param string $model
     * @param string $query
     * @param string $resource
     * @return array
     * @throws \ReflectionException
     */
    private function _getResultsForModel(string $model, string $query, string $resource): array
    {
        $modelReflection = new \ReflectionClass($model);
        if (!($modelReflection->implementsInterface(HasGlobalSearch::class))) {
            throw new \Exception('The ' . $model . ' must implement the HasGlobalSearch interface.');
        }

        /** @var HasGlobalSearch $model */
        $scoutQuery = $model::search(
            $query,
            fn($meiliSearch, string $query, array $options) => $model::meiliCallback($meiliSearch, $query, $options)
        );

        $scoutQuery = $model::modifyMeiliSearchQuery($scoutQuery, $query);

        $providerClass = $model::getResultProvider();

        /** @var ResultsProvider $provider */
        $provider = new $providerClass($scoutQuery, $model, $resource);

        return $provider->getResults();
    }

    private function modelNamespacePrefix(): string
    {
        return app()->getNamespace() . 'Models\\';
    }

}
