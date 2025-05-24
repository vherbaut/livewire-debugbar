<?php

namespace Vherbaut\LivewireDebugbar\Listeners;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use ReflectionClass;
use ReflectionProperty;
use Vherbaut\LivewireDebugbar\DebugbarCollector;

class ComponentListener
{
    protected DebugbarCollector $collector;
    protected array $startTimes = [];
    protected array $queryCountBefore = [];

    public function __construct(DebugbarCollector $collector)
    {
        $this->collector = $collector;
    }

    public function onMount(Component $component, array $params): void
    {
        $this->startTimer($component, 'mount');
        $this->startQueryTracking($component);

        // Debug pour vérifier que le listener est appelé
        logger()->info('LivewireDebugbar: Component mounted', [
            'id' => $component->getId(),
            'name' => $component->getName(),
            'class' => get_class($component),
        ]);

        $this->collector->addComponent($component->getId(), [
            'name' => $component->getName(),
            'class' => get_class($component),
            'state' => 'mounting',
            'mount_params' => $params,
            'properties' => $this->extractProperties($component),
            'locked_properties' => $this->getLockedProperties($component),
            'computed_properties' => $this->getComputedProperties($component),
            'listeners' => $this->getListeners($component),
            'created_at' => microtime(true),
        ]);

        $this->collector->addEvent($component->getId(), 'mount', $params);
    }

    public function onHydrate(Component $component, array $request): void
    {
        $this->startTimer($component, 'hydrate');

        $this->collector->addComponent($component->getId(), [
            'state' => 'hydrating',
            'request' => $request,
            'fingerprint' => $request['fingerprint'] ?? null,
            'hydrated_at' => microtime(true),
        ]);

        $this->collector->addEvent($component->getId(), 'hydrate', $request);
    }

    public function onUpdating(Component $component, string $property, $value): void
    {
        $this->collector->addEvent($component->getId(), 'updating', [
            'property' => $property,
            'old_value' => data_get($component, $property),
            'new_value' => $value,
        ]);
    }

    public function onUpdated(Component $component, string $property, $value): void
    {
        $this->collector->addComponent($component->getId(), [
            'properties' => $this->extractProperties($component),
            'updated_at' => microtime(true),
        ]);

        $this->collector->addEvent($component->getId(), 'updated', [
            'property' => $property,
            'value' => $value,
        ]);
    }

    public function onRendered(Component $component, string $view): void
    {
        $this->endTimer($component, 'render');
        $this->endQueryTracking($component);

        $serializedSize = strlen(serialize($component->all()));

        $this->collector->addComponent($component->getId(), [
            'state' => 'rendered',
            'view' => $view,
            'serialized_size' => $serializedSize,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'rendered_at' => microtime(true),
        ]);

        $this->collector->addEvent($component->getId(), 'rendered', [
            'view' => $view,
            'size' => $serializedSize,
        ]);
    }

    public function onDehydrate(Component $component, array $response): void
    {
        $this->endTimer($component, 'dehydrate');

        // Capturer les événements dispatchés
        $this->captureDispatchedEvents($component, $response);

        $this->collector->addComponent($component->getId(), [
            'state' => 'dehydrated',
            'response' => $this->sanitizeResponse($response),
            'dehydrated_at' => microtime(true),
        ]);

        $this->collector->addEvent($component->getId(), 'dehydrate', $this->sanitizeResponse($response));
    }

