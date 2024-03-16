<?php

namespace CruxAllez\FilamentMeiliBoost\Providers;

use CruxAllez\FilamentMeiliBoost\Contracts\HasGlobalSearch;
use CruxAllez\FilamentMeiliBoost\Contracts\ResultsProvider;
use Laravel\Scout\Builder;

class DefaultResultsProvider implements ResultsProvider
{
    public readonly Builder $scoutQuery;

    public readonly string $model;

    public readonly string|null $resource;

    public function __construct(Builder $scoutQuery, $model, string|null $resource)
    {
        $this->scoutQuery = $scoutQuery;
        $this->model = $model;
        $this->resource = $resource;
    }

    public function getResults(): array
    {
        $results = $this->scoutQuery->raw();

        $hits = $results['hits'];

        /** @var HasGlobalSearch $modelClass */
        $modelClass = $this->model;

        return collect($hits)
            ->map(function ($hit) use ($modelClass) {
                $model = new $modelClass($hit);
                return $model->getSearchResult($hit);
            })->toArray();
    }
}
