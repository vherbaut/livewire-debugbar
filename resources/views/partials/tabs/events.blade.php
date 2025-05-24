{{-- Events Tab --}}
<div x-show="activeTab === 'events'" class="ldb-tab-content">
    <div class="ldb-events-container">
        {{-- Events Header with Filters --}}
        <div class="ldb-events-header">
            <div class="ldb-events-filters">
                {{-- Search --}}
                <div class="ldb-events-search-container">
                    <svg class="ldb-events-search-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" 
                           x-model="eventSearch" 
                           @input="filterEvents()"
                           placeholder="{{ __('livewire-debugbar::debugbar.events.search_placeholder') }}"
                           class="ldb-events-search-input">
                </div>

                {{-- Event Type Filters --}}
                <div class="ldb-events-type-filters">
                    <button @click="eventTypeFilter = eventTypeFilter === 'all' ? 'dispatched' : 'all'"
                            class="ldb-events-type-filter"
                            :class="eventTypeFilter === 'dispatched' || eventTypeFilter === 'all' ? 'ldb-events-type-filter--active' : ''">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 011-1h.5a1.5 1.5 0 000-3H6a1 1 0 01-1-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.events.dispatched') }}
                        <span class="ldb-events-type-count" x-text="getEventsByType('dispatched').length"></span>
                    </button>
                    
                    <button @click="eventTypeFilter = eventTypeFilter === 'all' ? 'received' : 'all'"
                            class="ldb-events-type-filter"
                            :class="eventTypeFilter === 'received' || eventTypeFilter === 'all' ? 'ldb-events-type-filter--active' : ''">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.events.received') }}
                        <span class="ldb-events-type-count" x-text="getEventsByType('received').length"></span>
                    </button>
                    
                    <button @click="eventTypeFilter = eventTypeFilter === 'all' ? 'error' : 'all'"
                            class="ldb-events-type-filter"
                            :class="eventTypeFilter === 'error' || eventTypeFilter === 'all' ? 'ldb-events-type-filter--active' : ''">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.events.errors') }}
                        <span class="ldb-events-type-count" x-text="getEventsByType('error').length"></span>
                    </button>
                </div>

                {{-- Actions --}}
                <div class="ldb-events-actions">
                    <button @click="exportEvents()" 
                            class="ldb-events-action-btn"
                            title="{{ __('livewire-debugbar::debugbar.events.export_events') }}">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.events.export') }}
                    </button>
                    
                    <button @click="clearEvents()" 
                            class="ldb-events-action-btn ldb-events-action-btn--danger"
                            title="{{ __('livewire-debugbar::debugbar.events.clear_all') }}">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('livewire-debugbar::debugbar.events.clear') }}
                    </button>
                </div>
            </div>

            {{-- Stats Bar --}}
            <div class="ldb-events-stats">
                <div class="ldb-events-stat">
                    <span class="ldb-events-stat-label">{{ __('livewire-debugbar::debugbar.events.total_events') }}</span>
                    <span class="ldb-events-stat-value ldb-events-stat-value--primary" x-text="getFilteredEvents().length"></span>
                </div>
                <div class="ldb-events-stat">
                    <span class="ldb-events-stat-label">{{ __('livewire-debugbar::debugbar.events.unique_events') }}</span>
                    <span class="ldb-events-stat-value ldb-events-stat-value--success" x-text="getUniqueEventNames().length"></span>
                </div>
                <div class="ldb-events-stat">
                    <span class="ldb-events-stat-label">{{ __('livewire-debugbar::debugbar.events.avg_time') }}</span>
                    <span class="ldb-events-stat-value ldb-events-stat-value--warning" x-text="getAverageEventTime() + 'ms'"></span>
                </div>
            </div>
        </div>

        {{-- Events Timeline --}}
        <div class="ldb-events-timeline">
            {{-- Empty State --}}
            <div x-show="getFilteredEvents().length === 0" class="ldb-events-timeline-empty">
                <svg class="ldb-events-timeline-empty-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 011-1h.5a1.5 1.5 0 000-3H6a1 1 0 01-1-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                </svg>
                <h3 class="ldb-events-timeline-empty-title">{{ __('livewire-debugbar::debugbar.events.no_events_title') }}</h3>
                <p class="ldb-events-timeline-empty-desc">{{ __('livewire-debugbar::debugbar.events.no_events_desc') }}</p>
            </div>

            {{-- Events List --}}
            <div x-show="getFilteredEvents().length > 0">
                {{-- Group events by time --}}
                <template x-for="(group, groupIndex) in getGroupedEvents()" :key="groupIndex">
                    <div class="ldb-events-group">
                        <div class="ldb-events-group-header">
                            <div class="ldb-events-group-line"></div>
                            <span x-text="group.label"></span>
                            <div class="ldb-events-group-line"></div>
                        </div>

                        <template x-for="event in group.events" :key="event.id">
                            <div class="ldb-event-item">
                                {{-- Timeline Dot --}}
                                <div class="ldb-event-timeline-dot"
                                     :class="{
                                         'ldb-event-timeline-dot--dispatched': event.type === 'dispatched',
                                         'ldb-event-timeline-dot--received': event.type === 'received',
                                         'ldb-event-timeline-dot--processed': event.type === 'processed',
                                         'ldb-event-timeline-dot--error': event.error
                                     }"></div>

                                {{-- Event Card --}}
                                <div class="ldb-event-card"
                                     :class="{
                                         'ldb-event-card--expanded': expandedEvents[event.id],
                                         'ldb-event-card--error': event.error
                                     }"
                                     x-data="{ 
                                         editing: false, 
                                         activeDetailTab: 'params',
                                         editFields: []
                                     }"
                                     x-init="$data.editing = false">
                                    
                                    {{-- Event Header --}}
                                    <div class="ldb-event-header" @click="expandedEvents[event.id] = !expandedEvents[event.id]">
                                        <div class="ldb-event-header-main">
                                            <div class="ldb-event-info">
                                                <span class="ldb-event-name" x-text="event.name || event.event"></span>
                                                <span class="ldb-event-source">
                                                    <svg class="ldb-event-source-icon" fill="currentColor" viewBox="0 0 20 20">
                                                        <path x-show="event.component" fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        <path x-show="!event.component" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v10H5V5z"/>
                                                    </svg>
                                                    <span x-text="event.component?.name || 'Server'"></span>
                                                </span>
                                            </div>

                                            <div class="ldb-event-actions" @click.stop>
                                                <button @click="copyEventData(event)"
                                                        class="ldb-event-action-btn ldb-event-action-btn--copy livewire-debugbar__tooltip"
                                                        data-tooltip="{{ __('livewire-debugbar::debugbar.events.copy_data') }}">
                                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                                                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
                                                    </svg>
                                                </button>
                                                
                                                <button @click="$data.editing = true; initEditFields(event)"
                                                        x-show="event.params && Object.keys(event.params).length > 0"
                                                        class="ldb-event-action-btn ldb-event-action-btn--edit livewire-debugbar__tooltip"
                                                        data-tooltip="{{ __('livewire-debugbar::debugbar.events.edit_params') }}">
                                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                                    </svg>
                                                </button>
                                                
                                                <button @click="replayEvent(event)"
                                                        class="ldb-event-action-btn ldb-event-action-btn--replay livewire-debugbar__tooltip"
                                                        data-tooltip="{{ __('livewire-debugbar::debugbar.events.replay') }}">
                                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="ldb-event-meta">
                                            <span class="ldb-event-time">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <span x-text="formatEventTime(event.timestamp)"></span>
                                            </span>
                                            
                                            <span x-show="event.duration" class="ldb-event-duration">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                                                    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                                                </svg>
                                                <span x-text="event.duration + 'ms'"></span>
                                            </span>
                                            
                                            <span x-show="event.commitId" class="ldb-event-commit" x-text="'#' + event.commitId.substring(0, 6)"></span>
                                        </div>
                                    </div>

                                    {{-- Event Details (Expanded) --}}
                                    <div x-show="expandedEvents[event.id]" x-collapse class="ldb-event-details">
                                        {{-- Detail Tabs --}}
                                        <div class="ldb-event-details-tabs">
                                            <button @click="activeDetailTab = 'params'"
                                                    class="ldb-event-details-tab"
                                                    :class="activeDetailTab === 'params' ? 'ldb-event-details-tab--active' : ''">
                                                {{ __('livewire-debugbar::debugbar.events.parameters') }}
                                            </button>
                                            <button @click="activeDetailTab = 'impact'"
                                                    class="ldb-event-details-tab"
                                                    :class="activeDetailTab === 'impact' ? 'ldb-event-details-tab--active' : ''">
                                                {{ __('livewire-debugbar::debugbar.events.impact') }}
                                            </button>
                                            <button @click="activeDetailTab = 'raw'"
                                                    class="ldb-event-details-tab"
                                                    :class="activeDetailTab === 'raw' ? 'ldb-event-details-tab--active' : ''">
                                                {{ __('livewire-debugbar::debugbar.events.raw_data') }}
                                            </button>
                                        </div>

                                        <div class="ldb-event-details-content">
                                            {{-- Parameters Tab --}}
                                            <div x-show="activeDetailTab === 'params' && !$data.editing">
                                                <div class="ldb-event-params">
                                                    <div x-show="!event.params || Object.keys(event.params).length === 0" class="ldb-event-params-empty">
                                                        {{ __('livewire-debugbar::debugbar.events.no_parameters') }}
                                                    </div>
                                                    
                                                    <template x-for="(value, key) in event.params" :key="key">
                                                        <div class="ldb-event-param-item">
                                                            <span class="ldb-event-param-key" x-text="key"></span>
                                                            <span class="ldb-event-param-value" x-text="formatParamValue(value)"></span>
                                                            <span class="ldb-event-param-type" x-text="getParamType(value)"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Edit Mode --}}
                                            <div x-show="activeDetailTab === 'params' && $data.editing" class="ldb-event-edit-container">
                                                <div class="ldb-event-edit-header">
                                                    <span class="ldb-event-edit-title">{{ __('livewire-debugbar::debugbar.events.edit_parameters') }}</span>
                                                    <div class="ldb-event-edit-actions">
                                                        <button @click="saveAndReplayEvent(event); $data.editing = false"
                                                                class="ldb-btn ldb-btn--primary ldb-btn--sm">
                                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ __('livewire-debugbar::debugbar.events.save_replay') }}
                                                        </button>
                                                        <button @click="$data.editing = false"
                                                                class="ldb-btn ldb-btn--secondary ldb-btn--sm">
                                                            {{ __('livewire-debugbar::debugbar.components.cancel') }}
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="ldb-event-edit-fields">
                                                    {{-- Existing Parameters --}}
                                                    <template x-for="(field, fieldKey) in editFields" :key="fieldKey">
                                                        <div class="ldb-event-edit-field" :class="field.isNew ? 'ldb-event-edit-field--new' : ''">
                                                            <div class="ldb-event-edit-field-header">
                                                                <label class="ldb-event-edit-field-label">
                                                                    <span x-text="field.isNew ? 'New Parameter' : fieldKey"></span>
                                                                </label>
                                                                <button x-show="field.isNew" @click="removeParam(fieldKey)" class="ldb-event-edit-field-remove" title="Remove">
                                                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                            
                                                            <div class="ldb-event-edit-field-controls">
                                                                {{-- Key input for new parameters --}}
                                                                <input x-show="field.isNew"
                                                                       type="text"
                                                                       placeholder="Parameter name"
                                                                       @input="field.newKey = $event.target.value"
                                                                       class="ldb-event-edit-field-input ldb-event-edit-field-input--key">
                                                                
                                                                {{-- Type selector --}}
                                                                <select @change="updateEditField(fieldKey, field.value, $event.target.value); field.type = $event.target.value"
                                                                        x-model="field.type"
                                                                        class="ldb-event-edit-field-type">
                                                                    <option value="string">String</option>
                                                                    <option value="number">Number</option>
                                                                    <option value="boolean">Boolean</option>
                                                                    <option value="array">Array</option>
                                                                    <option value="object">Object</option>
                                                                </select>
                                                                
                                                                {{-- Boolean --}}
                                                                <select x-show="field.type === 'boolean'"
                                                                        @change="updateEditField(fieldKey, $event.target.value, field.type)"
                                                                        x-model="field.value"
                                                                        class="ldb-event-edit-field-input">
                                                                    <option :value="true">true</option>
                                                                    <option :value="false">false</option>
                                                                </select>
                                                                
                                                                {{-- Number --}}
                                                                <input x-show="field.type === 'number'"
                                                                       type="number"
                                                                       @input="updateEditField(fieldKey, $event.target.value, field.type)"
                                                                       :value="field.value"
                                                                       class="ldb-event-edit-field-input">
                                                                
                                                                {{-- String --}}
                                                                <input x-show="field.type === 'string'"
                                                                       type="text"
                                                                       @input="updateEditField(fieldKey, $event.target.value, field.type)"
                                                                       :value="field.value"
                                                                       class="ldb-event-edit-field-input">
                                                                
                                                                {{-- Array/Object --}}
                                                                <textarea x-show="field.type === 'array' || field.type === 'object'"
                                                                          @input="updateEditField(fieldKey, $event.target.value, field.type)"
                                                                          :value="typeof field.value === 'object' ? JSON.stringify(field.value, null, 2) : field.value"
                                                                          rows="4"
                                                                          class="ldb-event-edit-field-input ldb-event-edit-field-textarea"
                                                                          placeholder='{"key": "value"}'></textarea>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    
                                                    {{-- Add new parameter button --}}
                                                    <button @click="addNewParam()" class="ldb-event-edit-add-param">
                                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                                        </svg>
                                                        {{ __('livewire-debugbar::debugbar.events.add_parameter') }}
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Impact Tab --}}
                                            <div x-show="activeDetailTab === 'impact'" class="ldb-event-impact">
                                                <div class="ldb-event-impact-item">
                                                    <div class="ldb-event-impact-header">
                                                        <span class="ldb-event-impact-title">{{ __('livewire-debugbar::debugbar.events.affected_components') }}</span>
                                                        <span class="ldb-event-impact-badge ldb-event-impact-badge--component" x-text="getAffectedComponents(event).length + ' components'"></span>
                                                    </div>
                                                    <p class="ldb-event-impact-desc" x-text="getAffectedComponentsDescription(event)"></p>
                                                </div>
                                                
                                                <div x-show="getRelatedMethods(event).length > 0" class="ldb-event-impact-item">
                                                    <div class="ldb-event-impact-header">
                                                        <span class="ldb-event-impact-title">{{ __('livewire-debugbar::debugbar.events.triggered_methods') }}</span>
                                                        <span class="ldb-event-impact-badge ldb-event-impact-badge--method" x-text="getRelatedMethods(event).length + ' methods'"></span>
                                                    </div>
                                                    <p class="ldb-event-impact-desc" x-text="getRelatedMethodsDescription(event)"></p>
                                                </div>
                                                
                                                <div x-show="getModifiedProperties(event).length > 0" class="ldb-event-impact-item">
                                                    <div class="ldb-event-impact-header">
                                                        <span class="ldb-event-impact-title">{{ __('livewire-debugbar::debugbar.events.modified_properties') }}</span>
                                                        <span class="ldb-event-impact-badge ldb-event-impact-badge--property" x-text="getModifiedProperties(event).length + ' properties'"></span>
                                                    </div>
                                                    <p class="ldb-event-impact-desc" x-text="getModifiedPropertiesDescription(event)"></p>
                                                </div>
                                            </div>

                                            {{-- Raw Data Tab --}}
                                            <div x-show="activeDetailTab === 'raw'" class="ldb-event-raw-data">
                                                <div class="ldb-event-raw-toolbar">
                                                    <button @click="copyRawData(event)" class="ldb-event-raw-btn">
                                                        {{ __('livewire-debugbar::debugbar.events.copy') }}
                                                    </button>
                                                    <button @click="downloadRawData(event)" class="ldb-event-raw-btn">
                                                        {{ __('livewire-debugbar::debugbar.events.download') }}
                                                    </button>
                                                </div>
                                                <div class="ldb-event-raw-content">
                                                    <pre><code x-text="JSON.stringify(cleanEventForExport(event), null, 2)"></code></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Event Replay Status (Toast Notification) --}}
    <div x-show="eventReplayStatus"
         x-transition
         class="ldb-event-replay-status"
         :class="{
             'ldb-event-replay-status--success': eventReplayStatus?.type === 'success',
             'ldb-event-replay-status--error': eventReplayStatus?.type === 'error'
         }"
         x-init="setTimeout(() => eventReplayStatus = null, 3000)">
        <svg class="ldb-event-replay-icon" fill="currentColor" viewBox="0 0 20 20">
            <path x-show="!eventReplayStatus || eventReplayStatus.type === 'loading'" fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
            <path x-show="eventReplayStatus?.type === 'success'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            <path x-show="eventReplayStatus?.type === 'error'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="ldb-event-replay-text" x-text="eventReplayStatus?.message"></span>
    </div>
</div>