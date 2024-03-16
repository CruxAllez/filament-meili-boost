<?php

namespace CruxAllez\FilamentMeiliBoost;

use CruxAllez\FilamentMeiliBoost\Providers\MeiliGlobalSearchProvider;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentMeiliBoostPlugin implements Plugin
{
    public function getId(): string
    {
        return 'crux-allez-filament-meili-boost';
    }

    public function register(Panel $panel): void
    {

    }

    /**
     * @throws \Exception
     */
    public function boot(Panel $panel): void
    {
        $panel->globalSearch(MeiliGlobalSearchProvider::class);
    }

    public static function make(): static
    {
        return new static();
    }
}
