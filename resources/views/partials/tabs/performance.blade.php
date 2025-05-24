{{-- Performance Tab --}}
<div x-show="activeTab === 'performance'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="ldb-performance-container">

    {{-- Performance Header --}}
    <div class="ldb-performance-header">
        <h2 class="ldb-performance-title">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            {{ __('livewire-debugbar::debugbar.performance.overview') }}
        </h2>

        <div class="ldb-performance-actions">
            {{-- Real-time indicator --}}
            <div class="ldb-realtime-indicator">
                <span class="ldb-realtime-dot"></span>
                {{ __('livewire-debugbar::debugbar.performance.real_time') }}
            </div>

            {{-- Export button --}}
            <button @click="exportPerformanceData()" class="ldb-btn ldb-btn--secondary ldb-btn--sm">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                {{ __('livewire-debugbar::debugbar.performance.export_report') }}
            </button>
        </div>
    </div>

    {{-- Key Metrics Grid --}}
    <div class="ldb-metrics-grid">
        {{-- Active Components --}}
        <div class="ldb-metric-card ldb-metric-card--components">
            <div class="ldb-metric-value" x-text="Object.keys(components).length"></div>
            <div class="ldb-metric-label">{{ __('livewire-debugbar::debugbar.performance.active_components') }}</div>
        </div>

        {{-- Total Properties --}}
        <div class="ldb-metric-card ldb-metric-card--properties">
            <div class="ldb-metric-value" x-text="getTotalProperties()"></div>
            <div class="ldb-metric-label">{{ __('livewire-debugbar::debugbar.performance.total_properties') }}</div>
        </div>

        {{-- Memory Usage --}}
        <div class="ldb-metric-card ldb-metric-card--memory">
            <div class="ldb-metric-value" x-text="getTotalDataSize() + ' KB'"></div>
            <div class="ldb-metric-label">{{ __('livewire-debugbar::debugbar.performance.data_size') }}</div>
        </div>

        {{-- Events Fired --}}
        <div class="ldb-metric-card ldb-metric-card--events">
            <div class="ldb-metric-value" x-text="getEvents().length"></div>
            <div class="ldb-metric-label">{{ __('livewire-debugbar::debugbar.performance.events_fired') }}</div>
        </div>

        {{-- Database Queries --}}
        <div class="ldb-metric-card ldb-metric-card--queries">
            <div class="ldb-metric-value" x-text="getQueryCount ? getQueryCount() : 'N/A'"></div>
            <div class="ldb-metric-label">{{ __('livewire-debugbar::debugbar.performance.database_queries') }}</div>
        </div>

        {{-- Average Render Time --}}
        <div class="ldb-metric-card ldb-metric-card--time">
            <div class="ldb-metric-value" x-text="(getAverageRenderTime ? getAverageRenderTime() : '0') + ' ms'"></div>
            <div class="ldb-metric-label">{{ __('livewire-debugbar::debugbar.performance.avg_render_time') }}</div>
        </div>
    </div>

    {{-- Component Analysis --}}
    <div class="ldb-analysis-container">
        <div class="ldb-analysis-header">
            <h3 class="ldb-analysis-title">{{ __('livewire-debugbar::debugbar.performance.component_analysis') }}</h3>
            <div class="ldb-analysis-filters">
                <span class="text-sm text-gray-400">{{ __('livewire-debugbar::debugbar.performance.click_for_details') }}</span>
            </div>
        </div>

        <div class="ldb-analysis-list">
            <template x-for="(component, id) in components" :key="id">
                <div class="ldb-component-performance">
                    <div class="ldb-component-perf-header">
                        <div class="ldb-component-perf-name">
                            <span x-text="component.name"></span>
                            <code class="text-xs text-gray-500 bg-gray-800 px-2 py-0.5 rounded" x-text="id"></code>
                        </div>

                        <div class="ldb-component-perf-metrics">
                            <div class="ldb-perf-metric"
                                 :class="getPropertyCountStatus(component) === 'error' ? 'ldb-perf-metric--error' :
                                        getPropertyCountStatus(component) === 'warning' ? 'ldb-perf-metric--warning' :
                                        'ldb-perf-metric--good'">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 00-2 2v6a2 2 0 002 2h2a1 1 0 100 2H6a4 4 0 01-4-4V7a4 4 0 014-4h8a4 4 0 014 4v6a4 4 0 01-4 4h-2a1 1 0 100-2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a1 1 0 100-2 2 2 0 012 2z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ldb-perf-metric-value" x-text="Object.keys(component.properties).length"></span>
                                <span>{{ __('livewire-debugbar::debugbar.performance.props') }}</span>
                            </div>

                            <div class="ldb-perf-metric"
                                 :class="getDataSizeStatus(component) === 'error' ? 'ldb-perf-metric--error' :
                                        getDataSizeStatus(component) === 'warning' ? 'ldb-perf-metric--warning' :
                                        'ldb-perf-metric--good'">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ldb-perf-metric-value" x-text="getComponentDataSize(component)"></span>
                                <span>KB</span>
                            </div>
                        </div>
                    </div>

                    {{-- Performance Bars --}}
                    <div class="ldb-perf-bars">
                        {{-- Properties Bar --}}
                        <div class="ldb-perf-bar">
                            <div class="ldb-perf-bar-label">{{ __('livewire-debugbar::debugbar.performance.properties') }}</div>
                            <div class="ldb-perf-bar-track">
                                <div class="ldb-perf-bar-fill"
                                     :class="getPropertyCountStatus(component) === 'error' ? 'ldb-perf-bar-fill--error' :
                                            getPropertyCountStatus(component) === 'warning' ? 'ldb-perf-bar-fill--warning' :
                                            'ldb-perf-bar-fill--good'"
                                     :style="`width: ${Math.min(100, (Object.keys(component.properties).length / (config.thresholds?.max_properties || 50)) * 100)}%`">
                                </div>
                            </div>
                            <div class="ldb-perf-bar-value" x-text="`${Object.keys(component.properties).length}/${config.thresholds?.max_properties || 50}`"></div>
                        </div>

                        {{-- Memory Bar --}}
                        <div class="ldb-perf-bar">
                            <div class="ldb-perf-bar-label">{{ __('livewire-debugbar::debugbar.performance.memory') }}</div>
                            <div class="ldb-perf-bar-track">
                                <div class="ldb-perf-bar-fill"
                                     :class="getDataSizeStatus(component) === 'error' ? 'ldb-perf-bar-fill--error' :
                                            getDataSizeStatus(component) === 'warning' ? 'ldb-perf-bar-fill--warning' :
                                            'ldb-perf-bar-fill--good'"
                                     :style="`width: ${Math.min(100, (parseFloat(getComponentDataSize(component)) / ((config.thresholds?.max_serialized_size || 10240) / 1024)) * 100)}%`">
                                </div>
                            </div>
                            <div class="ldb-perf-bar-value" x-text="`${getComponentDataSize(component)}KB`"></div>
                        </div>
                    </div>

                    {{-- Warnings --}}
                    <div x-show="getPerformanceWarnings(component).length > 0" class="ldb-perf-warnings">
                        <template x-for="warning in getPerformanceWarnings(component)" :key="warning.type">
                            <div class="ldb-perf-warning"
                                 :class="warning.level === 'error' ? 'ldb-perf-warning--error' : 'ldb-perf-warning--warning'">
                                <svg class="ldb-perf-warning-icon" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="warning.message"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Large Properties --}}
                    <details x-show="getLargeProperties(component).length > 0" class="mt-3">
                        <summary class="cursor-pointer text-sm text-gray-400 hover:text-gray-300">
                            {{ __('livewire-debugbar::debugbar.performance.large_properties') }}
                            (<span x-text="getLargeProperties(component).length"></span>)
                        </summary>
                        <div class="mt-2 space-y-2">
                            <template x-for="prop in getLargeProperties(component)" :key="prop.name">
                                <div class="flex items-center justify-between p-2 bg-gray-900 rounded text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-blue-400" x-text="prop.name"></span>
                                        <span class="text-gray-500" x-text="prop.type"></span>
                                    </div>
                                    <span class="text-yellow-400 font-medium" x-text="(prop.size / 1024).toFixed(1) + ' KB'"></span>
                                </div>
                            </template>
                        </div>
                    </details>
                </div>
            </template>
        </div>
    </div>

    {{-- Memory Usage by Component --}}
    <div class="ldb-memory-chart">
        <div class="ldb-timeline-header">
            <h3 class="ldb-timeline-title">{{ __('livewire-debugbar::debugbar.performance.memory_usage_by_component') }}</h3>
            <div class="text-sm text-gray-400">
                {{ __('livewire-debugbar::debugbar.performance.total') }}: <span class="text-white font-medium" x-text="getTotalDataSize() + ' KB'"></span>
            </div>
        </div>

        <div class="ldb-memory-breakdown">
            <template x-for="(component, id) in Object.entries(components).sort((a, b) => parseFloat(getComponentDataSize(b[1])) - parseFloat(getComponentDataSize(a[1]))).slice(0, 10)" :key="id">
                <div class="ldb-memory-item">
                    <div class="ldb-memory-component">
                        <span class="font-medium" x-text="component[1].name"></span>
                        <span class="text-sm text-gray-500" x-text="getComponentDataSize(component[1]) + ' KB'"></span>
                    </div>
                    <div class="ldb-memory-bar">
                        <div class="ldb-memory-fill"
                             :style="`width: ${(parseFloat(getComponentDataSize(component[1])) / parseFloat(getTotalDataSize())) * 100}%`">
                        </div>
                    </div>
                    <span class="text-sm text-gray-400" x-text="Math.round((parseFloat(getComponentDataSize(component[1])) / parseFloat(getTotalDataSize())) * 100) + '%'"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Lifecycle Events Timeline --}}
    <div class="ldb-timeline-container">
        <div class="ldb-timeline-header">
            <h3 class="ldb-timeline-title">{{ __('livewire-debugbar::debugbar.performance.lifecycle_timeline') }}</h3>
            <button @click="lifecycleEvents = []" class="ldb-btn ldb-btn--danger ldb-btn--sm">
                {{ __('livewire-debugbar::debugbar.performance.clear_timeline') }}
            </button>
        </div>

        <div class="ldb-lifecycle-timeline">
            <template x-for="event in lifecycleEvents.slice().reverse().slice(0, 30)" :key="event.id">
                <div class="ldb-lifecycle-event"
                     :class="{
                         'ldb-lifecycle-event--init': event.event === 'init',
                         'ldb-lifecycle-event--hydrate': event.event === 'hydrate' || event.event === 'hydrated',
                         'ldb-lifecycle-event--update': event.event === 'updating' || event.event === 'updated',
                         'ldb-lifecycle-event--call': event.event === 'call',
                         'ldb-lifecycle-event--dehydrate': event.event === 'dehydrate' || event.event === 'dehydrated',
                         'ldb-lifecycle-event--mount': event.event === 'mount' || event.event === 'boot',
                         'ldb-lifecycle-event--error': event.event === 'error'
                     }">
                    <div class="ldb-lifecycle-time">
                        <span x-text="new Date(event.timestamp).toLocaleTimeString()"></span>
                    </div>
                    
                    <div class="ldb-lifecycle-icon">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            {{-- Init icon --}}
                            <path x-show="event.event === 'init'" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"/>

                            {{-- Hydrate icon --}}
                            <path x-show="event.event === 'hydrate' || event.event === 'hydrated'" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.696 6 10 6c.304 0 .792.193 1.264.979a1 1 0 001.715-1.029C12.279 4.784 11.232 4 10 4s-2.279.784-2.979 1.95c-.285.475-.507 1-.67 1.55H6a1 1 0 000 2h.013a9.358 9.358 0 000 1H6a1 1 0 100 2h.351c.163.55.385 1.075.67 1.55C7.721 15.216 8.768 16 10 16s2.279-.784 2.979-1.95a1 1 0 10-1.715-1.029c-.472.786-.96.979-1.264.979-.304 0-.792-.193-1.264-.979a4.265 4.265 0 01-.264-.521H10a1 1 0 100-2H8.017a7.36 7.36 0 010-1H10a1 1 0 100-2H8.472c.08-.185.167-.36.264-.521z"/>

                            {{-- Updating icon --}}
                            <path x-show="event.event === 'updating'" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>

                            {{-- Updated icon --}}
                            <path x-show="event.event === 'updated'" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>

                            {{-- Dehydrate icon --}}
                            <path x-show="event.event === 'dehydrate' || event.event === 'dehydrated'" d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>

                            {{-- Mount/Boot icon --}}
                            <path x-show="event.event === 'mount' || event.event === 'boot'" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4.5 1.25-5.5.5 1.5 1.25 6.5 1.25 6.5 1 0 1.5-.5 2.5-.5a3 3 0 01-2.38 2.12z"/>

                            {{-- Default icon --}}
                            <path x-show="!['init', 'hydrate', 'hydrated', 'updating', 'updated', 'dehydrate', 'dehydrated', 'mount', 'boot'].includes(event.event)" d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path x-show="!['init', 'hydrate', 'hydrated', 'updating', 'updated', 'dehydrate', 'dehydrated', 'mount', 'boot'].includes(event.event)" fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    
                    <div class="ldb-lifecycle-badge">
                        <div class="ldb-lifecycle-badge-inner"
                             :class="{
                                 'ldb-lifecycle-badge--init': event.event === 'init',
                                 'ldb-lifecycle-badge--hydrate': event.event === 'hydrate' || event.event === 'hydrated',
                                 'ldb-lifecycle-badge--update': event.event === 'updating' || event.event === 'updated',
                                 'ldb-lifecycle-badge--call': event.event === 'call',
                                 'ldb-lifecycle-badge--dehydrate': event.event === 'dehydrate' || event.event === 'dehydrated',
                                 'ldb-lifecycle-badge--mount': event.event === 'mount' || event.event === 'boot',
                                 'ldb-lifecycle-badge--error': event.event === 'error'
                             }">
                            <span x-text="event.event"></span>
                        </div>
                    </div>
                    
                    <div class="ldb-lifecycle-content">
                        <div class="ldb-lifecycle-component">
                            <span x-text="event.component?.name || 'Unknown Component'"></span>
                            <code class="ldb-lifecycle-id" x-text="event.componentId"></code>
                        </div>
                        <div x-show="event.event === 'init'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.init') }}</div>
                        <div x-show="event.event === 'hydrate'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.hydrate') }}</div>
                        <div x-show="event.event === 'hydrated'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.hydrated') }}</div>
                        <div x-show="event.event === 'updating'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.updating') }}</div>
                        <div x-show="event.event === 'updated'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.updated') }}</div>
                        <div x-show="event.event === 'dehydrate'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.dehydrate') }}</div>
                        <div x-show="event.event === 'dehydrated'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.dehydrated') }}</div>
                        <div x-show="event.event === 'mount'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.mount') }}</div>
                        <div x-show="event.event === 'boot'" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.boot') }}</div>
                        <div x-show="!['init', 'hydrate', 'hydrated', 'updating', 'updated', 'dehydrate', 'dehydrated', 'mount', 'boot'].includes(event.event)" class="ldb-lifecycle-details">{{ __('livewire-debugbar::debugbar.performance.event_desc.custom') }}</div>
                        
                        <div x-show="event.data && Object.keys(event.data).length > 0" class="ldb-lifecycle-data">
                            <span class="ldb-lifecycle-data-label">{{ __('livewire-debugbar::debugbar.performance.additional_data') }}:</span>
                            <code class="ldb-lifecycle-data-value" x-text="JSON.stringify(event.data)"></code>
                        </div>
                    </div>
                    
                    <div x-show="event.duration" class="ldb-lifecycle-duration">
                        <span class="ldb-duration-value" 
                              :class="{
                                  'ldb-duration--fast': event.duration < 50,
                                  'ldb-duration--normal': event.duration >= 50 && event.duration < 200,
                                  'ldb-duration--slow': event.duration >= 200
                              }"
                              x-text="event.duration + 'ms'"></span>
                        <span class="ldb-duration-label">{{ __('livewire-debugbar::debugbar.performance.duration') }}</span>
                    </div>
                </div>
            </template>

            <div x-show="lifecycleEvents.length === 0" class="ldb-lifecycle-empty">
                <svg class="ldb-lifecycle-empty-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="ldb-lifecycle-empty-text">{{ __('livewire-debugbar::debugbar.performance.no_lifecycle_events') }}</p>
            </div>
        </div>
    </div>

    {{-- Configuration Thresholds --}}
    <div class="ldb-thresholds-container">
        <div class="ldb-timeline-header">
            <h3 class="ldb-timeline-title">{{ __('livewire-debugbar::debugbar.performance.thresholds') }}</h3>
            <div class="text-sm text-gray-400">
                {{ __('livewire-debugbar::debugbar.performance.current_config_limits') }}
            </div>
        </div>

        <div class="ldb-thresholds-grid">
            <div class="ldb-threshold-item">
                <span class="ldb-threshold-label">{{ __('livewire-debugbar::debugbar.performance.max_properties') }}</span>
                <span class="ldb-threshold-value" x-text="config.thresholds?.max_properties || 50"></span>
            </div>

            <div class="ldb-threshold-item">
                <span class="ldb-threshold-label">{{ __('livewire-debugbar::debugbar.performance.max_serialized_size') }}</span>
                <span class="ldb-threshold-value" x-text="((config.thresholds?.max_serialized_size || 10240) / 1024).toFixed(0) + ' KB'"></span>
            </div>

            <div class="ldb-threshold-item">
                <span class="ldb-threshold-label">{{ __('livewire-debugbar::debugbar.performance.slow_render_time') }}</span>
                <span class="ldb-threshold-value" x-text="(config.thresholds?.slow_render_time || 100) + ' ms'"></span>
            </div>

            <div class="ldb-threshold-item">
                <span class="ldb-threshold-label">{{ __('livewire-debugbar::debugbar.performance.max_queries') }}</span>
                <span class="ldb-threshold-value" x-text="config.thresholds?.max_queries || 10"></span>
            </div>

            <div class="ldb-threshold-item">
                <span class="ldb-threshold-label">{{ __('livewire-debugbar::debugbar.performance.slow_query_time') }}</span>
                <span class="ldb-threshold-value" x-text="(config.thresholds?.slow_query_time || 100) + ' ms'"></span>
            </div>

            <div class="ldb-threshold-item">
                <span class="ldb-threshold-label">{{ __('livewire-debugbar::debugbar.performance.total_memory_limit') }}</span>
                <span class="ldb-threshold-value" x-text="((config.security?.max_data_size || 51200) / 1024).toFixed(0) + ' KB'"></span>
            </div>
        </div>
    </div>
</div>
