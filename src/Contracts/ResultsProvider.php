<?php

namespace CruxAllez\FilamentMeiliBoost\Contracts;

use Laravel\Scout\Builder;

interface ResultsProvider
{
    public function __construct(Builder $scoutQuery, string $model, string|null $resource);

    public function getResults(): array;
}
