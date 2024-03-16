<?php

namespace CruxAllez\FilamentMeiliBoost\Traits;

use CruxAllez\FilamentMeiliBoost\Providers\DefaultResultsProvider;
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

    public function getSearchResult(array $hit): GlobalSearchResult
    {
        return new GlobalSearchResult(
            title: $this->getSearchTitle($hit),
            url: $this->getSearchUrl($hit),
        );
    }

    public static function getAttributesToHighlight(): array
    {
        return ['*'];
    }

    public static function getHighlightPreTag(): string
    {
        return '<strong class="font-extrabold">';
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

    public static string $resultProvider = DefaultResultsProvider::class;

    public static function getResultProvider(): string {
        return static::$resultProvider;
    }
}
