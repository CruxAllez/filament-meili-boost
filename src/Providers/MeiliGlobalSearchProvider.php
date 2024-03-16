<?php

namespace CruxAllez\FilamentMeiliBoost\Providers;

use CruxAllez\FilamentMeiliBoost\Contracts\HasGlobalSearch;
use Filament\Facades\Filament;
use Filament\GlobalSearch\Contracts\GlobalSearchProvider;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

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

        foreach ($resources as $resource) {
            /** @var Resource $resource */
            /** @var Model $model */
            $model = $resource::getModel();

            if (!method_exists($model, 'search')) {
                $resourceResults = $resource::getGlobalSearchResults($query);

                if (!$resourceResults->count()) {
                    continue;
                }

                $builder->category($resource::getPluralModelLabel(), $resourceResults);
                continue;
            }

            if (!(app($model) instanceof HasGlobalSearch)) {
                throw new \Exception('The ' . $model . ' must implement the HasGlobalSearch interface.');
            }

            /** @var HasGlobalSearch $model */
            $scoutQuery = $model::search(
                $query,
                fn ($meiliSearch, string $query, array $options) => $model::meiliCallback($meiliSearch, $query, $options)
            );

            $scoutQuery = $model::modifyMeiliSearchQuery($scoutQuery, $query);

            $results = $scoutQuery->raw();

            dd($model, $results);
        }

        /*$result = new GlobalSearchResult(title: 'test', url: 'test');

        $builder->category('Tickets', [$result]);*/

        return $builder;
    }
}
