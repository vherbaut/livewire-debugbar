@once
    @livewireDebugbarStyles
    @livewireDebugbarScripts
@endonce

<!-- Livewire Debug Bar -->
<div class="livewire-debugbar livewire-debugbar--entering"
     x-data="livewireDebugbar({
         config: {{ Js::from($config) }}
     })"
     x-bind:class="{
         'livewire-debugbar--collapsed': collapsed,
         'livewire-debugbar--top': position === 'top',
         'livewire-debugbar--resizing': isResizing
     }"
     :style="{ height: collapsed ? '48px' : height + 'px' }"
     x-show="window.APP_DEBUG !== false"
     x-transition:enter="transition-all duration-200"
     x-transition:enter-start="transform translate-y-full opacity-0"
     x-transition:enter-end="transform translate-y-0 opacity-100">

    {{-- Resize Handle --}}
    <div class="livewire-debugbar__resize-handle"
         @mousedown="startResize($event)"
         x-show="position === 'bottom' || position === 'top'"></div>

    {{-- Debugbar Header --}}
    <header class="livewire-debugbar__header">
        <div class="livewire-debugbar__title">
            <svg class="livewire-debugbar__logo" fill="currentColor" viewBox="0 0 24 24">
                <path d="M13 3L4 14h6l-1 7 9-11h-6l1-7z"/>
            </svg>
            <span>{{ __('livewire-debugbar::debugbar.title') }}</span>
        </div>

        <div class="livewire-debugbar__status">
            <span class="livewire-debugbar__status-indicator"></span>
            <span x-text="Object.keys(components).length + ' {{ __('livewire-debugbar::debugbar.tabs.components') }}'"></span>

            {{-- Hot Reload Indicator --}}
            <button class="livewire-debugbar__hot-reload livewire-debugbar__tooltip"
                    :class="{ 'livewire-debugbar__hot-reload--active': hotReload }"
                    @click="toggleHotReload()"
                    x-show="config.hot_reload?.enabled"
                    :data-tooltip="hotReload ? '{{ __('livewire-debugbar::debugbar.hot_reload.disable') }}' : '{{ __('livewire-debugbar::debugbar.hot_reload.enable') }}'">
                <span class="livewire-debugbar__hot-reload-indicator"></span>
                <span x-text="hotReload ? '{{ __('livewire-debugbar::debugbar.hot_reload.active') }}' : '{{ __('livewire-debugbar::debugbar.hot_reload.inactive') }}'"></span>
            </button>
        </div>

        <div class="livewire-debugbar__actions">
            {{-- Minimize/Maximize --}}
            <button class="livewire-debugbar__btn livewire-debugbar__btn--icon livewire-debugbar__tooltip"
                    @click="toggleCollapse()"
                    :data-tooltip="collapsed ? '{{ __('livewire-debugbar::debugbar.maximize') }}' : '{{ __('livewire-debugbar::debugbar.minimize') }}'">
                <svg x-show="!collapsed" class="livewire-debugbar__btn__icon" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
                <svg x-show="collapsed" class="livewire-debugbar__btn__icon" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 15l-6-6-6 6"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- Debugbar Body --}}
    <div class="livewire-debugbar__body" x-show="!collapsed" x-transition>
        {{-- Sidebar Navigation --}}
        <nav class="livewire-debugbar__sidebar">
            <div class="livewire-debugbar__nav">
                <button class="livewire-debugbar__nav-item"
                        :class="{ 'livewire-debugbar__nav-item--active': activeTab === 'components' }"
                        @click="activeTab = 'components'">
                    <svg class="livewire-debugbar__nav-item__icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span class="livewire-debugbar__nav-item__text">{{ __('livewire-debugbar::debugbar.tabs.components') }}</span>
                    <span class="livewire-debugbar__nav-item__badge livewire-debugbar__nav-item__badge--info"
                          x-text="Object.keys(components).length"
                          x-show="Object.keys(components).length > 0"></span>
                </button>

                <button class="livewire-debugbar__nav-item"
                        :class="{ 'livewire-debugbar__nav-item--active': activeTab === 'events' }"
                        @click="activeTab = 'events'">
                    <svg class="livewire-debugbar__nav-item__icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 3L4 14h6l-1 7 9-11h-6l1-7z"/>
                    </svg>
                    <span class="livewire-debugbar__nav-item__text">{{ __('livewire-debugbar::debugbar.tabs.events') }}</span>
                    <span class="livewire-debugbar__nav-item__badge livewire-debugbar__nav-item__badge--info"
                          x-text="getEvents().length"
                          x-show="getEvents().length > 0"></span>
                </button>

                <button class="livewire-debugbar__nav-item"
                        :class="{ 'livewire-debugbar__nav-item--active': activeTab === 'performance' }"
                        @click="activeTab = 'performance'">
                    <svg class="livewire-debugbar__nav-item__icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>
                    </svg>
                    <span class="livewire-debugbar__nav-item__text">{{ __('livewire-debugbar::debugbar.tabs.performance') }}</span>
                    <span class="livewire-debugbar__nav-item__badge"
                          :class="{
                              'livewire-debugbar__nav-item__badge--error': getPerformanceIssueCount() > 0,
                              'livewire-debugbar__nav-item__badge--warning': getPerformanceWarningCount() > 0 && getPerformanceIssueCount() === 0,
                              'livewire-debugbar__nav-item__badge--info': getPerformanceIssueCount() === 0 && getPerformanceWarningCount() === 0
                          }"
                          x-text="getPerformanceIssueCount() + getPerformanceWarningCount()"
                          x-show="getPerformanceIssueCount() + getPerformanceWarningCount() > 0"></span>
                </button>

                <button class="livewire-debugbar__nav-item"
                        :class="{ 'livewire-debugbar__nav-item--active': activeTab === 'security' }"
                        @click="activeTab = 'security'">
                    <svg class="livewire-debugbar__nav-item__icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.4,7 14.8,8.6 14.8,10V11H16V16H8V11H9.2V10C9.2,8.6 10.6,7 12,7M12,8.2C11.2,8.2 10.4,8.7 10.4,10V11H13.6V10C13.6,8.7 12.8,8.2 12,8.2Z"/>
                    </svg>
                    <span class="livewire-debugbar__nav-item__text">{{ __('livewire-debugbar::debugbar.tabs.security') }}</span>
                    <span class="livewire-debugbar__nav-item__badge"
                          :class="{
                              'livewire-debugbar__nav-item__badge--error': getSecurityIssues().filter(i => i.level === 'critical').length > 0,
                              'livewire-debugbar__nav-item__badge--warning': getSecurityIssues().filter(i => i.level === 'warning').length > 0 && getSecurityIssues().filter(i => i.level === 'critical').length === 0,
                              'livewire-debugbar__nav-item__badge--info': getSecurityIssues().length === 0
                          }"
                          x-text="getSecurityIssues().length"
                          x-show="getSecurityIssues().length > 0"></span>
                </button>

                <button class="livewire-debugbar__nav-item"
                        :class="{ 'livewire-debugbar__nav-item--active': activeTab === 'tools' }"
                        @click="activeTab = 'tools'">
                    <svg class="livewire-debugbar__nav-item__icon" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M10,22C9.75,22 9.54,21.82 9.5,21.58L9.13,18.93C8.5,18.68 7.96,18.34 7.44,17.94L4.95,18.95C4.73,19.03 4.46,18.95 4.34,18.73L2.34,15.27C2.21,15.05 2.27,14.78 2.46,14.63L4.57,12.97L4.5,12L4.57,11L2.46,9.37C2.27,9.22 2.21,8.95 2.34,8.73L4.34,5.27C4.46,5.05 4.73,4.96 4.95,5.05L7.44,6.05C7.96,5.66 8.5,5.32 9.13,5.07L9.5,2.42C9.54,2.18 9.75,2 10,2H14C14.25,2 14.46,2.18 14.5,2.42L14.87,5.07C15.5,5.32 16.04,5.66 16.56,6.05L19.05,5.05C19.27,4.96 19.54,5.05 19.66,5.27L21.66,8.73C21.79,8.95 21.73,9.22 21.54,9.37L19.43,11L19.5,12L19.43,13L21.54,14.63C21.73,14.78 21.79,15.05 21.66,15.27L19.66,18.73C19.54,18.95 19.27,19.04 19.05,18.95L16.56,17.95C16.04,18.34 15.5,18.68 14.87,18.93L14.5,21.58C14.46,21.82 14.25,22 14,22H10M11.25,4L10.88,6.61C9.68,6.86 8.62,7.5 7.85,8.39L5.44,7.35L4.69,8.65L6.8,10.2C6.4,11.37 6.4,12.64 6.8,13.8L4.68,15.36L5.43,16.66L7.86,15.62C8.63,16.5 9.68,17.14 10.87,17.38L11.24,20H12.76L13.13,17.39C14.32,17.14 15.37,16.5 16.14,15.62L18.57,16.66L19.32,15.36L17.2,13.81C17.6,12.64 17.6,11.37 17.2,10.2L19.31,8.65L18.56,7.35L16.15,8.39C15.38,7.5 14.32,6.86 13.12,6.62L12.75,4H11.25Z"/>
                    </svg>
                    <span class="livewire-debugbar__nav-item__text">{{ __('livewire-debugbar::debugbar.tabs.tools') }}</span>
                    <span class="livewire-debugbar__nav-item__badge livewire-debugbar__nav-item__badge--error"
                          x-text="Object.values(validationErrors).flat().filter(e => e.level === 'critical').length"
                          x-show="Object.values(validationErrors).flat().filter(e => e.level === 'critical').length > 0"></span>
                </button>
            </div>
        </nav>

        {{-- Main content --}}
        <main class="livewire-debugbar__content">
            @include('livewire-debugbar::partials.tabs.components')
            @include('livewire-debugbar::partials.tabs.events')
            @include('livewire-debugbar::partials.tabs.performance')
            @include('livewire-debugbar::partials.tabs.security')
            @include('livewire-debugbar::partials.tabs.tools')
        </main>
    </div>
</div>
