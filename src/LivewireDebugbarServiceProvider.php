<?php

namespace BoutonPlace\LivewireDebugbar;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use BoutonPlace\LivewireDebugbar\Commands\LivewireDebugbarCommand;

class LivewireDebugbarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('LivewireDebugbar')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(LivewireDebugbarCommand::class);
    }
}
