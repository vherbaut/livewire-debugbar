{{-- Properties --}}
<div x-show="Object.keys(component.properties).length > 0">
    <div class="ldb-properties-header">
        <h5 class="ldb-properties-title">{{ __('livewire-debugbar::debugbar.components.properties') }}</h5>
        <div class="ldb-properties-count">
            <span x-text="Object.keys(component.properties).length"></span>
            {{ __('livewire-debugbar::debugbar.components.properties_count') }}
        </div>
    </div>

    <div class="ldb-properties-list">
        <template x-for="(property, propName) in component.properties" :key="propName">
            <div class="ldb-property-item"
                 x-data="{ editValue: '', showValue: false }">
                {{-- Property header --}}
                <div class="ldb-property-header">
                    <div class="ldb-property-header-content">
                        <div class="ldb-property-info">
                            <span class="ldb-property-name" x-text="propName"></span>
                            {{-- Property badges --}}
                            <span x-show="property.locked" class="ldb-property-badge ldb-property-badge--locked">🔒 {{ __('livewire-debugbar::debugbar.components.locked') }}</span>
                            <span x-show="!property.locked" class="ldb-property-badge ldb-property-badge--unlocked">🔓 {{ __('livewire-debugbar::debugbar.components.unlocked') }}</span>
                            <span x-show="property.isModel" class="ldb-property-badge ldb-property-badge--model">{{ __('livewire-debugbar::debugbar.components.model') }}</span>
                            <span x-show="property.isLazy" class="ldb-property-badge ldb-property-badge--lazy">{{ __('livewire-debugbar::debugbar.components.lazy') }}</span>
                            <span x-show="property.isReactive" class="ldb-property-badge ldb-property-badge--reactive">{{ __('livewire-debugbar::debugbar.components.reactive') }}</span>
                            {{-- Security warning for unlocked ID properties --}}
                            <span x-show="property.isId && !property.locked"
                                  class="ldb-property-badge ldb-property-badge--security-risk">
                                ⚠️ {{ __('livewire-debugbar::debugbar.components.security_risk') }}
                            </span>
                        </div>
                        {{-- Property meta info and actions --}}
                        <div class="ldb-property-meta">
                            <span class="ldb-property-type"
                                  :class="{
                                      'ldb-property-type--string': property.type === 'string',
                                      'ldb-property-type--number': property.type === 'integer' || property.type === 'double',
                                      'ldb-property-type--boolean': property.type === 'boolean',
                                      'ldb-property-type--array': property.type === 'array',
                                      'ldb-property-type--object': property.type === 'object',
                                      'ldb-property-type--null': property.type === 'null' || !['string', 'integer', 'double', 'boolean', 'array', 'object'].includes(property.type)
                                  }"
                                  x-text="property.type"></span>
                            <span x-text="property.size + ' bytes'" class="ldb-property-size"></span>
                            {{-- Actions --}}
                            <div class="ldb-property-actions">
                                <button @click="showValue = !showValue"
                                        class="ldb-property-action-btn livewire-debugbar__tooltip"
                                        :data-tooltip="showValue ? '{{ __('livewire-debugbar::debugbar.components.hide_value') }}' : '{{ __('livewire-debugbar::debugbar.components.show_value') }}'">
                                    <svg x-show="!showValue" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <svg x-show="showValue" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                    </svg>
                                </button>

                                <button @click="navigator.clipboard.writeText(JSON.stringify(property.value, null, 2))"
                                        class="ldb-property-action-btn ldb-property-action-btn--copy livewire-debugbar__tooltip"
                                        data-tooltip="{{ __('livewire-debugbar::debugbar.components.copy_value') }}">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Property value --}}
                <div x-show="showValue" class="ldb-property-value">
                    {{-- Recursive editing interface --}}
                    <div x-show="!property.locked"
                         x-data="{
                             componentId: null,
                             propertyData: {},
                             fields: [],
                             saving: false,
                             saved: false,
                             error: null,
                             init() {
                                 // Get component ID from parent context - traverse up to find the component context
                                 let parent = this;
                                 while (parent && !parent.id) {
                                     parent = parent.$parent;
                                 }
                                 this.componentId = parent ? parent.id : null;
                                 console.log('Component ID found:', this.componentId);

                                 this.propertyData = JSON.parse(JSON.stringify(property.value));
                                 this.fields = this.flattenObject(this.propertyData, '');
                             },
                             flattenObject(obj, prefix) {
                                 let fields = [];

                                 if (Array.isArray(obj)) {
                                     obj.forEach((item, index) => {
                                         const path = prefix ? `${prefix}[${index}]` : `[${index}]`;
                                         if (item && typeof item === 'object' && !Array.isArray(item)) {
                                             fields = fields.concat(this.flattenObject(item, path));
                                         } else {
                                             fields.push({
                                                 path: path,
                                                 value: item,
                                                 type: this.getType(item),
                                                 parentPath: prefix,
                                                 key: index
                                             });
                                         }
                                     });
                                 } else if (obj && typeof obj === 'object') {
                                     Object.entries(obj).forEach(([key, value]) => {
                                         const path = prefix ? `${prefix}.${key}` : key;
                                         if (value && typeof value === 'object' && !Array.isArray(value)) {
                                             fields = fields.concat(this.flattenObject(value, path));
                                         } else {
                                             fields.push({
                                                 path: path,
                                                 value: value,
                                                 type: this.getType(value),
                                                 parentPath: prefix,
                                                 key: key
                                             });
                                         }
                                     });
                                 } else {
                                     fields.push({
                                         path: prefix || 'value',
                                         value: obj,
                                         type: this.getType(obj),
                                         parentPath: '',
                                         key: 'value'
                                     });
                                 }

                                 return fields;
                             },
                             getType(value) {
                                 if (value === null) return 'null';
                                 if (typeof value === 'boolean') return 'boolean';
                                 if (typeof value === 'number') return 'number';
                                 if (Array.isArray(value)) return 'array';
                                 if (typeof value === 'object') return 'object';
                                 return 'string';
                             },
                             updateFieldValue(field, newValue) {
                                 // Handle simple values (no nesting)
                                 if (field.path === 'value') {
                                     switch(field.type) {
                                         case 'boolean':
                                             this.propertyData = newValue === 'true';
                                             break;
                                         case 'number':
                                             this.propertyData = parseFloat(newValue) || 0;
                                             break;
                                         case 'null':
                                             this.propertyData = null;
                                             break;
                                         default:
                                             this.propertyData = newValue;
                                     }
                                     field.value = this.propertyData;
                                     return;
                                 }

                                 // Handle nested objects/arrays
                                 const pathParts = field.path.split(/[\.\[\]]+/).filter(Boolean);
                                 let current = this.propertyData;

                                 // Navigate to parent
                                 for (let i = 0; i < pathParts.length - 1; i++) {
                                     if (current[pathParts[i]] === undefined) {
                                         console.error('Could not navigate to path:', field.path, 'at part:', pathParts[i]);
                                         return;
                                     }
                                     current = current[pathParts[i]];
                                 }

                                 const lastKey = pathParts[pathParts.length - 1];

                                 // Convert and set value
                                 let convertedValue;
                                 switch(field.type) {
                                     case 'boolean':
                                         convertedValue = newValue === 'true';
                                         break;
                                     case 'number':
                                         convertedValue = parseFloat(newValue) || 0;
                                         break;
                                     case 'null':
                                         convertedValue = null;
                                         break;
                                     default:
                                         convertedValue = newValue;
                                 }

                                 current[lastKey] = convertedValue;
                                 field.value = convertedValue;
                             },
                             async saveProperty() {
                                 this.saving = true;
                                 this.saved = false;
                                 this.error = null;

                                 try {
                                     // Find the Livewire component by ID
                                     if (!this.componentId) {
                                         throw new Error('Component ID not found');
                                     }

                                     const $wire = window.Livewire.find(this.componentId);
                                     if ($wire && $wire.$set) {
                                         // Update the property using $set method
                                         await $wire.$set(propName, this.propertyData);

                                         // Update the parent component's property data
                                         property.value = this.propertyData;
                                         property.size = JSON.stringify(this.propertyData).length;

                                         this.saved = true;
                                         console.log('Property updated:', propName, this.propertyData);

                                         // Hide success message after 2 seconds
                                         setTimeout(() => {
                                             this.saved = false;
                                         }, 2000);
                                     } else {
                                         throw new Error('Could not find Livewire component or $set method');
                                     }
                                 } catch (e) {
                                     console.error('Error updating property:', e);
                                     this.error = e.message;
                                 } finally {
                                     this.saving = false;
                                 }
                             }
                         }"
                         x-init="init()">

                        <div class="ldb-property-edit-container"
                             :class="{
                                 'ldb-property-edit-container--error': error,
                                 'ldb-property-edit-container--saved': saved
                             }">
                            <div class="ldb-property-edit-header">
                                <div class="ldb-property-edit-header-info">
                                    <span class="ldb-property-edit-title">{{ __('livewire-debugbar::debugbar.components.edit_properties') }}</span>
                                    <span x-show="saved" class="ldb-property-edit-saved" x-transition>
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ __('livewire-debugbar::debugbar.components.saved') }}
                                    </span>
                                    <span x-show="error" class="ldb-property-edit-error" x-text="error" x-transition></span>
                                </div>
                                <button @click="saveProperty()"
                                        :disabled="saving"
                                        class="ldb-property-save-btn">
                                    <svg x-show="!saving" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z"/>
                                    </svg>
                                    <svg x-show="saving" class="animate-spin" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="saving ? '{{ __('livewire-debugbar::debugbar.components.saving') }}' : '{{ __('livewire-debugbar::debugbar.components.save_all') }}'"></span>
                                </button>
                            </div>

                            {{-- Field list --}}
                            <div class="ldb-property-fields">
                                <div class="ldb-property-fields-list">
                                    <template x-for="field in fields" :key="field.path">
                                        <div class="ldb-property-field">
                                            <label class="ldb-property-field-label"
                                                   x-text="field.path"></label>

                                            {{-- Boolean select --}}
                                            <select x-show="field.type === 'boolean'"
                                                    :value="field.value !== null ? field.value.toString() : 'false'"
                                                    @change="updateFieldValue(field, $event.target.value)"
                                                    class="ldb-property-field-input"
                                                    :class="{ 'ldb-property-field-input--error': error }">
                                                <option value="true">{{ __('livewire-debugbar::debugbar.components.yes') }}</option>
                                                <option value="false">{{ __('livewire-debugbar::debugbar.components.no') }}</option>
                                            </select>

                                            {{-- Number input --}}
                                            <input x-show="field.type === 'number'"
                                                   type="number"
                                                   :value="field.value !== null ? field.value : 0"
                                                   @input="updateFieldValue(field, $event.target.value)"
                                                   class="ldb-property-field-input"
                                                   :class="{ 'ldb-property-field-input--error': error }">

                                            {{-- Text input --}}
                                            <input x-show="field.type === 'string'"
                                                   type="text"
                                                   :value="field.value !== null ? field.value : ''"
                                                   @input="updateFieldValue(field, $event.target.value)"
                                                   class="ldb-property-field-input"
                                                   :class="{ 'ldb-property-field-input--error': error }">

                                            {{-- Null select - can change type --}}
                                            <select x-show="field.type === 'null'"
                                                    @change="field.type = $event.target.value; field.value = field.type === 'boolean' ? 'false' : (field.type === 'number' ? '0' : ''); updateFieldValue(field, field.value)"
                                                    class="ldb-property-field-input"
                                                    :class="{ 'ldb-property-field-input--error': error }">
                                                <option value="null">null</option>
                                                <option value="string">→ string</option>
                                                <option value="number">→ number</option>
                                                <option value="boolean">→ boolean</option>
                                            </select>

                                            <span class="ldb-property-field-type"
                                                  :class="{
                                                      'ldb-property-field-type--string': field.type === 'string',
                                                      'ldb-property-field-type--number': field.type === 'number',
                                                      'ldb-property-field-type--boolean': field.type === 'boolean',
                                                      'ldb-property-field-type--null': field.type === 'null'
                                                  }"
                                                  x-text="field.type"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Display mode (read-only) --}}
                    <div class="ldb-property-display">
                        <pre class="ldb-property-display-code"><code x-text="JSON.stringify(property.value, null, 2)"></code></pre>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>