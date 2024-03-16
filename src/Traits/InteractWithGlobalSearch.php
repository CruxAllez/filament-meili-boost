<?php

namespace CruxAllez\FilamentMeiliBoost\Traits;

use Filament\GlobalSearch\GlobalSearchResult;
use Laravel\Scout\Builder;
use Meilisearch\Endpoints\Indexes;

trait InteractWithGlobalSearch
{
    public static function modifyMeiliSearchQuery(Builder $scoutQuery, string $searchQuery): Builder
    {
        return $scoutQuery;
    }

    public static function getGlobalSearchGroupName() : string
    {
        return class_basename(static::class);
    }

    public function getSearchTitle() {

    }

    public function getSearchResult(array $formatted): GlobalSearchResult
    {
        return new GlobalSearchResult(
            title: 'test',

        );
    }

    public static function getAttributesToHighlight(): array
    {
        return ['*'];
    }

    public static function getHighlightPreTag(): string
    {
        return '<strong>';
    }

    public static function getHighlightPostTag(): string
    {
        return '</strong>';
    }

    public static function meiliCallback(Indexes $meiliSearch, string $query, array $options): array|\Meilisearch\Search\SearchResult
    {
        $options['attributesToHighlight'] = self::getAttributesToHighlight();
        $options['highlightPreTag'] = self::getHighlightPreTag();
        $options['highlightPostTag'] = self::getHighlightPostTag();

        return $meiliSearch->search($query, $options);
    }
}
