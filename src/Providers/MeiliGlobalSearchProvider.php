<?php

namespace CruxAllez\FilamentMeiliBoost\Providers;

use Filament\Facades\Filament;
use Filament\GlobalSearch\Contracts\GlobalSearchProvider;
use Filament\GlobalSearch\DefaultGlobalSearchProvider;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

class MeiliGlobalSearchProvider implements GlobalSearchProvider
{

    //

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

                if (! $resourceResults->count()) {
                    continue;
                }

                $builder->category($resource::getPluralModelLabel(), $resourceResults);
            }


            $query = $model;


            $meiliResults = $model::search(
                $query,
                function ($meiliSearch, string $query, array $options) {
                    $options['attributesToHighlight'] = ['*'];
                    $options['highlightPreTag'] = '<strong>';
                    $options['highlightPostTag'] = '</strong>';

                    return $meiliSearch->search($query, $options);
                }
            )->raw();

            dd($model, $meiliResults, $meiliResults );
        }

        $result = new GlobalSearchResult(title: 'test', url: 'test');

        $builder->category('Tickets', [$result]);

        return $builder;
    }
}
