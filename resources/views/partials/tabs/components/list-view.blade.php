{{-- List View --}}
<div x-show="componentViewMode === 'list' && Object.keys(components).length > 0"
     class="ldb-list-view">
    <table class="ldb-list-table">
        <thead class="ldb-list-thead">
            <tr>
                <th class="ldb-list-th left">
                    {{ __('livewire-debugbar::debugbar.components.component') }}
                </th>
                <th class="ldb-list-th center">
                    {{ __('livewire-debugbar::debugbar.components.state') }}
                </th>
                <th class="ldb-list-th center">
                    {{ __('livewire-debugbar::debugbar.components.properties') }}
                </th>
                <th class="ldb-list-th center">
                    {{ __('livewire-debugbar::debugbar.components.data_size') }}
                </th>
                <th class="ldb-list-th center">
                    {{ __('livewire-debugbar::debugbar.components.issues') }}
                </th>
                <th class="ldb-list-th right">
                    {{ __('livewire-debugbar::debugbar.components.actions') }}
                </th>
            </tr>
        </thead>
        <tbody class="ldb-list-tbody">
            <template x-for="[componentId, component] in sortedComponents()" :key="componentId">
                <tr class="ldb-list-tr"
                    :class="{ 'active': activeComponentId === componentId }">
                    <td class="ldb-list-td">
                        <div>
                            <div class="ldb-list-component-name" x-text="component.name"></div>
                            <code class="ldb-list-component-id" x-text="'#' + componentId"></code>
                        </div>
                    </td>
                    <td class="ldb-list-td ldb-list-text-center">
                        <span class="ldb-list-state-badge"
                              :class="{
                                  'active': component.state === 'active',
                                  'hydrating': component.state === 'hydrating',
                                  'dehydrated': component.state === 'dehydrated'
                              }"
                              x-text="component.state"></span>
                    </td>
                    <td class="ldb-list-td ldb-list-text-center" x-text="Object.keys(component.properties || {}).length"></td>
                    <td class="ldb-list-td ldb-list-text-center" x-text="getComponentDataSize(component) + ' KB'"></td>
                    <td class="ldb-list-td ldb-list-text-center">
                        <div class="ldb-list-issues">
                            <span x-show="getComponentValidationErrors(componentId).filter(e => e.level === 'critical').length > 0"
                                  class="ldb-list-issue-badge critical"
                                  x-text="getComponentValidationErrors(componentId).filter(e => e.level === 'critical').length"></span>
                            <span x-show="getComponentValidationErrors(componentId).filter(e => e.level === 'warning').length > 0"
                                  class="ldb-list-issue-badge warning"
                                  x-text="getComponentValidationErrors(componentId).filter(e => e.level === 'warning').length"></span>
                        </div>
                    </td>
                    <td class="ldb-list-td ldb-list-text-right">
                        <div class="ldb-list-actions">
                            {{-- View Properties --}}
                            <button @click="componentViewMode = 'cards'; activeComponentId = componentId"
                                    class="ldb-list-action-btn view"
                                    title="{{ __('livewire-debugbar::debugbar.components.view_details') }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            {{-- Focus Component --}}
                            <button @click="focusComponent(componentId)"
                                    class="ldb-list-action-btn focus"
                                    title="{{ __('livewire-debugbar::debugbar.components.focus_component') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>

                            {{-- Refresh Component --}}
                            <button @click="refreshComponent(componentId)"
                                    class="ldb-list-action-btn refresh"
                                    title="{{ __('livewire-debugbar::debugbar.components.refresh_component') }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>