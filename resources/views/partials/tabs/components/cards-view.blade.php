{{-- Cards View --}}
<div x-show="componentViewMode === 'cards' && Object.keys(components).length > 0" class="ldb-components-cards">
    <template x-for="[componentId, component] in sortedComponents()" :key="componentId">
        <div class="ldb-component-card"
             :class="{
                 'ldb-component-card--active': activeComponentId === componentId,
                 'ldb-component-card--critical': getComponentValidationErrors(componentId).filter(e => e.level === 'critical').length > 0,
                 'ldb-component-card--warning': getComponentValidationErrors(componentId).filter(e => e.level === 'warning').length > 0
             }">
            {{-- Card Header --}}
            <div class="ldb-component-card-header">
                <div class="ldb-component-card-header-main">
                    <div class="ldb-component-card-info">
                        <div class="ldb-component-card-icon">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ldb-component-card-details">
                            <h3 x-text="component.name"></h3>
                            <div class="ldb-component-card-meta">
                                <code class="ldb-component-card-id" x-text="'#' + componentId"></code>
                                <span class="ldb-component-state-badge"
                                      :class="{
                                          'ldb-component-state-badge--active': component.state === 'active',
                                          'ldb-component-state-badge--hydrating': component.state === 'hydrating',
                                          'ldb-component-state-badge--dehydrated': component.state === 'dehydrated'
                                      }"
                                      x-text="component.state"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Actions --}}
                    <div class="ldb-component-actions">
                        {{-- Performance Badge --}}
                        <div x-show="getPerformanceWarnings(component).length > 0"
                             class="ldb-performance-badge"
                             :class="{
                                 'ldb-performance-badge--error': getPerformanceWarnings(component).some(w => w.level === 'error'),
                                 'ldb-performance-badge--warning': getPerformanceWarnings(component).some(w => w.level === 'warning')
                             }">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="getPerformanceWarnings(component).length"></span>
                        </div>

                        {{-- Focus Component --}}
                        <button @click="focusComponent(componentId)"
                                class="ldb-component-action-btn ldb-component-action-btn--blue"
                                title="{{ __('livewire-debugbar::debugbar.components.focus_component') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>

                        {{-- Refresh Component --}}
                        <button @click="refreshComponent(componentId)"
                                class="ldb-component-action-btn ldb-component-action-btn--green"
                                title="{{ __('livewire-debugbar::debugbar.components.refresh') }}">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        {{-- Expand/Collapse --}}
                        <button @click="expandedComponents[componentId] = !expandedComponents[componentId]"
                                class="ldb-component-action-btn">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path x-show="!expandedComponents[componentId]" fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                <path x-show="expandedComponents[componentId]" fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="ldb-quick-stats-grid">
                    <div class="ldb-quick-stats-grid-inner">
                        <div class="ldb-stats-item">
                            <div class="ldb-stats-label">{{ __('livewire-debugbar::debugbar.components.properties') }}</div>
                            <div class="ldb-stats-value" x-text="Object.keys(component.properties || {}).length"></div>
                        </div>
                        <div class="ldb-stats-item">
                            <div class="ldb-stats-label">{{ __('livewire-debugbar::debugbar.components.data_size') }}</div>
                            <div class="ldb-stats-value" x-text="getComponentDataSize(component) + ' KB'"></div>
                        </div>
                        <div class="ldb-stats-item">
                            <div class="ldb-stats-label">{{ __('livewire-debugbar::debugbar.components.commits') }}</div>
                            <div class="ldb-stats-value" x-text="getComponentCommitsById(componentId).length"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expanded Content --}}
            <div x-show="expandedComponents[componentId]"
                 x-collapse
                 class="ldb-expanded-content">
                {{-- Properties Section --}}
                <div class="ldb-expanded-content-inner">
                    <div x-data="{ id: componentId, component: components[componentId] }">
                        @include('livewire-debugbar::partials.components.properties')
                    </div>
                </div>

                {{-- Component Actions Bar --}}
                <div class="ldb-component-actions-bg">
                    <div class="ldb-component-actions-group">
                        <button @click="activeComponentId = componentId; activeTab = 'tools'"
                                class="ldb-component-action-btn">
                            🕰️ {{ __('livewire-debugbar::debugbar.components.view_history') }}
                        </button>
                        <button @click="generateTestCode(componentId)"
                                class="ldb-component-action-btn">
                            📋 {{ __('livewire-debugbar::debugbar.components.generate_test') }}
                        </button>
                        <button @click="exportComponentData(componentId)"
                                class="ldb-component-action-btn">
                            📁 {{ __('livewire-debugbar::debugbar.components.export_data') }}
                        </button>
                    </div>

                    <button @click="copyComponentPath(component.name)"
                            class="ldb-component-action-btn">
                        📄 {{ __('livewire-debugbar::debugbar.components.copy_path') }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>