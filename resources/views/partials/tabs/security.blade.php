{{-- Security Tab  --}}
<div x-show="activeTab === 'security'" class="livewire-debugbar__panel livewire-debugbar__panel--active">
    {{-- Real-time Security Validation --}}
    <div class="bg-gray-950 rounded-lg border border-gray-700 p-4 mb-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">🔍 {{ __('livewire-debugbar::debugbar.tools.real_time_validation') }}</h3>
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2">
                    <input type="checkbox"
                           x-model="realTimeValidation"
                           @change="toggleRealTimeValidation()"
                           class="rounded border-gray-600 bg-gray-950 text-blue-600">
                    <span class="text-sm text-gray-300">{{ __('livewire-debugbar::debugbar.tools.enable_validation') }}</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox"
                           x-model="stateChangeNotifications"
                           @change="toggleStateChangeNotifications()"
                           class="rounded border-gray-600 bg-gray-950 text-blue-600">
                    <span class="text-sm text-gray-300">{{ __('livewire-debugbar::debugbar.tools.show_alerts') }}</span>
                </label>
            </div>
        </div>

        {{-- Active Security Issues --}}
        <div x-show="Object.values(validationErrors).flat().filter(e => e.type === 'security').length > 0" class="space-y-2 mb-4">
            <h4 class="font-medium text-red-300">🚨 {{ __('livewire-debugbar::debugbar.tools.critical_issues') }}</h4>
            <template x-for="[componentId, errors] in Object.entries(validationErrors)" :key="componentId">
                <div x-show="errors.filter(e => e.type === 'security').length > 0" class="space-y-1">
                    <div class="text-sm font-medium text-gray-300" x-text="components[componentId]?.name"></div>
                    <template x-for="error in errors.filter(e => e.type === 'security')" :key="error.id">
                        <div class="flex items-start space-x-2 p-2 rounded text-xs bg-red-900/30 border border-red-700">
                            <span class="font-mono text-gray-400" x-text="error.property"></span>
                            <span class="flex-1" x-text="error.message"></span>
                            <button x-show="error.autoFix"
                                    @click="error.autoFixAction && error.autoFixAction()"
                                    class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs">
                                {{ __('livewire-debugbar::debugbar.tools.fix') }}
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Security Overview  --}}
    <div class="bg-gray-950 rounded-lg border border-gray-700 p-4">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ __('livewire-debugbar::debugbar.security.overview') }}</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="bg-gray-900 rounded p-3">
                <div class="text-2xl font-bold"
                     x-bind:class="getSecurityIssues().filter(i => i.level === 'critical').length > 0 ? 'text-red-400' : 'text-green-400'"
                     x-text="getSecurityIssues().filter(i => i.level === 'critical').length"></div>
                <div class="text-xs text-gray-500">{{ __('livewire-debugbar::debugbar.security.critical_issues') }}</div>
            </div>

            <div class="bg-gray-900 rounded p-3">
                <div class="text-2xl font-bold text-yellow-400"
                     x-text="getSecurityIssues().filter(i => i.level === 'warning').length"></div>
                <div class="text-xs text-gray-500">{{ __('livewire-debugbar::debugbar.security.warnings') }}</div>
            </div>

            <div class="bg-gray-900 rounded p-3">
                <div class="text-2xl font-bold text-blue-400"
                     x-text="getUnlockedProperties().length"></div>
                <div class="text-xs text-gray-500">{{ __('livewire-debugbar::debugbar.security.unlocked_props') }}</div>
            </div>

            <div class="bg-gray-900 rounded p-3">
                <div class="text-2xl font-bold text-purple-400"
                     x-text="getSensitiveData().length"></div>
                <div class="text-xs text-gray-500">{{ __('livewire-debugbar::debugbar.security.sensitive_data') }}</div>
            </div>
        </div>
    </div>
    {{-- Security Issues  --}}
    <div x-show="getSecurityIssues().length > 0" class="bg-gray-950 rounded-lg border border-gray-700">
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('livewire-debugbar::debugbar.security.security_issues') }}</h3>
        </div>

        <div class="p-4 space-y-3">
            <template x-for="issue in getSecurityIssues()" :key="issue.id">
                <div class="rounded border p-3"
                     x-bind:class="issue.level === 'critical' ? 'bg-red-900 border-red-700' : issue.level === 'high' ? 'bg-orange-900 border-orange-700' : 'bg-yellow-900 border-yellow-700'">

                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"
                                 x-bind:class="issue.level === 'critical' ? 'text-red-400' : issue.level === 'high' ? 'text-orange-400' : 'text-yellow-400'">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-sm" x-text="issue.title"></span>
                        </div>

                        <span class="text-xs px-2 py-1 rounded uppercase font-medium"
                              x-bind:class="issue.level === 'critical' ? 'bg-red-700 text-red-200' : issue.level === 'high' ? 'bg-orange-700 text-orange-200' : 'bg-yellow-700 text-yellow-200'"
                              x-text="issue.level"></span>
                    </div>

                    <p class="text-sm mb-3"
                       x-bind:class="issue.level === 'critical' ? 'text-red-300' : issue.level === 'high' ? 'text-orange-300' : 'text-yellow-300'"
                       x-text="issue.message"></p>

                    <div class="flex items-center justify-between">
                        <div class="text-xs"
                             x-bind:class="issue.level === 'critical' ? 'text-red-400' : issue.level === 'high' ? 'text-orange-400' : 'text-yellow-400'">
                            <span>{{ __('livewire-debugbar::debugbar.security.component') }} </span>
                            <code x-text="issue.component"></code>
                            <span x-show="issue.property"> • {{ __('livewire-debugbar::debugbar.security.property') }} </span>
                            <code x-show="issue.property" x-text="issue.property"></code>
                        </div>

                        <button @click="focusSecurityIssue(issue)"
                                class="text-xs px-2 py-1 rounded hover:bg-gray-700 transition-colors"
                                x-bind:class="issue.level === 'critical' ? 'text-red-400 hover:text-red-300' : issue.level === 'high' ? 'text-orange-400 hover:text-orange-300' : 'text-yellow-400 hover:text-yellow-300'">
                            {{ __('livewire-debugbar::debugbar.security.view_details') }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Unlocked Properties Analysis --}}
    <div x-show="getUnlockedProperties().length > 0" class="bg-gray-950 rounded-lg border border-gray-700">
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('livewire-debugbar::debugbar.security.unlocked_properties') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ __('livewire-debugbar::debugbar.security.unlocked_properties_desc') }}</p>
        </div>

        <div class="p-4 space-y-3">
            <template x-for="prop in getUnlockedProperties()" :key="prop.id">
                <div class="bg-gray-900 rounded border border-gray-700 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-mono text-blue-300" x-text="prop.property"></span>
                            <span x-show="prop.isId" class="px-1.5 py-0.5 bg-red-600 text-white rounded text-xs animate-pulse">{{ __('livewire-debugbar::debugbar.security.id_property') }}</span>
                            <span x-show="prop.isSensitive" class="px-1.5 py-0.5 bg-orange-600 text-white rounded text-xs">{{ __('livewire-debugbar::debugbar.security.sensitive') }}</span>
                        </div>

                        <div class="flex items-center space-x-2 text-xs text-gray-400">
                            <span x-text="prop.type"></span>
                            <code class="bg-gray-700 px-2 py-1 rounded" x-text="prop.component"></code>
                        </div>
                    </div>

                    <div x-show="prop.recommendations.length > 0" class="mt-2">
                        <details class="text-xs">
                            <summary class="cursor-pointer text-gray-400 hover:text-gray-300">{{ __('livewire-debugbar::debugbar.security.recommendations') }}</summary>
                            <ul class="mt-2 space-y-1 text-gray-400">
                                <template x-for="rec in prop.recommendations" :key="rec">
                                    <li class="flex items-start space-x-2">
                                        <span class="text-blue-400">•</span>
                                        <span x-text="rec"></span>
                                    </li>
                                </template>
                            </ul>
                        </details>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Sensitive Data Detection --}}
    <div x-show="getSensitiveData().length > 0" class="bg-gray-950 rounded-lg border border-gray-700">
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('livewire-debugbar::debugbar.security.sensitive_data_detected') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ __('livewire-debugbar::debugbar.security.sensitive_data_desc') }}</p>
        </div>

        <div class="p-4 space-y-3">
            <template x-for="item in getSensitiveData()" :key="item.id">
                <div class="bg-gray-900 rounded border border-orange-700 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-mono text-orange-300" x-text="item.property"></span>
                            <span class="px-1.5 py-0.5 bg-orange-600 text-white rounded text-xs" x-text="item.pattern"></span>
                        </div>

                        <code class="text-xs text-gray-400 bg-gray-700 px-2 py-1 rounded" x-text="item.component"></code>
                    </div>

                    <p class="text-xs text-orange-300 mb-2" x-text="item.reason"></p>

                    <div class="text-xs text-gray-400">
                        <strong>{{ __('livewire-debugbar::debugbar.security.recommendation') }}</strong> <span x-text="item.recommendation"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Security Best Practices --}}
    <div class="bg-gray-950 rounded-lg border border-gray-700 p-4">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ __('livewire-debugbar::debugbar.security.best_practices') }}</h3>

        <div class="space-y-3 text-xs">
            <div class="flex items-start space-x-3">
                <svg class="w-4 h-4 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="text-white">{{ __('livewire-debugbar::debugbar.security.lock_sensitive') }}</strong>
                    <p class="text-gray-400">{{ __('livewire-debugbar::debugbar.security.lock_sensitive_desc') }}</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <svg class="w-4 h-4 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="text-white">{{ __('livewire-debugbar::debugbar.security.validate_input') }}</strong>
                    <p class="text-gray-400">{{ __('livewire-debugbar::debugbar.security.validate_input_desc') }}</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <svg class="w-4 h-4 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="text-white">{{ __('livewire-debugbar::debugbar.security.minimize_exposed') }}</strong>
                    <p class="text-gray-400">{{ __('livewire-debugbar::debugbar.security.minimize_exposed_desc') }}</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <svg class="w-4 h-4 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="text-white">{{ __('livewire-debugbar::debugbar.security.use_authorization') }}</strong>
                    <p class="text-gray-400">{{ __('livewire-debugbar::debugbar.security.use_authorization_desc') }}</p>
                </div>
            </div>
        </div>
    </div>

</div>