    protected function extractProperties(Component $component): array
    {
        $properties = [];
        $reflection = new ReflectionClass($component);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $name = $property->getName();
                $value = $property->getValue($component);
                $properties[$name] = [
                    'value' => $value,
                    'type' => gettype($value),
                    'size' => strlen(serialize($value)),
                    'is_locked' => in_array($name, $this->getLockedProperties($component)),
                ];
            }
        }

        return $properties;
    }

    protected function getLockedProperties(Component $component): array
    {
        $locked = [];

        if (method_exists($component, 'getLockedPropertyNames')) {
            $locked = $component->getLockedPropertyNames();
        }

        return $locked;
    }

    protected function getComputedProperties(Component $component): array
    {
        $computed = [];
        $reflection = new ReflectionClass($component);

        foreach ($reflection->getMethods() as $method) {
            $name = $method->getName();
            if (str_starts_with($name, 'get') && str_ends_with($name, 'Property')) {
                $propertyName = lcfirst(substr($name, 3, -8));
                $computed[] = $propertyName;
            }
        }

        return $computed;
    }

    protected function getListeners(Component $component): array
    {
        $listeners = [];

        // Ne pas appeler getListeners() qui n'existe pas dans Livewire 3
        if (property_exists($component, 'listeners')) {
            $listeners = $component->listeners ?? [];
        }

        return $listeners;
    }

    protected function startTimer(Component $component, string $type): void
    {
        $this->startTimes[$component->getId()][$type] = microtime(true);
    }

    protected function endTimer(Component $component, string $type): void
    {
        $id = $component->getId();

        if (isset($this->startTimes[$id][$type])) {
            $time = (microtime(true) - $this->startTimes[$id][$type]) * 1000;

            $this->collector->addPerformanceData($id, $type, $time);

            unset($this->startTimes[$id][$type]);
        }
    }

    protected function startQueryTracking(Component $component): void
    {
        $this->queryCountBefore[$component->getId()] = count(DB::getQueryLog());
        DB::enableQueryLog();
    }

    protected function endQueryTracking(Component $component): void
    {
        $id = $component->getId();
        $queries = collect(DB::getQueryLog());

        if (isset($this->queryCountBefore[$id])) {
            $beforeCount = $this->queryCountBefore[$id];
            $newQueries = $queries->slice($beforeCount);

            $this->collector->addComponent($id, [
                'queries' => $newQueries->take(20)->toArray(), // Limiter à 20 pour la performance
                'query_count' => $newQueries->count(),
                'query_time' => $newQueries->sum('time'),
            ]);

            unset($this->queryCountBefore[$id]);
        }
    }

    protected function captureDispatchedEvents(Component $component, array $response): void
    {
        // Capturer les événements dans la réponse Livewire
        if (isset($response['effects']['dispatches'])) {
            foreach ($response['effects']['dispatches'] as $dispatch) {
                $this->collector->addDispatchedEvent($component->getId(), [
                    'event' => $dispatch['event'] ?? 'unknown',
                    'params' => $dispatch['params'] ?? [],
                    'to' => $dispatch['to'] ?? null,
                    'self' => $dispatch['self'] ?? false,
                    'component' => $dispatch['component'] ?? null,
                    'timestamp' => microtime(true),
                ]);
            }
        }

        // Capturer les événements émis (dispatch global)
        if (isset($response['effects']['emits'])) {
            foreach ($response['effects']['emits'] as $emit) {
                $this->collector->addDispatchedEvent($component->getId(), [
                    'event' => $emit['event'] ?? 'unknown',
                    'params' => $emit['params'] ?? [],
                    'to' => 'global',
                    'self' => false,
                    'component' => null,
                    'timestamp' => microtime(true),
                ]);
            }
        }

        // Capturer les événements browser (dispatchBrowserEvent)
        if (isset($response['effects']['dispatches-browser-event'])) {
            foreach ($response['effects']['dispatches-browser-event'] as $browserEvent) {
                $this->collector->addDispatchedEvent($component->getId(), [
                    'event' => $browserEvent['event'] ?? 'unknown',
                    'params' => $browserEvent['params'] ?? [],
                    'to' => 'browser',
                    'self' => false,
                    'component' => null,
                    'timestamp' => microtime(true),
                ]);
            }
        }
    }

    protected function sanitizeResponse(array $response): array
    {
        // Retirer les données sensibles ou trop volumineuses
        $sanitized = $response;

        // Limiter la taille des données pour éviter les problèmes de mémoire
        if (isset($sanitized['serverMemo']['data'])) {
            $sanitized['serverMemo']['data'] = $this->limitArraySize($sanitized['serverMemo']['data'], 100);
        }

        return $sanitized;
    }

    protected function limitArraySize(array $array, int $maxKeys): array
    {
        if (count($array) <= $maxKeys) {
            return $array;
        }

        return array_slice($array, 0, $maxKeys, true) + ['...' => '(truncated)'];
    }
}
