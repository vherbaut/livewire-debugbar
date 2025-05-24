{{-- Tree View --}}
<div x-show="componentViewMode === 'tree' && Object.keys(components).length > 0"
     class="ldb-tree-view">
    <div class="tree-view">
        <template x-for="[componentId, node] in Object.entries(getComponentTree())" :key="componentId">
            <div class="tree-node">
                <div class="ldb-tree-node-item"
                     :class="{
                         'active': activeComponentId === componentId,
                         'critical': getComponentValidationErrors(componentId).filter(e => e.level === 'critical').length > 0,
                         'warning': getComponentValidationErrors(componentId).filter(e => e.level === 'warning').length > 0
                     }">

                    {{-- Expand/Collapse Button --}}
                    <button @click="toggleTreeNode(componentId)"
                            x-show="hasChildren(componentId)"
                            class="ldb-tree-expand-btn">
                        <svg class="ldb-tree-expand-icon"
                             :class="expandedTreeNodes[componentId] ? 'rotated' : ''"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="!hasChildren(componentId)" class="ldb-tree-spacer"></div>

                    {{-- Component Icon --}}
                    <div class="ldb-tree-component-icon">
                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    {{-- Component Info --}}
                    <div class="ldb-tree-component-info">
                        <div class="flex items-center gap-2">
                            <span class="ldb-tree-component-name" x-text="node.component.name"></span>
                            <code class="ldb-tree-component-id" x-text="'#' + componentId"></code>
                            <span class="ldb-tree-component-state"
                                  :class="{
                                      'active': node.component.state === 'active',
                                      'hydrating': node.component.state === 'hydrating',
                                      'dehydrated': node.component.state === 'dehydrated'
                                  }"
                                  x-text="node.component.state"></span>
                        </div>

                        {{-- Component Stats --}}
                        <div class="ldb-tree-component-stats">
                            <span class="ldb-tree-stat-item">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7 2a1 1 0 00-1 1v1a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V6a2 2 0 00-2-2V3a1 1 0 10-2 0v1H9V3a1 1 0 00-1-1H7zm0 5a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="Object.keys(node.component.properties || {}).length"></span> props
                            </span>
                            <span class="ldb-tree-stat-item">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="getComponentDataSize(node.component)"></span> KB
                            </span>
                            <span x-show="node.children.length > 0" class="ldb-tree-stat-item">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="node.children.length"></span> children
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="ldb-tree-actions">
                        {{-- Performance Badge --}}
                        <div x-show="getPerformanceWarnings(node.component).length > 0"
                             class="ldb-tree-perf-badge"
                             :class="{
                                 'error': getPerformanceWarnings(node.component).some(w => w.level === 'error'),
                                 'warning': getPerformanceWarnings(node.component).some(w => w.level === 'warning')
                             }">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="getPerformanceWarnings(node.component).length"></span>
                        </div>

                        {{-- Expand Properties --}}
                        <button @click="activeComponentId = componentId; expandedTreeNodes[componentId + '_props'] = !expandedTreeNodes[componentId + '_props']"
                                class="ldb-tree-action-btn"
                                title="{{ __('livewire-debugbar::debugbar.components.properties') }}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        {{-- Focus Component --}}
                        <button @click="focusComponent(componentId)"
                                class="ldb-tree-action-btn"
                                title="{{ __('livewire-debugbar::debugbar.components.focus_component') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>

                        {{-- Refresh Component --}}
                        <button @click="refreshComponent(componentId)"
                                class="ldb-tree-action-btn refresh"
                                title="{{ __('livewire-debugbar::debugbar.components.refresh') }}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Properties Section (Expandable) --}}
                <div x-show="expandedTreeNodes[componentId + '_props']"
                     x-collapse
                     class="ldb-tree-props-section">
                    <div x-data="{ id: componentId, component: components[componentId] }">
                        @include('livewire-debugbar::partials.components.properties')
                    </div>
                </div>

                {{-- Children --}}
                <div x-show="expandedTreeNodes[componentId]"
                     x-collapse
                     class="ldb-tree-children">
                    <template x-for="childId in node.children" :key="childId">
                        @include('livewire-debugbar::partials.tabs.components.tree-child')
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>