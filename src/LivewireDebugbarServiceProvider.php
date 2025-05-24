<?php

namespace Vherbaut\LivewireDebugbar;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vherbaut\LivewireDebugbar\Commands\LivewireDebugbarCommand;
use Vherbaut\LivewireDebugbar\Http\Middleware\DebugbarMiddleware;
use Vherbaut\LivewireDebugbar\Listeners\ComponentListener;

class LivewireDebugbarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('livewire-debugbar')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('web')
            ->hasCommand(LivewireDebugbarCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(DebugbarCollector::class);
        $this->app->singleton(AssetManager::class);
    }

    public function packageBooted(): void
    {
        if ($this->shouldLoadDebugbar()) {
            $this->registerMiddleware();
            $this->registerBladeDirectives();

            // 🔥 Bootstrap de l'AssetManager
            AssetManager::boot();
        }
    }

    protected function shouldLoadDebugbar(): bool
    {
        return config('app.debug', false) && config('livewire-debugbar.enabled', true);
    }

    protected function registerMiddleware(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', DebugbarMiddleware::class);
    }


    protected function registerBladeDirectives(): void
    {
        Blade::directive('livewireDebugbar', function () {
            return "<?php echo app('".DebugbarCollector::class."')->render(); ?>";
        });
    }
}
