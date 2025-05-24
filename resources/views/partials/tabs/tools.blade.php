{{-- Developer Tools --}}
<div x-show="activeTab === 'tools'" class="livewire-debugbar__panel livewire-debugbar__panel--active">
<div class="p-4 space-y-6">

    {{-- Time Travel Debugging --}}
    <div class="bg-gray-900 rounded border border-gray-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-200">🕰️ {{ __('livewire-debugbar::debugbar.tools.time_travel') }}</h3>
                <p class="text-sm text-gray-400">{{ __('livewire-debugbar::debugbar.tools.time_travel_desc') }}</p>
            </div>
            <select x-model="activeComponentId" class="bg-gray-950 border border-gray-600 rounded px-3 py-1 text-sm text-gray-300">
                <option value="">{{ __('livewire-debugbar::debugbar.tools.select_component') }}</option>
                <template x-for="[id, component] in Object.entries(components)" :key="id">
                    <option :value="id" x-text="component.name + ' (' + id + ')'"></option>
                </template>
            </select>
        </div>

        <div x-show="activeComponentId && stateHistory[activeComponentId]" class="space-y-2">
            <div class="text-sm text-gray-400 mb-2">
                {{ __('livewire-debugbar::debugbar.tools.state_history') }}: <span x-text="stateHistory[activeComponentId]?.length || 0"></span> {{ __('livewire-debugbar::debugbar.tools.snapshots') }}
            </div>

            <div class="max-h-64 overflow-y-auto space-y-1">
                <template x-for="snapshot in (stateHistory[activeComponentId] || []).slice().reverse()" :key="snapshot.id">
                    <div class="flex items-center justify-between p-2 bg-gray-950 rounded text-xs">
                        <div class="space-x-2">
                            <span class="text-gray-400" x-text="new Date(snapshot.timestamp).toLocaleTimeString()"></span>
                            <span class="text-gray-300"
                                  x-text="Object.keys(snapshot.state).length + ' {{ __('livewire-debugbar::debugbar.tools.properties') }}'"></span>
                        </div>
                        <div class="flex space-x-1">
                            <button @click="restoreComponentState(activeComponentId, snapshot.id)"
                                    class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded">
                                {{ __('livewire-debugbar::debugbar.tools.restore') }}
                            </button>
                            <button @click="console.log('State snapshot:', snapshot.state)"
                                    class="px-2 py-1 bg-gray-600 hover:bg-gray-700 text-white rounded">
                                {{ __('livewire-debugbar::debugbar.tools.view') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>


    {{-- Developer Tools --}}
    <div class="bg-gray-900 rounded border border-gray-700 p-4">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-200">🛠️ {{ __('livewire-debugbar::debugbar.tools.developer_tools') }}</h3>
            <p class="text-sm text-gray-400">{{ __('livewire-debugbar::debugbar.tools.developer_tools_desc') }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('livewire-debugbar::debugbar.tools.component_actions') }}</label>
                <div class="space-y-2">
                    <select x-model="activeComponentId" class="w-full bg-gray-950 border border-gray-600 rounded px-3 py-2 text-sm text-gray-300">
                        <option value="">{{ __('livewire-debugbar::debugbar.tools.select_component') }}</option>
                        <template x-for="[id, component] in Object.entries(components)" :key="id">
                            <option :value="id" x-text="component.name + ' (' + id + ')'"></option>
                        </template>
                    </select>

                    <div class="grid grid-cols-2 gap-2">
                        <button x-show="activeComponentId"
                                @click="generateTestCode(activeComponentId)"
                                class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm">
                            📋 {{ __('livewire-debugbar::debugbar.tools.generate_test') }}
                        </button>
                        <button x-show="activeComponentId"
                                @click="exportComponentData(activeComponentId)"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                            📁 {{ __('livewire-debugbar::debugbar.tools.export_data') }}
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('livewire-debugbar::debugbar.tools.performance_insights') }}</label>
                <div class="bg-gray-950 rounded p-3 text-xs" x-data="{ insights: null }" x-init="insights = getPerformanceInsights()">
                    <div class="space-y-1">
                        <div>{{ __('livewire-debugbar::debugbar.tools.total_components') }}: <span class="font-mono text-blue-300" x-text="insights?.totalComponents"></span></div>
                        <div>{{ __('livewire-debugbar::debugbar.tools.total_properties') }}: <span class="font-mono text-blue-300" x-text="insights?.totalProperties"></span></div>
                        <div>{{ __('livewire-debugbar::debugbar.tools.avg_properties') }}: <span class="font-mono text-blue-300" x-text="insights?.averageProperties"></span></div>
                        <div>{{ __('livewire-debugbar::debugbar.tools.validation_errors') }}: <span class="font-mono text-red-300" x-text="insights?.totalValidationErrors"></span></div>
                        <div>{{ __('livewire-debugbar::debugbar.tools.critical_errors') }}: <span class="font-mono text-red-400" x-text="insights?.criticalErrors"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
