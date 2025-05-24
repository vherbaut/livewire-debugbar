{{-- Tree Child Component --}}
<div class="ldb-tree-child">
    <div class="ldb-tree-node-item"
         :class="{
             'active': activeComponentId === childId,
             'critical': getComponentValidationErrors(childId).filter(e => e.level === 'critical').length > 0,
             'warning': getComponentValidationErrors(childId).filter(e => e.level === 'warning').length > 0
         }">

        {{-- Tree Line --}}
        <div class="ldb-tree-line">
            <div class="ldb-tree-line-border"></div>
        </div>

        {{-- Component Icon --}}
        <div class="ldb-tree-component-icon">
            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>

        {{-- Component Info --}}
        <div class="ldb-tree-component-info">
            <div class="flex items-center gap-2">
                <span class="ldb-tree-component-name" x-text="components[childId].name"></span>
                <code class="ldb-tree-component-id" x-text="'#' + childId"></code>
            </div>

            {{-- Component Stats --}}
            <div class="ldb-tree-component-stats">
                <span class="ldb-tree-stat-item">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7 2a1 1 0 00-1 1v1a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V6a2 2 0 00-2-2V3a1 1 0 10-2 0v1H9V3a1 1 0 00-1-1H7zm0 5a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="Object.keys(components[childId].properties || {}).length"></span> props
                </span>
                <span class="ldb-tree-stat-item">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="getComponentDataSize(components[childId])"></span> KB
                </span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="ldb-tree-actions">
            {{-- Expand Properties --}}
            <button @click="activeComponentId = childId; expandedTreeNodes[childId + '_props'] = !expandedTreeNodes[childId + '_props']"
                    class="ldb-tree-action-btn"
                    title="{{ __('livewire-debugbar::debugbar.components.properties') }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
            </button>

            {{-- Focus Component --}}
            <button @click="focusComponent(childId)"
                    class="ldb-tree-action-btn"
                    title="{{ __('livewire-debugbar::debugbar.components.focus_component') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Properties Section (Expandable) --}}
    <div x-show="expandedTreeNodes[childId + '_props']"
         x-collapse
         class="ldb-tree-child-props">
        <div x-data="{ id: childId, component: components[childId] }">
            @include('livewire-debugbar::partials.components.properties')
        </div>
    </div>
</div>