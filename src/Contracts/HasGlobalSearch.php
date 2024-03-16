<?php

namespace CruxAllez\FilamentMeiliBoost\Contracts;

use Filament\GlobalSearch\GlobalSearchResult;
use Laravel\Scout\Builder;

interface HasGlobalSearch
{
    public static function modifyMeiliSearchQuery(Builder $scoutQuery, string $searchQuery): Builder;

    public static function getGlobalSearchGroupName() : string;

    public function getSearchResult(array $formatted): GlobalSearchResult;

    public static function meiliCallback($meiliSearch, string $query, array $options);
}
