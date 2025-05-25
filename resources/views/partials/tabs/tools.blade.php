{{-- Developer Tools Tab --}}
<div x-show="activeTab === 'tools'" class="livewire-debugbar__panel livewire-debugbar__panel--active">
    <div class="tools-container">
        
        {{-- Quick Actions Bar --}}
        <div class="bg-gray-950 rounded-lg border border-gray-700 p-4 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <button @click="refreshAllComponents()" 
                            class="tool-button tool-button-primary">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.refresh_all') ?? 'Refresh All' }}
                    </button>
                    
                    <button @click="clearAllData()" 
                            class="tool-button tool-button-secondary">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.clear_all') ?? 'Clear All' }}
                    </button>
                    
                    <button @click="exportAllData()" 
                            class="tool-button tool-button-secondary">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.export_all') ?? 'Export All' }}
                    </button>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">{{ __('livewire-debugbar::debugbar.tools.auto_refresh') ?? 'Auto Refresh' }}</span>
                    <label class="setting-toggle">
                        <input type="checkbox" x-model="autoRefresh" @change="toggleAutoRefresh()">
                        <span class="setting-toggle-track"></span>
                        <span class="setting-toggle-thumb"></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Time Travel Debugging --}}
        <div class="tool-card mb-6">
            <div class="tool-card-header">
                <div class="tool-card-title">
                    <h3>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.time_travel') ?? 'Time Travel Debugging' }}
                    </h3>
                    <select x-model="activeComponentId" 
                            class="bg-gray-950 border border-gray-600 rounded-lg px-3 py-1.5 text-sm text-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">{{ __('livewire-debugbar::debugbar.tools.select_component') ?? 'Select Component' }}</option>
                        <template x-for="[id, component] in Object.entries(components)" :key="id">
                            <option :value="id" x-text="`${component.name} (${id})`"></option>
                        </template>
                    </select>
                </div>
                <p class="tool-card-description">{{ __('livewire-debugbar::debugbar.tools.time_travel_desc') ?? 'Browse and restore component state history' }}</p>
            </div>
            
            <div class="tool-card-body" x-show="activeComponentId && stateHistory[activeComponentId]">
                <div class="time-travel-container">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm text-gray-400">
                            <span x-text="stateHistory[activeComponentId]?.length || 0"></span> {{ __('livewire-debugbar::debugbar.tools.snapshots') ?? 'snapshots' }}
                        </div>
                        <button @click="clearComponentHistory(activeComponentId)" 
                                class="text-xs text-gray-500 hover:text-gray-300">
                            {{ __('livewire-debugbar::debugbar.tools.clear_history') ?? 'Clear History' }}
                        </button>
                    </div>
                    
                    <div class="time-travel-timeline">
                        <div class="timeline-wrapper">
                            <template x-for="(snapshot, index) in (stateHistory[activeComponentId] || []).slice().reverse()" :key="snapshot.id">
                                <div class="timeline-item">
                                    <div class="timeline-marker" :class="{ 'active': index === 0 }"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <div>
                                                <div class="timeline-time" x-text="new Date(snapshot.timestamp).toLocaleTimeString()"></div>
                                                <div class="timeline-metadata">
                                                    <span x-text="`${Object.keys(snapshot.state).length} properties`"></span>
                                                    <span>•</span>
                                                    <span x-text="`${JSON.stringify(snapshot.state).length} bytes`"></span>
                                                </div>
                                            </div>
                                            <div class="timeline-actions">
                                                <button @click="compareWithCurrent(activeComponentId, snapshot.id)"
                                                        class="tool-button tool-button-secondary text-xs px-2 py-1"
                                                        data-tooltip="Compare with current">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                                    </svg>
                                                </button>
                                                <button @click="viewSnapshotDetails(snapshot)"
                                                        class="tool-button tool-button-secondary text-xs px-2 py-1"
                                                        data-tooltip="View details">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                                <button @click="restoreComponentState(activeComponentId, snapshot.id)"
                                                        class="tool-button tool-button-primary text-xs px-2 py-1"
                                                        data-tooltip="Restore this state">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Component Inspector --}}
        <div class="tool-card mb-6">
            <div class="tool-card-header">
                <div class="tool-card-title">
                    <h3>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"/>
                            <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.component_inspector') ?? 'Component Inspector' }}
                    </h3>
                </div>
                <p class="tool-card-description">{{ __('livewire-debugbar::debugbar.tools.component_inspector_desc') ?? 'Deep inspection and manipulation of components' }}</p>
            </div>
            
            <div class="tool-card-body">
                <div class="inspector-container">
                    <div class="inspector-search mb-4">
                        <input type="text" 
                               x-model="inspectorSearch" 
                               @input="filterInspectorResults()"
                               placeholder="{{ __('livewire-debugbar::debugbar.tools.search_components') ?? 'Search components...' }}"
                               class="w-full">
                        <svg class="inspector-search-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    
                    <div class="inspector-results">
                        <template x-for="[id, component] in getFilteredComponents()" :key="id">
                            <div class="inspector-item">
                                <div class="inspector-item-header">
                                    <div>
                                        <div class="inspector-item-name" x-text="component.name"></div>
                                        <div class="text-xs text-gray-500" x-text="`ID: ${id}`"></div>
                                    </div>
                                    <div class="inspector-item-actions">
                                        <button @click="inspectComponent(id)" 
                                                class="tool-button tool-button-secondary text-xs px-2 py-1"
                                                data-tooltip="Inspect">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                        <button @click="cloneComponent(id)" 
                                                class="tool-button tool-button-secondary text-xs px-2 py-1"
                                                data-tooltip="Clone">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"/>
                                                <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"/>
                                            </svg>
                                        </button>
                                        <button @click="benchmarkComponent(id)" 
                                                class="tool-button tool-button-secondary text-xs px-2 py-1"
                                                data-tooltip="Benchmark">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 grid grid-cols-3 gap-2 text-xs text-gray-500">
                                    <div>Properties: <span class="text-gray-300" x-text="Object.keys(component.properties || {}).length"></span></div>
                                    <div>State: <span class="text-gray-300" x-text="component.state"></span></div>
                                    <div>Size: <span class="text-gray-300" x-text="getComponentDataSize(component) + ' KB'"></span></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Code Generator --}}
        <div class="tool-card mb-6">
            <div class="tool-card-header">
                <div class="tool-card-title">
                    <h3>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.code_generator') ?? 'Code Generator' }}
                    </h3>
                </div>
                <p class="tool-card-description">{{ __('livewire-debugbar::debugbar.tools.code_generator_desc') ?? 'Generate boilerplate code for your components' }}</p>
            </div>
            
            <div class="tool-card-body">
                <div class="code-generator-container">
                    <div class="generator-option" @click="generateCode('test')" :class="{ 'selected': generatorType === 'test' }">
                        <svg class="generator-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <h4 class="generator-title">{{ __('livewire-debugbar::debugbar.tools.generate_tests') ?? 'Generate Tests' }}</h4>
                        <p class="generator-description">{{ __('livewire-debugbar::debugbar.tools.generate_tests_desc') ?? 'Create PHPUnit tests for components' }}</p>
                    </div>
                    
                    <div class="generator-option" @click="generateCode('factory')" :class="{ 'selected': generatorType === 'factory' }">
                        <svg class="generator-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                        <h4 class="generator-title">{{ __('livewire-debugbar::debugbar.tools.generate_factory') ?? 'Generate Factory' }}</h4>
                        <p class="generator-description">{{ __('livewire-debugbar::debugbar.tools.generate_factory_desc') ?? 'Create model factories from data' }}</p>
                    </div>
                    
                    <div class="generator-option" @click="generateCode('form')" :class="{ 'selected': generatorType === 'form' }">
                        <svg class="generator-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 5a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <h4 class="generator-title">{{ __('livewire-debugbar::debugbar.tools.generate_form') ?? 'Generate Form' }}</h4>
                        <p class="generator-description">{{ __('livewire-debugbar::debugbar.tools.generate_form_desc') ?? 'Create form components from properties' }}</p>
                    </div>
                    
                    <div class="generator-option" @click="generateCode('migration')" :class="{ 'selected': generatorType === 'migration' }">
                        <svg class="generator-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/>
                            <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/>
                            <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/>
                        </svg>
                        <h4 class="generator-title">{{ __('livewire-debugbar::debugbar.tools.generate_migration') ?? 'Generate Migration' }}</h4>
                        <p class="generator-description">{{ __('livewire-debugbar::debugbar.tools.generate_migration_desc') ?? 'Create migrations from component data' }}</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <select x-model="selectedComponentForGeneration" 
                            class="w-full bg-gray-950 border border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-300">
                        <option value="">{{ __('livewire-debugbar::debugbar.tools.select_component') ?? 'Select Component' }}</option>
                        <template x-for="[id, component] in Object.entries(components)" :key="id">
                            <option :value="id" x-text="`${component.name}`"></option>
                        </template>
                    </select>
                    
                    <button @click="executeCodeGeneration()" 
                            :disabled="!selectedComponentForGeneration || !generatorType"
                            class="tool-button tool-button-success w-full mt-3">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.generate_code') ?? 'Generate Code' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Performance Profiler --}}
        <div class="tool-card mb-6">
            <div class="tool-card-header">
                <div class="tool-card-title">
                    <h3>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.performance_profiler') ?? 'Performance Profiler' }}
                    </h3>
                    <button @click="runPerformanceProfile()" 
                            class="tool-button tool-button-primary text-xs">
                        {{ __('livewire-debugbar::debugbar.tools.run_profile') ?? 'Run Profile' }}
                    </button>
                </div>
                <p class="tool-card-description">{{ __('livewire-debugbar::debugbar.tools.performance_profiler_desc') ?? 'Analyze component performance and bottlenecks' }}</p>
            </div>
            
            <div class="tool-card-body">
                <div class="profiler-container">
                    <div class="profiler-metrics">
                        <div class="metric-card">
                            <div class="metric-value" :class="getMetricClass('render', getAverageRenderTime())" x-text="getAverageRenderTime() + 'ms'"></div>
                            <div class="metric-label">{{ __('livewire-debugbar::debugbar.tools.avg_render') ?? 'Avg Render' }}</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" :class="getMetricClass('memory', getTotalDataSize())" x-text="getTotalDataSize() + 'KB'"></div>
                            <div class="metric-label">{{ __('livewire-debugbar::debugbar.tools.memory_usage') ?? 'Memory Usage' }}</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" :class="getMetricClass('components', Object.keys(components).length)" x-text="Object.keys(components).length"></div>
                            <div class="metric-label">{{ __('livewire-debugbar::debugbar.tools.components') ?? 'Components' }}</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value" :class="getMetricClass('events', getEvents().length)" x-text="getEvents().length"></div>
                            <div class="metric-label">{{ __('livewire-debugbar::debugbar.tools.events_fired') ?? 'Events Fired' }}</div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h4 class="text-sm font-semibold text-gray-300 mb-2">{{ __('livewire-debugbar::debugbar.tools.hotspots') ?? 'Performance Hotspots' }}</h4>
                        <div class="space-y-2">
                            <template x-for="hotspot in getPerformanceHotspots()" :key="hotspot.id">
                                <div class="p-3 bg-gray-900 rounded-lg border border-gray-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-mono text-sm text-yellow-400" x-text="hotspot.component"></div>
                                            <div class="text-xs text-gray-500" x-text="hotspot.issue"></div>
                                        </div>
                                        <button @click="optimizeComponent(hotspot.id)" 
                                                class="tool-button tool-button-secondary text-xs px-2 py-1">
                                            {{ __('livewire-debugbar::debugbar.tools.optimize') ?? 'Optimize' }}
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Query Analyzer --}}
        <div class="tool-card mb-6">
            <div class="tool-card-header">
                <div class="tool-card-title">
                    <h3>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/>
                            <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/>
                            <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.query_analyzer') ?? 'Query Analyzer' }}
                    </h3>
                </div>
                <p class="tool-card-description">{{ __('livewire-debugbar::debugbar.tools.query_analyzer_desc') ?? 'Analyze database queries from components' }}</p>
            </div>
            
            <div class="tool-card-body">
                <div class="query-builder-container">
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/>
                            <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/>
                            <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/>
                        </svg>
                        <p class="text-sm">{{ __('livewire-debugbar::debugbar.tools.query_analyzer_info') ?? 'Query analysis requires Laravel Debugbar integration' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Console Output --}}
        <div class="tool-card">
            <div class="tool-card-header">
                <div class="tool-card-title">
                    <h3>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.tools.console') ?? 'Console Output' }}
                    </h3>
                    <button @click="clearConsole()" 
                            class="text-xs text-gray-500 hover:text-gray-300">
                        {{ __('livewire-debugbar::debugbar.tools.clear') ?? 'Clear' }}
                    </button>
                </div>
            </div>
            
            <div class="tool-card-body p-0">
                <div class="console-container" x-ref="console">
                    <template x-for="log in consoleLogs" :key="log.id">
                        <div class="console-line" :class="log.type">
                            <span class="console-timestamp" x-text="log.time"></span>
                            <span x-text="log.message"></span>
                        </div>
                    </template>
                    <div x-show="consoleLogs.length === 0" class="console-line info">
                        <span class="console-timestamp">{{ date('H:i:s') }}</span>
                        <span>{{ __('livewire-debugbar::debugbar.tools.console_ready') ?? 'Console ready...' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>