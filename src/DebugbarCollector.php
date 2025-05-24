<?php

namespace Vherbaut\LivewireDebugbar;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class DebugbarCollector
{
    protected array $components = [];
    protected array $events = [];
    protected array $dispatchedEvents = [];
    protected array $performance = [];
    protected bool $enabled = true;

    public function addComponent(string $id, array $data): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->components[$id] = array_merge($this->components[$id] ?? [], $data);
        $this->checkPerformanceThresholds($id, $data);
    }

    public function addEvent(string $componentId, string $event, array $payload = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->events[] = [
            'component_id' => $componentId,
            'event' => $event,
            'payload' => $payload,
            'timestamp' => microtime(true),
            'memory' => memory_get_usage(true),
        ];
    }

    public function addDispatchedEvent(string $componentId, array $eventData): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->dispatchedEvents[] = array_merge([
            'component_id' => $componentId,
            'dispatched_at' => microtime(true),
            'memory' => memory_get_usage(true),
        ], $eventData);
    }

    public function addPerformanceData(string $componentId, string $type, float $time, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->performance[] = [
            'component_id' => $componentId,
            'type' => $type,
            'time' => $time,
            'context' => $context,
            'timestamp' => microtime(true),
        ];
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }

    public function getEventsGroupedByType(): array
    {
        return [
            'lifecycle' => $this->events,
            'dispatched' => $this->dispatchedEvents,
        ];
    }

    public function getPerformance(): array
    {
        return $this->performance;
    }

    public function getComponentsWithAlerts(): array
    {
        $components = [];

        foreach ($this->components as $id => $component) {
            $alerts = [];

            // Alerte trop de propriétés
            if (isset($component['properties']) && count($component['properties']) > config('livewire-debugbar.thresholds.max_properties', 50)) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => 'Trop de propriétés (' . count($component['properties']) . ')',
                ];
            }

            // Alerte taille sérialisée
            if (isset($component['serialized_size']) && $component['serialized_size'] > config('livewire-debugbar.thresholds.max_serialized_size', 10240)) {
                $alerts[] = [
                    'type' => 'error',
                    'message' => 'Données sérialisées trop importantes (' . $this->formatBytes($component['serialized_size']) . ')',
                ];
            }

            // Alerte temps de rendu lent
            $renderTime = $this->getComponentRenderTime($id);
            if ($renderTime > config('livewire-debugbar.thresholds.slow_render_time', 100)) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => 'Rendu lent (' . round($renderTime, 2) . 'ms)',
                ];
            }

            // Alerte trop de requêtes
            if (isset($component['query_count']) && $component['query_count'] > config('livewire-debugbar.thresholds.max_queries', 10)) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => 'Trop de requêtes (' . $component['query_count'] . ')',
                ];
            }

            $components[$id] = array_merge($component, ['alerts' => $alerts]);
        }

        return $components;
    }

    public function getStatistics(): array
    {
        return [
            'components_count' => count($this->components),
            'events_count' => count($this->events),
            'dispatched_events_count' => count($this->dispatchedEvents),
            'performance_entries_count' => count($this->performance),
            'total_render_time' => $this->getTotalRenderTime(),
            'average_render_time' => $this->getAverageRenderTime(),
            'total_queries' => $this->getTotalQueries(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    public function render(): string
    {
        if (!$this->enabled || !config('livewire-debugbar.enabled')) {
            return '';
        }

        return View::make('livewire-debugbar::debugbar', [
            'components' => $this->getComponentsWithAlerts(),
            'events' => $this->getEventsGroupedByType(),
            'performance' => $this->getPerformance(),
            'statistics' => $this->getStatistics(),
            'config' => config('livewire-debugbar'),
        ])->render();
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function clear(): void
    {
        $this->components = [];
        $this->events = [];
        $this->dispatchedEvents = [];
        $this->performance = [];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    protected function checkPerformanceThresholds(string $id, array $data): void
    {
        // Les alertes sont générées dans getComponentsWithAlerts()
        // Cette méthode peut être utilisée pour des actions immédiates
    }

    protected function getComponentRenderTime(string $componentId): float
    {
        $renders = collect($this->performance)
            ->where('component_id', $componentId)
            ->where('type', 'render');

        return $renders->sum('time');
    }

    protected function getTotalRenderTime(): float
    {
        return collect($this->performance)
            ->where('type', 'render')
            ->sum('time');
    }

    protected function getAverageRenderTime(): float
    {
        $renders = collect($this->performance)->where('type', 'render');

        if ($renders->isEmpty()) {
            return 0;
        }

        return $renders->avg('time');
    }

    protected function getTotalQueries(): int
    {
        return collect($this->components)->sum('query_count');
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
