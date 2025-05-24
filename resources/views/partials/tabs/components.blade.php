{{-- Components Tab --}}
<div x-show="activeTab === 'components'" class="ldb-tab-content">
    {{-- Tab Header --}}
    <div class="ldb-tab-header">
        <h2 class="ldb-tab-title">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
            {{ __('livewire-debugbar::debugbar.tabs.components') }}
        </h2>
    </div>

    {{-- Filter Bar --}}
    <div class="ldb-filter-bar">
        <div class="ldb-filter-bar-left">
            {{-- Search Input --}}
            <div class="ldb-search-container">
                <svg class="ldb-search-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
                <input type="text"
                       x-model="componentSearch"
                       placeholder="{{ __('livewire-debugbar::debugbar.search.components_placeholder') }}"
                       class="ldb-search-input">
            </div>

            {{-- Filter by State - TODO: Implement --}}
            {{-- <select x-model="componentStateFilter" @change="filterComponents()" class="ldb-filter-select">
                <option value="">{{ __('livewire-debugbar::debugbar.filters.all_states') }}</option>
                <option value="active">{{ __('livewire-debugbar::debugbar.filters.active') }}</option>
                <option value="hydrating">{{ __('livewire-debugbar::debugbar.filters.hydrating') }}</option>
                <option value="dehydrated">{{ __('livewire-debugbar::debugbar.filters.dehydrated') }}</option>
            </select> --}}

            {{-- Filter by Validation - TODO: Implement --}}
            {{-- <select x-model="componentValidationFilter" @change="filterComponents()" class="ldb-filter-select">
                <option value="">{{ __('livewire-debugbar::debugbar.filters.all_validations') }}</option>
                <option value="valid">{{ __('livewire-debugbar::debugbar.filters.valid') }}</option>
                <option value="warnings">{{ __('livewire-debugbar::debugbar.filters.with_warnings') }}</option>
                <option value="errors">{{ __('livewire-debugbar::debugbar.filters.with_errors') }}</option>
            </select> --}}

            {{-- Quick Stats --}}
            <div class="ldb-quick-stats">
                <div class="ldb-quick-stats-item">
                    <span class="ldb-quick-stats-label">{{ __('livewire-debugbar::debugbar.stats.total') }}:</span>
                    <span class="ldb-quick-stats-value ldb-quick-stats-value--blue" x-text="Object.keys(components).length"></span>
                </div>
                <div class="ldb-quick-stats-item">
                    <span class="ldb-quick-stats-label">{{ __('livewire-debugbar::debugbar.stats.active') }}:</span>
                    <span class="ldb-quick-stats-value ldb-quick-stats-value--green" x-text="Object.values(components).filter(c => c.state === 'active').length"></span>
                </div>
                <div class="ldb-quick-stats-item">
                    <span class="ldb-quick-stats-label">{{ __('livewire-debugbar::debugbar.stats.warnings') }}:</span>
                    <span class="ldb-quick-stats-value ldb-quick-stats-value--yellow" x-text="Object.keys(components).filter(id => getComponentValidationErrors(id).length > 0).length"></span>
                </div>
            </div>
        </div>

        <div class="ldb-filter-bar-right">
            {{-- View Mode Toggle --}}
            <div class="ldb-view-mode-toggle">
                <button @click="componentViewMode = 'cards'"
                        class="ldb-view-mode-btn"
                        :class="componentViewMode === 'cards' ? 'ldb-view-mode-btn--active' : ''"
                        title="{{ __('livewire-debugbar::debugbar.view_modes.cards') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button @click="componentViewMode = 'tree'"
                        class="ldb-view-mode-btn"
                        :class="componentViewMode === 'tree' ? 'ldb-view-mode-btn--active' : ''"
                        title="{{ __('livewire-debugbar::debugbar.view_modes.tree') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/>
                        <path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/>
                    </svg>
                </button>
                <button @click="componentViewMode = 'list'"
                        class="ldb-view-mode-btn"
                        :class="componentViewMode === 'list' ? 'ldb-view-mode-btn--active' : ''"
                        title="{{ __('livewire-debugbar::debugbar.view_modes.list') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            {{-- Sort Dropdown --}}
            <select x-model="componentSort" @change="sortComponents()" class="ldb-component-sort-select">
                <option value="name">{{ __('livewire-debugbar::debugbar.sort.by_name') }}</option>
                <option value="state">{{ __('livewire-debugbar::debugbar.sort.by_state') }}</option>
                <option value="properties">{{ __('livewire-debugbar::debugbar.sort.by_properties') }}</option>
                <option value="size">{{ __('livewire-debugbar::debugbar.sort.by_size') }}</option>
            </select>

            {{-- Refresh All --}}
            <button @click="refreshAllComponents()" class="ldb-component-refresh-btn" title="{{ __('livewire-debugbar::debugbar.actions.refresh_all') }}">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Active Filters Display --}}
    <div x-show="componentSearch || componentStateFilter || componentValidationFilter" class="ldb-filter-tags">
        <span class="ldb-filter-tags-label">{{ __('livewire-debugbar::debugbar.filters.active_filters') }}:</span>

        <span x-show="componentSearch" class="ldb-filter-tag">
            {{ __('livewire-debugbar::debugbar.filters.search') }}: <strong x-text="componentSearch"></strong>
            <button @click="componentSearch = ''; filterComponents()" class="ldb-filter-tag-remove">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>

        <span x-show="componentStateFilter" class="ldb-filter-tag">
            {{ __('livewire-debugbar::debugbar.filters.state') }}: <strong x-text="componentStateFilter"></strong>
            <button @click="componentStateFilter = ''; filterComponents()" class="ldb-filter-tag-remove">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>

        <span x-show="componentValidationFilter" class="ldb-filter-tag">
            {{ __('livewire-debugbar::debugbar.filters.validation') }}: <strong x-text="componentValidationFilter"></strong>
            <button @click="componentValidationFilter = ''; filterComponents()" class="ldb-filter-tag-remove">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
    </div>

    {{-- Components Container --}}
    <div class="ldb-components-content">
        {{-- No Components State --}}
        <div x-show="Object.keys(components).length === 0" class="ldb-no-components">
            <svg class="ldb-no-components-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2H5zm3 2a1 1 0 000 2h4a1 1 0 100-2H8zm0 3a1 1 0 000 2h4a1 1 0 100-2H8zm0 3a1 1 0 000 2h4a1 1 0 100-2H8z" clip-rule="evenodd"/>
            </svg>
            <h3 class="ldb-no-components-title">{{ __('livewire-debugbar::debugbar.components.no_components') }}</h3>
            <p class="ldb-no-components-desc">{{ __('livewire-debugbar::debugbar.components.no_components_desc') }}</p>
        </div>

        {{-- Include Component Views --}}
        @include('livewire-debugbar::partials.tabs.components.cards-view')
        @include('livewire-debugbar::partials.tabs.components.tree-view')
        @include('livewire-debugbar::partials.tabs.components.list-view')
    </div>
</div>
