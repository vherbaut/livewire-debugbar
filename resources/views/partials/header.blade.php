{{-- Header --}}
<div class="flex items-center justify-between px-4 py-2 bg-gray-950 border-b border-gray-700">
    <div class="flex items-center space-x-4">
        <button @click="toggleCollapse()" class="text-blue-400 hover:text-blue-300 focus:outline-none">
            <svg x-show="collapsed" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            <svg x-show="!collapsed" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>

        <h3 class="text-lg font-semibold text-blue-400">Livewire Debugbar</h3>

        <div class="flex items-center space-x-2 text-sm text-gray-300">
            <span class="px-2 py-1 bg-blue-600 rounded text-xs" x-text="Object.keys(components).length + ' components'"></span>
            <span class="px-2 py-1 bg-green-600 rounded text-xs" x-text="(events.lifecycle.length + events.dispatched.length) + ' events'"></span>
        </div>
    </div>
</div>
