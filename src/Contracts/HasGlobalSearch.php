<?php

namespace CruxAllez\FilamentMeiliBoost\Contracts;

use Filament\GlobalSearch\GlobalSearchResult;
use Illuminate\Contracts\Support\Htmlable;
use Laravel\Scout\Builder;
use Meilisearch\Endpoints\Indexes;

interface HasGlobalSearch
{
    public static function modifyMeiliSearchQuery(Builder $scoutQuery, string $searchQuery): Builder;

    public static function getGlobalSearchGroupName() : string;

    public function getSearchResult(array $hit): GlobalSearchResult;

    public static function meiliCallback(Indexes $meiliSearch, string $query, array $options): array|\Meilisearch\Search\SearchResult;

    public static function getResultProvider(): string;

    public function getSearchTitle(array $hit): string|Htmlable;

    public function getSearchUrl(array $hit): string;
}
