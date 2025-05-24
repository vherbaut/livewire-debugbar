<?php

namespace Vherbaut\LivewireDebugbar\Commands;

use Illuminate\Console\Command;

class LivewireDebugbarCommand extends Command
{
    public $signature = 'livewire-debugbar:status';

    public $description = 'Show Livewire Debugbar status and configuration';

    /**
     * @return int
     */
    public function handle(): int
    {
        $this->info('Livewire Debugbar Status');
        $this->line('');

        // Configuration status
        $enabled = config('livewire-debugbar.enabled', false);
        $debug = config('app.debug', false);
        $hotReload = config('livewire-debugbar.hot_reload.enabled', false);

        $this->table(
            ['Setting', 'Value', 'Status'],
            [
                ['APP_DEBUG', $debug ? 'true' : 'false', $debug ? '✅' : '❌'],
                ['DEBUGBAR_ENABLED', $enabled ? 'true' : 'false', $enabled ? '✅' : '❌'],
                ['HOT_RELOAD', $hotReload ? 'true' : 'false', $hotReload ? '✅' : '❌'],
                ['Position', config('livewire-debugbar.position', 'bottom'), '✅'],
            ]
        );

        $this->line('');

        // Watch paths
        $watchPaths = config('livewire-debugbar.hot_reload.watch_paths', []);
        if ($watchPaths) {
            $this->info('Watched Paths:');
            foreach ($watchPaths as $path) {
                $fullPath = base_path($path);
                $exists = is_dir($fullPath);
                $this->line("  {$path} " . ($exists ? '✅' : '❌'));
            }
        }

        $this->line('');

        // Performance thresholds
        $thresholds = config('livewire-debugbar.thresholds', []);
        $this->info('Performance Thresholds:');
        $this->table(
            ['Metric', 'Threshold'],
            [
                ['Max Properties', $thresholds['max_properties'] ?? 50],
                ['Max Serialized Size', $this->formatBytes($thresholds['max_serialized_size'] ?? 10240)],
                ['Slow Render Time', ($thresholds['slow_render_time'] ?? 100) . 'ms'],
                ['Max Queries', $thresholds['max_queries'] ?? 10],
            ]
        );

        $this->line('');

        if ($enabled && $debug) {
            $this->info('✅ Livewire Debugbar is active and ready!');
        } else {
            $this->warn('⚠️  Livewire Debugbar is not active.');
            if (!$debug) {
                $this->line('   Set APP_DEBUG=true to enable debugging.');
            }
            if (!$enabled) {
                $this->line('   Set LIVEWIRE_DEBUGBAR_ENABLED=true to enable the debugbar.');
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
