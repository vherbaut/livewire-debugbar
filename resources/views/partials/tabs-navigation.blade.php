{{-- Tabs navigation --}}
<div x-show="!collapsed" class="flex border-b border-gray-700">
    <button @click="activeTab = 'components'"
            x-bind:class="{ 'bg-gray-700 text-blue-400': activeTab === 'components', 'text-gray-300 hover:text-white': activeTab !== 'components' }"
            class="px-4 py-2 text-sm font-medium border-r border-gray-700 focus:outline-none livewire-debugbar__tooltip"
            data-tooltip="{{ __('livewire-debugbar::debugbar.tab_tooltips.components') }}">
        {{ __('livewire-debugbar::debugbar.tabs.components') }}
    </button>
    <button @click="activeTab = 'events'"
            x-bind:class="{ 'bg-gray-700 text-blue-400': activeTab === 'events', 'text-gray-300 hover:text-white': activeTab !== 'events' }"
            class="px-4 py-2 text-sm font-medium border-r border-gray-700 focus:outline-none livewire-debugbar__tooltip"
            data-tooltip="{{ __('livewire-debugbar::debugbar.tab_tooltips.events') }}">
        {{ __('livewire-debugbar::debugbar.tabs.events') }}
    </button>
    <button @click="activeTab = 'performance'"
            x-bind:class="{ 'bg-gray-700 text-blue-400': activeTab === 'performance', 'text-gray-300 hover:text-white': activeTab !== 'performance' }"
            class="px-4 py-2 text-sm font-medium border-r border-gray-700 focus:outline-none livewire-debugbar__tooltip"
            data-tooltip="{{ __('livewire-debugbar::debugbar.tab_tooltips.performance') }}">
        {{ __('livewire-debugbar::debugbar.tabs.performance') }}
    </button>
    <button @click="activeTab = 'security'"
            x-bind:class="{ 'bg-gray-700 text-blue-400': activeTab === 'security', 'text-gray-300 hover:text-white': activeTab !== 'security' }"
            class="px-4 py-2 text-sm font-medium border-r border-gray-700 focus:outline-none livewire-debugbar__tooltip"
            data-tooltip="{{ __('livewire-debugbar::debugbar.tab_tooltips.security') }}">
        {{ __('livewire-debugbar::debugbar.tabs.security') }}
    </button>
    <button @click="activeTab = 'advanced'"
            x-bind:class="{ 'bg-gray-700 text-blue-400': activeTab === 'advanced', 'text-gray-300 hover:text-white': activeTab !== 'advanced' }"
            class="px-4 py-2 text-sm font-medium focus:outline-none livewire-debugbar__tooltip"
            data-tooltip="{{ __('livewire-debugbar::debugbar.tab_tooltips.advanced') }}">
        {{ __('livewire-debugbar::debugbar.tabs.advanced') }}
    </button>
</div>
