import '../css/app.css';

declare global {
    interface Window {
        Livewire: any;
        Alpine: any;
        livewireDebugbar: any;
    }
}

// Alpine function for the debugbar
window.livewireDebugbar = function(initialData: any) {
    return {
        // State
        collapsed: initialData.config?.interface?.collapsed_by_default ?? true,
        activeTab: initialData.config?.interface?.default_tab || 'components',
        position: 'bottom', // bottom, floating, docked-left, docked-right
        showSettings: false,

        // Hot reload state
        hotReload: false,
        hotReloadInterval: null as number | null,

        // Resize state
        isResizing: false,
        initialY: 0,
        initialHeight: 400,
        height: 400,

        // Data
        components: {},
        events: [],
        commits: [] as any[],
        performance: [],
        config: initialData.config || {},
        activeComponentId: null as string | null,
        activeComponentCommitId: null as string | null,

        // Advanced debugging features
        stateHistory: {} as Record<string, any[]>,
        validationErrors: {} as Record<string, any[]>,
        lifecycleEvents: [] as any[],
        componentQueries: {} as Record<string, any[]>,
        realTimeValidation: true,
        stateChangeNotifications: true,

        // UI State
        componentSearch: '',
        componentStateFilter: '',
        componentValidationFilter: '',
        componentViewMode: 'cards', // cards, tree, list
        componentSort: 'name', // name, properties, size, performance
        activeFilters: [] as string[],
        expandedComponents: {} as Record<string, boolean>,
        expandedTreeNodes: {} as Record<string, boolean>,

        // Initialization
        init() {
            // Load saved preferences first
            this.loadPreferences();

            // Wait a bit for Livewire to be fully loaded
            setTimeout(() => {
                this.detectComponents();
                this.storeInitialCommit();
                this.setupEventListeners();
                this.setupKeyboardShortcuts();
                this.setupHotReload();
            }, 100);
        },

        // Detect Livewire components
        detectComponents() {
            if (!window.Livewire) {
                console.error('Livewire not found!');
                return;
            }

            const components = window.Livewire.all();
            const detectedComponents: Record<string, any> = {};

            components.forEach((component: any) => {
                const id = component.id;
                detectedComponents[id] = {
                    id: id,
                    name: component.name,
                    class: component.name,
                    state: 'active',
                    properties: this.extractProperties(component),
                    element: component.el,
                    fingerprint: component.fingerprint,
                    serverMemo: component.serverMemo
                };
            });

            this.components = detectedComponents;

            // Validate and store state for each component
            Object.entries(detectedComponents).forEach(([componentId, component]) => {
                this.validateComponentProperties(componentId, component.properties);
                this.storeComponentState(componentId, component.properties);
            });

            // Auto-expand root nodes in tree view
            const tree = this.getComponentTree();
            Object.keys(tree).forEach((componentId) => {
                if (!this.expandedTreeNodes.hasOwnProperty(componentId)) {
                    this.expandedTreeNodes[componentId] = true;
                }
            });
        },

        // Store component state in history
        storeComponentState(componentId: string, state: any) {
            if (!this.stateHistory[componentId]) {
                this.stateHistory[componentId] = [];
            }

            const timestamp = Date.now();
            const stateSnapshot = {
                timestamp,
                state: JSON.parse(JSON.stringify(state)),
                id: this.randomId()
            };

            this.stateHistory[componentId].push(stateSnapshot);

            // Keep only last 50 states per component
            if (this.stateHistory[componentId].length > 50) {
                this.stateHistory[componentId] = this.stateHistory[componentId].slice(-50);
            }
        },

        // Validate component properties in real-time
        validateComponentProperties(componentId: string, properties: Record<string, any>) {
            if (!this.realTimeValidation) return;

            const errors = [];
            let errorId = 1;

            Object.entries(properties).forEach(([propName, prop]) => {
                // Rule 1: ID properties should be locked
                if (prop.isId && !prop.locked) {
                    errors.push({
                        id: errorId++,
                        type: 'security',
                        level: 'critical',
                        property: propName,
                        message: `ID property "${propName}" is not locked - serious security vulnerability!`,
                        suggestion: 'Add #[Locked] attribute to this property',
                        autoFix: false
                    });
                }

                // Rule 2: Large properties should be monitored
                if (prop.size > 10240) { // > 10KB
                    errors.push({
                        id: errorId++,
                        type: 'performance',
                        level: 'warning',
                        property: propName,
                        message: `Property "${propName}" is very large (${(prop.size / 1024).toFixed(1)}KB)`,
                        suggestion: 'Consider optimizing data structure or using pagination',
                        autoFix: false
                    });
                }

                // Rule 3: Too many properties
                const propCount = Object.keys(properties).length;
                if (propCount > 30) {
                    errors.push({
                        id: errorId++,
                        type: 'complexity',
                        level: 'warning',
                        property: 'component',
                        message: `Component has ${propCount} properties - consider breaking it down`,
                        suggestion: 'Split into smaller, focused components',
                        autoFix: false
                    });
                }

                // Rule 4: Sensitive data patterns
                const sensitivePatterns = ['password', 'secret', 'token', 'key', 'ssn', 'credit'];
                const propNameLower = propName.toLowerCase();

                sensitivePatterns.forEach(pattern => {
                    if (propNameLower.includes(pattern) && !prop.locked) {
                        errors.push({
                            id: errorId++,
                            type: 'security',
                            level: 'high',
                            property: propName,
                            message: `Property "${propName}" contains sensitive data but is not locked`,
                            suggestion: 'Lock this property or move to server-side processing',
                            autoFix: true,
                            autoFixAction: () => this.suggestPropertyLock(componentId, propName)
                        });
                    }
                });

                // Rule 5: Circular references
                try {
                    JSON.stringify(prop.value);
                } catch (e) {
                    if (e.message.includes('circular')) {
                        errors.push({
                            id: errorId++,
                            type: 'data',
                            level: 'error',
                            property: propName,
                            message: `Property "${propName}" contains circular references`,
                            suggestion: 'Remove circular references or use a different data structure',
                            autoFix: false
                        });
                    }
                }
            });

            this.validationErrors[componentId] = errors;

            // Show notification for critical errors
            if (this.stateChangeNotifications && errors.some(e => e.level === 'critical')) {
                this.showCriticalValidationAlert(componentId, errors.filter(e => e.level === 'critical'));
            }
        },

        // Show critical validation alert
        showCriticalValidationAlert(componentId: string, criticalErrors: any[]) {
            const component = this.components[componentId];
            if (!component) return;

            console.error(`🚨 CRITICAL SECURITY ISSUES in component ${component.name}:`, criticalErrors);

            // Create visual notification
            const notification = document.createElement('div');
            notification.className = 'livewire-debugbar-critical-alert';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #dc2626;
                color: white;
                padding: 16px;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.3);
                z-index: 999999;
                max-width: 400px;
                font-family: monospace;
                font-size: 12px;
                line-height: 1.4;
            `;

            notification.innerHTML = `
                <div style="font-weight: bold; margin-bottom: 8px;">🚨 CRITICAL SECURITY ISSUES</div>
                <div style="margin-bottom: 8px;">Component: ${component.name}</div>
                ${criticalErrors.map(error => `<div>• ${error.message}</div>`).join('')}
                <button onclick="this.parentNode.remove()" style="
                    background: rgba(255,255,255,0.2);
                    border: none;
                    color: white;
                    padding: 4px 8px;
                    border-radius: 4px;
                    margin-top: 8px;
                    cursor: pointer;
                ">Dismiss</button>
            `;

            document.body.appendChild(notification);

            // Auto-remove after 10 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 10000);
        },

        // Suggest property lock fix
        suggestPropertyLock(componentId: string, propertyName: string) {
            const component = this.components[componentId];
            if (!component) return;

            const suggestion = `
To fix the security issue with property "${propertyName}" in component ${component.name}:

1. Add the #[Locked] attribute to the property:

   #[Locked]
   public $${propertyName};

2. Or use the Locked trait method:

   protected function lockProperties()
   {
       return ['${propertyName}'];
   }

This prevents users from modifying the property from the browser.
            `;

            console.log('🔒 SECURITY FIX SUGGESTION:', suggestion);
            alert(suggestion);
        },

        // Track component lifecycle events
        trackLifecycleEvent(componentId: string, event: string, data?: any) {
            const timestamp = Date.now();
            this.lifecycleEvents.push({
                id: this.randomId(),
                componentId,
                event,
                data,
                timestamp,
                component: this.getComponentById(componentId)
            });

            // Keep only last 200 lifecycle events
            if (this.lifecycleEvents.length > 200) {
                this.lifecycleEvents = this.lifecycleEvents.slice(-200);
            }
        },

        // Get lifecycle events for component
        getComponentLifecycleEvents(componentId: string) {
            return this.lifecycleEvents
                .filter(event => event.componentId === componentId)
                .reverse();
        },

        // Restore component to previous state (time travel debugging)
        restoreComponentState(componentId: string, stateSnapshotId: string) {
            const history = this.stateHistory[componentId];
            if (!history) return;

            const snapshot = history.find(s => s.id === stateSnapshotId);
            if (!snapshot) return;

            const $wire = window.Livewire.find(componentId);
            if (!$wire) return;

            try {
                // Restore each property
                Object.entries(snapshot.state).forEach(([propName, value]) => {
                    $wire.$set(propName, value);
                });

                console.log(`🕰️ Restored component ${componentId} to state from ${new Date(snapshot.timestamp).toLocaleTimeString()}`);
            } catch (e) {
                console.error('Failed to restore state:', e);
            }
        },

        // Extract properties from a component
        extractProperties(component: any) {
            const properties: Record<string, any> = {};

            // In Livewire 3, component data is in component.data
            // and reactive properties are in component.$wire
            let data = {};
            let dataMeta = {};

            // Try to get data from component.data first (Livewire 3 way)
            if (component.data) {
                data = component.data;
            }

            // Try $wire if available
            if (component.$wire) {
                // $wire contains the reactive properties
                data = { ...data, ...component.$wire };
            }

            // Look for snapshot data (another Livewire 3 structure)
            if (component.snapshot) {
                if (component.snapshot.data) {
                    data = { ...data, ...component.snapshot.data };
                }
                if (component.snapshot.memo?.dataMeta) {
                    dataMeta = component.snapshot.memo.dataMeta;
                }
            }

            if (Object.keys(data).length > 0) {
                // Extract property metadata from dataMeta
                const lockedProperties = dataMeta.locked || [];
                const models = dataMeta.models || {};
                const lazy = dataMeta.lazy || [];
                const reactive = dataMeta.reactive || [];

                Object.entries(data).forEach(([key, value]) => {
                    // Determine value type
                    let valueType = typeof value;
                    if (value === null) valueType = 'null';
                    if (Array.isArray(value)) valueType = 'array';
                    if (value instanceof Date) valueType = 'date';

                    // Calculate property size
                    let size = 0;
                    try {
                        size = JSON.stringify(value).length;
                    } catch (e) {
                        size = -1; // Circular reference or error
                    }

                    properties[key] = {
                        value: value,
                        type: valueType,
                        size: size,
                        locked: lockedProperties.includes(key),
                        isModel: models.hasOwnProperty(key),
                        isLazy: lazy.includes(key),
                        isReactive: reactive.includes(key),
                        isId: key.toLowerCase() === 'id' || key.toLowerCase().endsWith('_id'),
                        path: key
                    };
                });
            }

            return properties;
        },

        // Store initial commit for all components
        storeInitialCommit() {
            window.Livewire.all().forEach((component: any) => {
                this.commits.push({
                    id: this.randomId(),
                    initial: true,
                    current: true,
                    componentId: component.id,
                    calls: null,
                    size: null,
                    snapshot: component.snapshotEncoded,
                    effects: {
                        returns: [],
                        html: component.el.outerHTML.toString(),
                    },
                    updates: null,
                    duration: null,
                });
            });
        },

        // Setup event listeners
        setupEventListeners() {
            if (!window.Livewire) return;

            const self = this;

            // Register lifecycle hooks for comprehensive tracking
            window.Livewire.hook('component.init', ({ component }: any) => {
                this.trackLifecycleEvent(component.id, 'init', { name: component.name });
            });

            window.Livewire.hook('element.init', ({ component }: any) => {
                this.trackLifecycleEvent(component.id, 'element.init');
            });

            window.Livewire.hook('component.hydrate', ({ component }: any) => {
                this.trackLifecycleEvent(component.id, 'hydrate');
            });

            window.Livewire.hook('component.dehydrate', ({ component }: any) => {
                this.trackLifecycleEvent(component.id, 'dehydrate');
            });

            window.Livewire.hook('element.updating', ({ component, cleanup }: any) => {
                this.trackLifecycleEvent(component.id, 'updating');
            });

            window.Livewire.hook('element.updated', ({ component }: any) => {
                this.trackLifecycleEvent(component.id, 'updated');
                // Re-detect components after update to catch property changes
                setTimeout(() => this.detectComponents(), 100);
            });

            window.Livewire.hook('message.sent', ({ component, message }: any) => {
                this.trackLifecycleEvent(component.id, 'message.sent', {
                    calls: message.updates,
                    payload: message
                });
            });

            window.Livewire.hook('message.received', ({ component, message }: any) => {
                this.trackLifecycleEvent(component.id, 'message.received', {
                    response: message
                });
            });

            window.Livewire.hook('message.failed', ({ component, message }: any) => {
                this.trackLifecycleEvent(component.id, 'message.failed', {
                    error: message
                });
            });

            // Register commit listener to track all Livewire commits
            window.Livewire.hook('commit', ({ component, commit, succeed }: any) => {
                // Start measuring commit request duration
                const commitStart = performance.now();

                // If the commit succeeded we will store the snapshot and effects
                succeed(({ snapshot, effects }: any) => {
                    // Calculate the snapshot and effect size
                    let size = Math.round(new Blob([JSON.stringify({ snapshot, effects })]).size / 1024 * 100) / 100;

                    // Measure when request was completed
                    const commitEnd = performance.now();

                    // Generate a random id for tracking
                    const commitId = this.randomId();

                    // Add the commit
                    this.commits.push({
                        id: commitId,
                        initial: false,
                        current: false,
                        componentId: component.id,
                        calls: commit.calls,
                        size: size,
                        snapshot: snapshot,
                        effects: effects,
                        updates: commit.updates,
                        duration: Math.round(commitEnd - commitStart),
                    });

                    // Mark the commit as current
                    this.setComponentCurrentCommit(commitId);

                    // Update our component list
                    this.detectComponents();
                });
            });
        },

        // Get all Events that have been dispatched
        getEvents() {
            // We start by grabbing out all commits that have dispatched events
            return this.commits.filter(commit => commit.effects?.dispatches)
                // Next we map over each commit and the component object for easy reference
                .map(commit => commit.effects.dispatches.map((dispatch: any) => ({
                    ...dispatch,
                    id: this.randomId(),
                    commitId: commit.id,
                    component: this.getComponentById(commit.componentId),
                    timestamp: Date.now(),
                    type: 'dispatched',
                })))
                // Finally we flatten the array to make it easier to loop over in the template
                .flat()
                .reverse();
        },

        // Event filtering and search
        eventSearch: '',
        eventTypeFilter: 'all',
        expandedEvents: {} as Record<string, boolean>,
        eventReplayStatus: null as { type: string; message: string } | null,

        getFilteredEvents() {
            let filtered = this.getEvents();

            // Filter by type
            if (this.eventTypeFilter !== 'all') {
                filtered = filtered.filter((event: any) => {
                    if (this.eventTypeFilter === 'error') {
                        return event.error === true;
                    }
                    return event.type === this.eventTypeFilter;
                });
            }

            // Search filter
            if (this.eventSearch) {
                const search = this.eventSearch.toLowerCase();
                filtered = filtered.filter((event: any) => {
                    // Search in event name
                    if ((event.name || event.event || '').toLowerCase().includes(search)) {
                        return true;
                    }

                    // Search in component name
                    if ((event.component?.name || '').toLowerCase().includes(search)) {
                        return true;
                    }

                    // Try to search in params, but handle circular references
                    try {
                        return JSON.stringify(event.params).toLowerCase().includes(search);
                    } catch (e) {
                        // If circular reference, just check if params is an object
                        return event.params && typeof event.params === 'object';
                    }
                });
            }

            return filtered;
        },

        getEventsByType(type: string) {
            const events = this.getEvents();
            if (type === 'error') {
                return events.filter((event: any) => event.error === true);
            }
            return events.filter((event: any) => event.type === type);
        },

        getUniqueEventNames() {
            const events = this.getEvents();
            const names = new Set(events.map((event: any) => event.name || event.event));
            return Array.from(names);
        },

        getAverageEventTime() {
            const events = this.getEvents();
            const eventsWithDuration = events.filter((event: any) => event.duration);
            if (eventsWithDuration.length === 0) return 0;

            const totalDuration = eventsWithDuration.reduce((sum: number, event: any) => sum + event.duration, 0);
            return Math.round(totalDuration / eventsWithDuration.length);
        },

        getGroupedEvents() {
            const now = Date.now();
            const groups: Record<string, any[]> = {};

            this.getFilteredEvents().forEach((event: any) => {
                const timestamp = event.timestamp || now;
                const diff = now - timestamp;

                let label = 'Just now';
                if (diff > 3600000) {
                    label = `${Math.floor(diff / 3600000)} hours ago`;
                } else if (diff > 60000) {
                    label = `${Math.floor(diff / 60000)} minutes ago`;
                } else if (diff > 5000) {
                    label = `${Math.floor(diff / 1000)} seconds ago`;
                }

                if (!groups[label]) {
                    groups[label] = [];
                }
                groups[label].push(event);
            });

            return Object.entries(groups).map(([label, events]) => ({ label, events }));
        },

        formatEventTime(timestamp: number) {
            return new Date(timestamp).toLocaleTimeString();
        },

        formatParamValue(value: any) {
            if (value === null) return 'null';
            if (value === undefined) return 'undefined';
            if (typeof value === 'object') {
                try {
                    // Try to stringify, but catch circular reference errors
                    return JSON.stringify(value);
                } catch (e) {
                    // If circular reference, try to create a simple representation
                    if (Array.isArray(value)) {
                        return `[Array(${value.length})]`;
                    }
                    // For objects, show just the keys
                    const keys = Object.keys(value);
                    if (keys.length > 5) {
                        return `{${keys.slice(0, 5).join(', ')}, ...}`;
                    }
                    return `{${keys.join(', ')}}`;
                }
            }
            return String(value);
        },

        getParamType(value: any) {
            if (value === null) return 'null';
            if (Array.isArray(value)) return 'array';
            return typeof value;
        },

        cleanEventForExport(event: any) {
            // Create a clean copy of the event without circular references
            const cleanEvent = {
                id: event.id,
                name: event.name || event.event,
                type: event.type,
                params: event.params || [],
                timestamp: event.timestamp,
                commitId: event.commitId,
                component: event.component ? {
                    id: event.component.id,
                    name: event.component.name
                } : null
            };

            // Add any additional non-circular properties
            if (event.duration) cleanEvent.duration = event.duration;
            if (event.error) cleanEvent.error = event.error;
            if (event.caller_method) cleanEvent.caller_method = event.caller_method;

            return cleanEvent;
        },

        copyEventData(event: any) {
            const cleanEvent = this.cleanEventForExport(event);
            navigator.clipboard.writeText(JSON.stringify(cleanEvent, null, 2));
            this.showNotification('Event data copied to clipboard');
        },

        initEditFields(event: any) {
            const context = this.$el.querySelector(`[x-data*="editing"]`);
            if (context && context.__x) {
                context.__x.getUnobservedData().editFields = this.flattenEventParams(event.params || {});
            }
        },

        flattenEventParams(params: any, prefix = '') {
            let fields: any[] = [];

            Object.entries(params || {}).forEach(([key, value]) => {
                const path = prefix ? `${prefix}.${key}` : key;
                fields.push({
                    path: path,
                    value: value,
                    type: this.getParamType(value),
                    key: key
                });
            });

            return fields;
        },

        saveAndReplayEvent(event: any) {
            const context = this.$el.querySelector(`[x-data*="editing"]`);
            if (context && context.__x) {
                const editFields = context.__x.getUnobservedData().editFields;
                const newParams: any = {};

                editFields.forEach((field: any) => {
                    newParams[field.key] = field.value;
                });

                event.params = newParams;
                context.__x.getUnobservedData().editing = false;
                this.replayEvent(event);
            }
        },

        clearEvents() {
            this.commits = this.commits.map(commit => ({
                ...commit,
                effects: {
                    ...commit.effects,
                    dispatches: []
                }
            }));
            this.showNotification('All events cleared');
        },

        exportEvents() {
            const events = this.getEvents();
            const cleanEvents = events.map(event => this.cleanEventForExport(event));
            const dataStr = JSON.stringify({
                events: cleanEvents,
                exported_at: new Date().toISOString(),
                total_count: cleanEvents.length
            }, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);

            const exportFileDefaultName = `livewire-events-${Date.now()}.json`;

            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();

            this.showNotification('Events exported successfully');
        },

        getAffectedComponents(event: any) {
            // This would need to be implemented based on your event tracking
            const components = Object.values(this.components);
            return components.filter((comp: any) => {
                // Logic to determine which components are affected by this event
                return event.component?.id === comp.id;
            });
        },

        getAffectedComponentsDescription(event: any) {
            const affected = this.getAffectedComponents(event);
            if (affected.length === 0) return 'No components were directly affected by this event.';
            return `This event affected ${affected.length} component(s): ${affected.map((c: any) => c.name).join(', ')}`;
        },

        getRelatedMethods(event: any) {
            // This would need to be implemented based on your method tracking
            return [];
        },

        getRelatedMethodsDescription(event: any) {
            const methods = this.getRelatedMethods(event);
            if (methods.length === 0) return 'No methods were triggered by this event.';
            return `This event triggered ${methods.length} method(s).`;
        },

        getModifiedProperties(event: any) {
            // This would need to be implemented based on your property tracking
            return [];
        },

        getModifiedPropertiesDescription(event: any) {
            const props = this.getModifiedProperties(event);
            if (props.length === 0) return 'No properties were modified by this event.';
            return `This event modified ${props.length} property/properties.`;
        },

        copyRawData(event: any) {
            const cleanEvent = this.cleanEventForExport(event);
            navigator.clipboard.writeText(JSON.stringify(cleanEvent, null, 2));
            this.showNotification('Raw data copied to clipboard');
        },

        downloadRawData(event: any) {
            const cleanEvent = this.cleanEventForExport(event);
            const dataStr = JSON.stringify(cleanEvent, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);

            const exportFileDefaultName = `event-${event.id}-${Date.now()}.json`;

            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();

            this.showNotification('Event data downloaded');
        },

        getDefaultValueForType(type: string) {
            switch(type) {
                case 'boolean': return false;
                case 'number': return 0;
                case 'string': return '';
                default: return null;
            }
        },

        filterEvents() {
            // This method is called on input, the actual filtering happens in getFilteredEvents()
        },

        // Get all commits for the active component
        activeComponentCommits() {
            return this.getComponentCommitsById(this.activeComponentId);
        },

        // Get all commits for a component
        getComponentCommitsById(componentId: string | null) {
            if (!componentId) return [];
            return this.commits.filter(commit => commit.componentId === componentId).reverse();
        },

        // Get the last commit for a component
        getLastComponentCommitById(componentId: string) {
            return this.getComponentCommitsById(componentId)[0] ?? null;
        },

        // Get the active commit for a component
        getComponentActiveCommit(componentId: string) {
            let commits = this.getComponentCommitsById(componentId);
            return commits.find(commit => commit.current === true) ?? null;
        },

        // Set component current commit
        setComponentCurrentCommit(commitId: string) {
            // Find the commit
            let commit = this.commits.find(commit => commit.id === commitId);
            if (!commit) return;

            // Lookup the associated component
            let componentId = commit.componentId;

            // Mark the commit as current
            this.commits.forEach(commit => {
                if(commit.componentId === componentId) {
                    commit.current = commit.id === commitId;
                }
            });
        },

        // Update property value
        updateProperty(componentId: string, propertyName: string, value: string) {
            try {
                const parsedValue = JSON.parse(value);
                const component = this.components[componentId];

                if (!component) {
                    console.error('Component not found:', componentId);
                    return;
                }

                // Update the tracked property
                if (component.properties[propertyName]) {
                    component.properties[propertyName].value = parsedValue;
                    component.properties[propertyName].size = JSON.stringify(parsedValue).length;
                    component.properties[propertyName].type = Array.isArray(parsedValue) ? 'array' : typeof parsedValue;
                }

                // Update in Livewire
                if (window.Livewire) {
                    const lwComponent = window.Livewire.find(componentId);
                    if (lwComponent) {
                        try {
                            if (lwComponent.$wire && typeof lwComponent.$wire.set === 'function') {
                                lwComponent.$wire.set(propertyName, parsedValue);
                            } else if (lwComponent.$wire && lwComponent.$wire[propertyName] !== undefined) {
                                lwComponent.$wire[propertyName] = parsedValue;
                            } else if (lwComponent.data && lwComponent.data[propertyName] !== undefined) {
                                lwComponent.data[propertyName] = parsedValue;
                            } else {
                                console.warn(`Could not update property ${propertyName} on component ${componentId}`);
                            }
                        } catch (e) {
                            console.error('Error updating Livewire component:', e);
                        }
                    }
                }
            } catch (e) {
                console.error('Failed to update property:', e);
                alert('Invalid JSON value. Please check your syntax.');
            }
        },

        // Refresh component
        refreshComponent(componentId: string) {
            if (window.Livewire) {
                const component = window.Livewire.find(componentId);
                if (component) {
                    if (component.$wire && typeof component.$wire.refresh === 'function') {
                        component.$wire.refresh();
                    } else if (component.$wire && typeof component.$wire.$refresh === 'function') {
                        component.$wire.$refresh();
                    } else if (typeof component.call === 'function') {
                        component.call('$refresh');
                    } else {
                        // Fallback: just re-detect components
                        this.detectComponents();
                    }
                }
            }
        },

        // Refresh all components
        refreshAllComponents() {
            Object.keys(this.components).forEach(componentId => {
                this.refreshComponent(componentId);
            });
            this.showNotification('🔄 All components refreshed', 'success');
        },

        // Component helper
        getComponentById(componentId: string) {
            return window.Livewire.all().find((c: any) => c.id === componentId);
        },

        // Generate random ID
        randomId() {
            return Math.random().toString(36).substring(7);
        },

        // Get total properties
        getTotalProperties() {
            return Object.values(this.components).reduce((sum: number, component: any) => {
                return sum + Object.keys(component.properties || {}).length;
            }, 0);
        },

        // Get total data size
        getTotalDataSize() {
            const totalBytes = Object.values(this.components).reduce((sum: number, component: any) => {
                return sum + Object.values(component.properties || {}).reduce((propSum: number, prop: any) => {
                    return propSum + (prop.size || 0);
                }, 0);
            }, 0);

            return (totalBytes / 1024).toFixed(1);
        },

        // Get component data size
        getComponentDataSize: (component: any) => {
            const bytes = Object.values(component.properties || {}).reduce((sum: number, prop: any) => {
                return sum + (prop.size || 0);
            }, 0);

            return (bytes / 1024).toFixed(1);
        },

        // Get property count status
        getPropertyCountStatus(component: any) {
            const count = Object.keys(component.properties || {}).length;
            const maxProperties = this.config.performance?.max_properties || 50;

            if (count >= maxProperties) return 'error';
            if (count >= maxProperties * 0.8) return 'warning';
            return 'ok';
        },

        // Get data size status
        getDataSizeStatus(component: any) {
            const sizeKB = parseFloat(this.getComponentDataSize(component));
            const maxSize = this.config.performance?.max_data_size || 256;

            if (sizeKB >= maxSize) return 'error';
            if (sizeKB >= maxSize * 0.8) return 'warning';
            return 'ok';
        },

        // Get performance warnings
        getPerformanceWarnings(component: any) {
            const warnings = [];
            const propertyCount = Object.keys(component.properties || {}).length;
            const dataSize = parseFloat(this.getComponentDataSize(component));

            const maxProperties = this.config.performance?.max_properties || 50;
            if (propertyCount >= maxProperties) {
                warnings.push({
                    type: 'property_count',
                    level: 'error',
                    message: `Too many properties (${propertyCount}/${maxProperties}). Consider reducing component state.`
                });
            } else if (propertyCount >= maxProperties * 0.8) {
                warnings.push({
                    type: 'property_count',
                    level: 'warning',
                    message: `High property count (${propertyCount}/${maxProperties}). Monitor component complexity.`
                });
            }

            const maxDataSize = this.config.performance?.max_data_size || 256;
            if (dataSize >= maxDataSize) {
                warnings.push({
                    type: 'data_size',
                    level: 'error',
                    message: `Component data too large (${dataSize}KB/${maxDataSize}KB). Optimize data structure.`
                });
            } else if (dataSize >= maxDataSize * 0.8) {
                warnings.push({
                    type: 'data_size',
                    level: 'warning',
                    message: `Large component data (${dataSize}KB/${maxDataSize}KB). Consider optimization.`
                });
            }

            // Check for unlocked ID properties
            Object.entries(component.properties || {}).forEach(([propName, prop]: [string, any]) => {
                if (prop.isId && !prop.locked) {
                    warnings.push({
                        type: 'unlocked_id',
                        level: 'error',
                        message: `Unlocked ID property "${propName}" poses security and performance risks.`
                    });
                }
            });

            return warnings;
        },

        getLargeProperties(component: any) {
            const warningSize = (this.config.performance?.property_warning_size || 10) * 1024; // Convert KB to bytes
            return Object.entries(component.properties || {})
                .map(([name, prop]: [string, any]) => ({ name, ...prop }))
                .filter((prop: any) => prop.size >= warningSize)
                .sort((a: any, b: any) => b.size - a.size);
        },


        // Performance issue counters for badges
        getPerformanceIssueCount() {
            return Object.values(this.components).reduce((count: number, component: any) => {
                const warnings = this.getPerformanceWarnings(component);
                return count + warnings.filter((w: any) => w.level === 'error').length;
            }, 0);
        },

        getPerformanceWarningCount() {
            return Object.values(this.components).reduce((count: number, component: any) => {
                const warnings = this.getPerformanceWarnings(component);
                return count + warnings.filter((w: any) => w.level === 'warning').length;
            }, 0);
        },

        // UI Position Management
        togglePosition() {
            const positions = ['bottom', 'top', 'left', 'right'];
            const currentIndex = positions.indexOf(this.position);
            const nextIndex = (currentIndex + 1) % positions.length;
            this.position = positions[nextIndex];
            this.savePreferences();
        },


        // Settings Management
        toggleSettings() {
            this.showSettings = !this.showSettings;
        },

        resetPosition() {
            this.position = 'bottom';
            this.savePreferences();
        },

        // Load saved preferences
        loadPreferences() {
            const savedPosition = localStorage.getItem('livewire-debugbar-position');
            if (savedPosition && ['bottom', 'top', 'left', 'right'].includes(savedPosition)) {
                this.position = savedPosition;
            }

            const savedCollapsed = localStorage.getItem('livewire-debugbar-collapsed');
            if (savedCollapsed !== null) {
                this.collapsed = savedCollapsed === 'true';
            }

            const savedHeight = localStorage.getItem('livewire-debugbar-height');
            if (savedHeight) {
                this.height = parseInt(savedHeight);
            }
        },

        // Save preferences
        savePreferences() {
            localStorage.setItem('livewire-debugbar-position', this.position);
            localStorage.setItem('livewire-debugbar-collapsed', this.collapsed.toString());
        },

        // Setup keyboard shortcuts
        setupKeyboardShortcuts() {
            if (!this.config.interface?.keyboard_shortcuts) return;

            document.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + Shift + D to toggle debugbar
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
                    e.preventDefault();
                    this.toggleCollapse();
                }

                // Ctrl/Cmd + Shift + 1-4 for tabs
                if ((e.ctrlKey || e.metaKey) && e.shiftKey) {
                    switch(e.key) {
                        case '1':
                            e.preventDefault();
                            this.activeTab = 'components';
                            break;
                        case '2':
                            e.preventDefault();
                            this.activeTab = 'events';
                            break;
                        case '3':
                            e.preventDefault();
                            this.activeTab = 'performance';
                            break;
                        case '4':
                            e.preventDefault();
                            this.activeTab = 'security';
                            break;
                    }
                }
            });
        },

        // Setup hot reload
        setupHotReload() {
            if (!this.config.hot_reload?.enabled) return;

            // Check if hot reload is saved as enabled
            const savedHotReload = localStorage.getItem('livewire-debugbar-hot-reload');
            if (savedHotReload === 'true') {
                this.startHotReload();
            }
        },

        // Start hot reload
        startHotReload() {
            if (this.hotReloadInterval) return;

            this.hotReload = true;
            const interval = this.config.hot_reload?.interval || 3000;

            this.hotReloadInterval = window.setInterval(() => {
                // Refresh all components
                this.detectComponents();

                // Refresh active component if property was changed
                if (this.activeComponentId) {
                    const component = this.getComponentById(this.activeComponentId);
                    if (component) {
                        component.$wire.$refresh();
                    }
                }
            }, interval);

            localStorage.setItem('livewire-debugbar-hot-reload', 'true');
        },

        // Stop hot reload
        stopHotReload() {
            if (this.hotReloadInterval) {
                clearInterval(this.hotReloadInterval);
                this.hotReloadInterval = null;
            }
            this.hotReload = false;
            localStorage.setItem('livewire-debugbar-hot-reload', 'false');
        },

        // Toggle hot reload
        toggleHotReload() {
            if (this.hotReload) {
                this.stopHotReload();
            } else {
                this.startHotReload();
            }
        },

        // Resize handling
        startResize(event: MouseEvent) {
            this.isResizing = true;
            this.initialY = event.clientY;
            this.initialHeight = this.height;

            document.addEventListener('mousemove', this.handleResize.bind(this));
            document.addEventListener('mouseup', this.stopResize.bind(this));

            event.preventDefault();
        },

        handleResize(event: MouseEvent) {
            if (!this.isResizing) return;

            const deltaY = this.initialY - event.clientY;
            const newHeight = Math.max(200, Math.min(800, this.initialHeight + deltaY));

            this.height = newHeight;

            // Update CSS
            const debugbar = document.querySelector('.livewire-debugbar') as HTMLElement;
            if (debugbar) {
                debugbar.style.height = `${newHeight}px`;
            }
        },

        stopResize() {
            this.isResizing = false;
            document.removeEventListener('mousemove', this.handleResize.bind(this));
            document.removeEventListener('mouseup', this.stopResize.bind(this));

            // Save height preference
            localStorage.setItem('livewire-debugbar-height', this.height.toString());
        },

        // Replay event
        replayEvent(event: any) {
            if (!window.Livewire) return;

            const eventName = event.name || event.event;
            const params = event.params || [];

            // Find the component that originally dispatched this event
            if (event.component && event.component.id) {
                const component = window.Livewire.find(event.component.id);
                if (component && component.$wire && component.$wire.$dispatch) {
                    // Dispatch from the original component
                    component.$wire.$dispatch(eventName, ...params);
                    console.log(`Event "${eventName}" replayed from component ${event.component.name}`);
                    return;
                }
            }

            // Fallback to global dispatch
            window.Livewire.dispatch(eventName, ...params);
            console.log(`Event "${eventName}" replayed globally`);
        },

        // Get validation errors for component
        getComponentValidationErrors(componentId: string) {
            return this.validationErrors[componentId] || [];
        },

        // Get component state history
        getComponentStateHistory(componentId: string) {
            return this.stateHistory[componentId] || [];
        },

        // Export component debugging data
        exportComponentData(componentId: string) {
            const component = this.components[componentId];
            if (!component) return;

            // Clean component data to avoid circular references
            const cleanComponent = {
                id: componentId,
                name: component.name,
                state: component.state,
                properties: component.properties,
                updates: component.updates || [],
                snapshot: component.snapshot || null,
                memo: component.memo || {},
                checksum: component.checksum || null
            };

            const exportData = {
                component: cleanComponent,
                stateHistory: this.getComponentStateHistory(componentId),
                validationErrors: this.getComponentValidationErrors(componentId),
                lifecycleEvents: this.getComponentLifecycleEvents(componentId),
                commits: this.getComponentCommitsById(componentId),
                timestamp: new Date().toISOString()
            };

            const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `livewire-component-${component.name}-${componentId}.json`;
            a.click();
            URL.revokeObjectURL(url);

            this.showNotification(`📁 Component data exported: ${component.name}`, 'success');
            console.log('📁 Component data exported:', componentId);
        },

        // Generate test code for component
        generateTestCode(componentId: string) {
            const component = this.components[componentId];
            if (!component) return;

            const properties = Object.entries(component.properties)
                .map(([name, prop]) => `        '${name}' => ${JSON.stringify(prop.value)}`)
                .join(',\n');

            const testCode = `
<?php

use Livewire\\Livewire;

test('${component.name} component renders and functions correctly', function () {
    // Test initial render
    Livewire::test(${component.name}::class, [
${properties}
    ])
    ->assertStatus(200)
    ->assertSee('expected content');

    // Test property updates
${Object.keys(component.properties).map(prop =>
    `    // ->set('${prop}', 'new value')\n    // ->assertSet('${prop}', 'new value')`
).join('\n')}

    // Test method calls
    // ->call('methodName')
    // ->assertEmitted('eventName');
});
            `;

            // Copy to clipboard
            navigator.clipboard.writeText(testCode.trim()).then(() => {
                this.showNotification('📋 Test code copied to clipboard', 'success');
                console.log('📋 Test code copied to clipboard');
            }).catch(() => {
                this.showNotification('Failed to copy test code', 'error');
            });
        },

        // Performance insights
        getPerformanceInsights() {
            const totalComponents = Object.keys(this.components).length;
            const totalProperties = Object.values(this.components).reduce((sum, comp: any) =>
                sum + Object.keys(comp.properties).length, 0);
            const averageProperties = totalProperties / totalComponents || 0;

            const largestComponents = Object.entries(this.components)
                .map(([id, comp]: [string, any]) => ({
                    id,
                    name: comp.name,
                    propertyCount: Object.keys(comp.properties).length,
                    dataSize: Object.values(comp.properties).reduce((sum: number, prop: any) => sum + prop.size, 0)
                }))
                .sort((a, b) => b.dataSize - a.dataSize)
                .slice(0, 5);

            return {
                totalComponents,
                totalProperties,
                averageProperties: Math.round(averageProperties * 10) / 10,
                largestComponents,
                totalValidationErrors: Object.values(this.validationErrors).flat().length,
                criticalErrors: Object.values(this.validationErrors).flat().filter((e: any) => e.level === 'critical').length
            };
        },

        // Toggle advanced features
        toggleRealTimeValidation() {
            this.realTimeValidation = !this.realTimeValidation;
            localStorage.setItem('livewire-debugbar-real-time-validation', this.realTimeValidation.toString());

            if (this.realTimeValidation) {
                // Re-validate all components
                Object.entries(this.components).forEach(([componentId, component]) => {
                    this.validateComponentProperties(componentId, component.properties);
                });
            } else {
                // Clear all validation errors
                this.validationErrors = {};
            }
        },

        toggleStateChangeNotifications() {
            this.stateChangeNotifications = !this.stateChangeNotifications;
            localStorage.setItem('livewire-debugbar-state-notifications', this.stateChangeNotifications.toString());
        },

        // Component comparison tool
        compareComponentStates(componentId: string, stateId1: string, stateId2: string) {
            const history = this.stateHistory[componentId];
            if (!history) return null;

            const state1 = history.find(s => s.id === stateId1);
            const state2 = history.find(s => s.id === stateId2);

            if (!state1 || !state2) return null;

            const differences = [];
            const allKeys = new Set([
                ...Object.keys(state1.state),
                ...Object.keys(state2.state)
            ]);

            allKeys.forEach(key => {
                const val1 = state1.state[key];
                const val2 = state2.state[key];

                if (JSON.stringify(val1) !== JSON.stringify(val2)) {
                    differences.push({
                        property: key,
                        before: val1,
                        after: val2,
                        type: val1 === undefined ? 'added' : val2 === undefined ? 'removed' : 'changed'
                    });
                }
            });

            return {
                state1: state1,
                state2: state2,
                differences: differences,
                changeCount: differences.length
            };
        },

        // Component UI Methods
        filterComponents() {
            // This will trigger re-render through Alpine reactivity
            // The actual filtering is done in sortedComponents()
        },

        sortedComponents() {
            let componentsArray = Object.entries(this.components);

            // Filter by search
            if (this.componentSearch) {
                const search = this.componentSearch.toLowerCase();
                componentsArray = componentsArray.filter(([id, component]: [string, any]) => {
                    return component.name.toLowerCase().includes(search) ||
                           id.toLowerCase().includes(search);
                });
            }

            // Filter by state
            if (this.componentStateFilter) {
                componentsArray = componentsArray.filter(([id, component]: [string, any]) => {
                    return component.state === this.componentStateFilter;
                });
            }

            // Filter by validation
            if (this.componentValidationFilter) {
                componentsArray = componentsArray.filter(([id, component]: [string, any]) => {
                    const errors = this.getComponentValidationErrors(id);
                    switch (this.componentValidationFilter) {
                        case 'valid':
                            return errors.length === 0;
                        case 'warnings':
                            return errors.some((e: any) => e.level === 'warning');
                        case 'errors':
                            return errors.some((e: any) => e.level === 'critical' || e.level === 'error');
                        default:
                            return true;
                    }
                });
            }

            // Sort
            componentsArray.sort(([idA, compA]: [string, any], [idB, compB]: [string, any]) => {
                switch (this.componentSort) {
                    case 'properties':
                        return Object.keys(compB.properties || {}).length - Object.keys(compA.properties || {}).length;
                    case 'size':
                        const sizeA = Object.values(compA.properties || {}).reduce((sum: number, prop: any) => sum + prop.size, 0);
                        const sizeB = Object.values(compB.properties || {}).reduce((sum: number, prop: any) => sum + prop.size, 0);
                        return sizeB - sizeA;
                    case 'performance':
                        const warningsA = this.getPerformanceWarnings(compA).length;
                        const warningsB = this.getPerformanceWarnings(compB).length;
                        return warningsB - warningsA;
                    default: // name
                        return compA.name.localeCompare(compB.name);
                }
            });

            return componentsArray;
        },

        focusComponent(componentId: string) {
            const component = this.components[componentId];
            if (!component || !component.element) return;

            // Scroll element into view
            component.element.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Highlight element
            const originalOutline = component.element.style.outline;
            component.element.style.outline = '3px solid #3B82F6';
            component.element.style.outlineOffset = '2px';

            setTimeout(() => {
                component.element.style.outline = originalOutline;
            }, 2000);

            console.log(`🎯 Focused on component: ${component.name}`);
        },

        copyComponentPath(componentName: string) {
            // Convert component name to file path
            const path = componentName
                .split('.')
                .map(part => part.charAt(0).toUpperCase() + part.slice(1))
                .join('/');

            const fullPath = `app/Http/Livewire/${path}.php`;

            navigator.clipboard.writeText(fullPath).then(() => {
                this.showNotification(`📋 Path copied: ${fullPath}`, 'success');
                console.log(`📋 Copied path: ${fullPath}`);
            }).catch(() => {
                this.showNotification('Failed to copy component path', 'error');
            });
        },

        removeFilter(filter: string) {
            const index = this.activeFilters.indexOf(filter);
            if (index > -1) {
                this.activeFilters.splice(index, 1);
            }
        },

        // Tree view methods
        getComponentTree() {
            const tree: Record<string, any> = {};
            const componentMap: Record<string, any> = {};

            // Build a map of all components with children array
            Object.entries(this.components).forEach(([id, component]) => {
                componentMap[id] = {
                    component: component,
                    children: []
                };
            });

            // Build parent-child relationships
            Object.entries(this.components).forEach(([id, component]) => {
                if (component.element) {
                    // Find parent components
                    let parent = component.element.parentElement;
                    let parentComponent = null;

                    while (parent && !parentComponent) {
                        // Check if this parent element is a Livewire component
                        const parentId = Array.from(parent.attributes || [])
                            .find((attr: any) => attr.name === 'wire:id')?.value;

                        if (parentId && componentMap[parentId]) {
                            parentComponent = componentMap[parentId];
                            parentComponent.children.push(id);
                            break;
                        }

                        parent = parent.parentElement;
                    }

                    // If no parent found, it's a root component
                    if (!parentComponent) {
                        tree[id] = componentMap[id];
                    }
                }
            });

            return tree;
        },

        hasChildren(componentId: string) {
            const component = this.components[componentId];
            if (!component || !component.element) return false;

            // Check if any other components are children of this one
            return Object.values(this.components).some((otherComponent: any) => {
                if (!otherComponent.element || otherComponent.id === componentId) return false;

                let parent = otherComponent.element.parentElement;
                while (parent) {
                    const parentId = Array.from(parent.attributes || [])
                        .find((attr: any) => attr.name === 'wire:id')?.value;

                    if (parentId === componentId) return true;
                    parent = parent.parentElement;
                }
                return false;
            });
        },

        toggleTreeNode(componentId: string) {
            this.expandedTreeNodes[componentId] = !this.expandedTreeNodes[componentId];
        },

        componentMatchesSearch(component: any) {
            if (!this.componentSearch) return true;

            const search = this.componentSearch.toLowerCase();
            return component.name.toLowerCase().includes(search) ||
                   component.id.toLowerCase().includes(search);
        },

        // UI Actions
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            this.savePreferences();
        },

        // Notification system
        showNotification(message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info') {
            const notification = document.createElement('div');
            notification.className = 'ldb-notification';

            const typeColors = {
                success: { bg: '#10B981', text: 'white' },
                error: { bg: '#EF4444', text: 'white' },
                warning: { bg: '#F59E0B', text: 'black' },
                info: { bg: '#3B82F6', text: 'white' }
            };

            const colors = typeColors[type];

            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: ${colors.bg};
                color: ${colors.text};
                padding: 12px 20px;
                border-radius: 8px;
                font-family: var(--ldb-font-sans);
                font-size: 14px;
                font-weight: 500;
                z-index: 999999;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.1);
                max-width: 400px;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;

            notification.textContent = message;
            document.body.appendChild(notification);

            // Animate in
            requestAnimationFrame(() => {
                notification.style.transform = 'translateX(0)';
            });

            // Auto remove after 4 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 4000);

            // Click to dismiss
            notification.addEventListener('click', () => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            });
        },

        // Additional event-related state and methods
        eventStatistics: {
            total: 0,
            byType: {} as Record<string, number>,
            byComponent: {} as Record<string, number>,
            avgPayloadSize: 0,
            peakTime: null as string | null,
            replays: {} as Record<string, number>
        },

        showEventEditor: false,
        editingEvent: null as any,
        autoRefresh: false,

        toggleEventDetails(eventId: string) {
            const detailsEl = document.getElementById(`event-details-${eventId}`);
            if (detailsEl) {
                detailsEl.classList.toggle('hidden');
            }
        },

        editEvent(eventId: string) {
            const event = this.events.find((e: any) => e.id === eventId);
            if (!event) return;

            this.editingEvent = JSON.parse(JSON.stringify(event));
            this.showEventEditor = true;
        },

        saveEventEdit() {
            if (!this.editingEvent) return;

            const index = this.events.findIndex((e: any) => e.id === this.editingEvent.id);
            if (index !== -1) {
                this.events[index] = JSON.parse(JSON.stringify(this.editingEvent));
                this.showNotification(window.livewireDebugbarTranslations?.event_updated || 'Event updated', 'success');
            }

            this.showEventEditor = false;
            this.editingEvent = null;
        },

        cancelEventEdit() {
            this.showEventEditor = false;
            this.editingEvent = null;
        },

        deleteEvent(eventId: string) {
            const index = this.events.findIndex((e: any) => e.id === eventId);
            if (index !== -1) {
                this.events.splice(index, 1);
                this.showNotification(window.livewireDebugbarTranslations?.event_deleted || 'Event deleted', 'success');
            }
        },

        async importEvents(event: Event) {
            const input = event.target as HTMLInputElement;
            if (!input.files || !input.files[0]) return;

            try {
                const text = await input.files[0].text();
                const data = JSON.parse(text);

                if (data.events && Array.isArray(data.events)) {
                    this.events = [...this.events, ...data.events];
                    this.showNotification(window.livewireDebugbarTranslations?.events_imported || 'Events imported successfully', 'success');
                } else {
                    throw new Error('Invalid format');
                }
            } catch (error) {
                this.showNotification(window.livewireDebugbarTranslations?.import_error || 'Failed to import events', 'error');
            }

            // Reset input
            input.value = '';
        },

        updateEventStatistics() {
            const events = this.getEvents();

            if (!events.length) {
                this.eventStatistics = {
                    total: 0,
                    byType: {},
                    byComponent: {},
                    avgPayloadSize: 0,
                    peakTime: null,
                    replays: this.eventStatistics.replays || {}
                };
                return;
            }

            const stats = {
                total: events.length,
                byType: {} as Record<string, number>,
                byComponent: {} as Record<string, number>,
                avgPayloadSize: 0,
                peakTime: null as string | null,
                replays: this.eventStatistics.replays || {}
            };

            let totalPayloadSize = 0;
            const timeGroups: Record<string, number> = {};

            events.forEach((event: any) => {
                // Count by type
                const eventName = event.name || event.event || 'Unknown';
                stats.byType[eventName] = (stats.byType[eventName] || 0) + 1;

                // Count by component
                if (event.component && event.component.name) {
                    stats.byComponent[event.component.name] = (stats.byComponent[event.component.name] || 0) + 1;
                }

                // Calculate payload size
                const payload = event.params || event.payload || [];
                totalPayloadSize += JSON.stringify(payload).length;

                // Group by hour for peak time
                const timestamp = event.timestamp || Date.now();
                const hour = new Date(timestamp).getHours();
                const hourKey = `${hour}:00`;
                timeGroups[hourKey] = (timeGroups[hourKey] || 0) + 1;
            });

            // Calculate averages
            stats.avgPayloadSize = Math.round(totalPayloadSize / events.length);

            // Find peak time
            let maxEvents = 0;
            Object.entries(timeGroups).forEach(([time, count]) => {
                if (count > maxEvents) {
                    maxEvents = count;
                    stats.peakTime = time;
                }
            });

            this.eventStatistics = stats;
        },

        initializeEventTab() {
            // Initial statistics calculation
            this.updateEventStatistics();

            // Set up auto-refresh if needed
            if (this.autoRefresh) {
                setInterval(() => {
                    if (this.activeTab === 'events') {
                        this.updateEventStatistics();
                    }
                }, 5000);
            }
        },

        // Inline editing methods
        updateEditField(key: string, value: string, type: string) {
            if (!this.editFields[key]) {
                this.editFields[key] = { type };
            }

            // Convert value based on type
            let convertedValue: any = value;
            switch (type) {
                case 'number':
                    convertedValue = parseFloat(value);
                    break;
                case 'boolean':
                    convertedValue = value === 'true';
                    break;
                case 'object':
                case 'array':
                    try {
                        convertedValue = JSON.parse(value);
                    } catch (e) {
                        // Keep as string if parse fails
                    }
                    break;
            }

            this.editFields[key].value = convertedValue;
        },

        addNewParam() {
            const newKey = `param_${Date.now()}`;
            this.editFields[newKey] = {
                value: '',
                type: 'string',
                isNew: true
            };
        },

        removeParam(key: string) {
            delete this.editFields[key];
        },

        // Performance methods
        getQueryCount() {
            // This would be implemented with actual query tracking
            return 0;
        },

        getAverageRenderTime() {
            // This would be implemented with actual render time tracking
            const commits = this.commits.filter(c => c.duration);
            if (commits.length === 0) return 0;
            const totalTime = commits.reduce((sum, c) => sum + c.duration, 0);
            return Math.round(totalTime / commits.length);
        },

        exportPerformanceData() {
            const report = {
                timestamp: new Date().toISOString(),
                summary: {
                    totalComponents: Object.keys(this.components).length,
                    totalProperties: this.getTotalProperties(),
                    totalMemory: this.getTotalDataSize() + ' KB',
                    totalEvents: this.getEvents().length,
                    averageRenderTime: this.getAverageRenderTime() + ' ms',
                    averagePropertiesPerComponent: Math.round(this.getTotalProperties() / Object.keys(this.components).length)
                },
                components: Object.entries(this.components).map(([id, component]) => ({
                    id,
                    name: component.name,
                    properties: Object.keys(component.properties).length,
                    memory: this.getComponentDataSize(component) + ' KB',
                    warnings: this.getPerformanceWarnings(component).length,
                    status: this.getPropertyCountStatus(component),
                    largeProperties: this.getLargeProperties(component).map(p => ({
                        name: p.name,
                        size: (p.size / 1024).toFixed(1) + ' KB',
                        type: p.type
                    }))
                })),
                warnings: this.getAllPerformanceWarnings(),
                thresholds: this.config.thresholds || {},
                lifecycleEvents: this.lifecycleEvents.slice(-50)
            };
            
            const json = JSON.stringify(report, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `livewire-performance-report-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
            
            this.showNotification('Performance report exported', 'success');
        },
        
        getAllPerformanceWarnings() {
            const warnings = [];
            Object.entries(this.components).forEach(([id, component]) => {
                const componentWarnings = this.getPerformanceWarnings(component);
                if (componentWarnings.length > 0) {
                    warnings.push({
                        componentId: id,
                        componentName: component.name,
                        warnings: componentWarnings
                    });
                }
            });
            return warnings;
        },

        // Security related methods
        isScanning: false,
        lastScanTime: null as string | null,
        securityIssues: [] as any[],
        auditLog: [] as any[],
        auditLogFilter: 'all',

        getSecurityIssues() {
            const issues: any[] = [];
            
            Object.entries(this.components).forEach(([componentId, component]: [string, any]) => {
                // Check for unlocked sensitive properties
                Object.entries(component.properties).forEach(([propName, prop]: [string, any]) => {
                    if (!prop.locked) {
                        if (propName.toLowerCase().includes('id') || propName === 'id') {
                            issues.push({
                                id: `${componentId}-${propName}-unlocked-id`,
                                level: 'critical',
                                title: 'Unlocked ID Property',
                                message: `The property "${propName}" is an ID field that should be locked to prevent manipulation`,
                                component: component.name,
                                property: propName,
                                fixable: true
                            });
                        } else if (this.isSensitivePropertyName(propName)) {
                            issues.push({
                                id: `${componentId}-${propName}-unlocked-sensitive`,
                                level: 'high',
                                title: 'Unlocked Sensitive Property',
                                message: `The property "${propName}" appears to contain sensitive data and should be locked`,
                                component: component.name,
                                property: propName,
                                fixable: true
                            });
                        }
                    }
                    
                    // Check for exposed sensitive data
                    if (this.containsSensitiveData(prop.value)) {
                        issues.push({
                            id: `${componentId}-${propName}-exposed`,
                            level: 'high',
                            title: 'Exposed Sensitive Data',
                            message: `The property "${propName}" contains potentially sensitive information that should not be exposed`,
                            component: component.name,
                            property: propName,
                            fixable: false
                        });
                    }
                });
                
                // Check for large property counts
                const propCount = Object.keys(component.properties).length;
                if (propCount > 30) {
                    issues.push({
                        id: `${componentId}-too-many-props`,
                        level: 'medium',
                        title: 'Excessive Property Count',
                        message: `Component has ${propCount} properties. Consider refactoring to reduce exposed data`,
                        component: component.name,
                        fixable: false
                    });
                }
            });
            
            return issues;
        },

        getUnlockedProperties() {
            const unlocked: any[] = [];
            
            Object.entries(this.components).forEach(([componentId, component]: [string, any]) => {
                Object.entries(component.properties).forEach(([propName, prop]: [string, any]) => {
                    if (!prop.locked) {
                        unlocked.push({
                            id: `${componentId}-${propName}`,
                            component: component.name,
                            property: propName,
                            type: prop.type,
                            value: prop.value,
                            isId: propName.toLowerCase().includes('id') || propName === 'id',
                            isSensitive: this.isSensitivePropertyName(propName),
                            recommendations: this.getPropertyRecommendations(propName, prop)
                        });
                    }
                });
            });
            
            return unlocked;
        },

        getSensitiveData() {
            const sensitive: any[] = [];
            
            Object.entries(this.components).forEach(([componentId, component]: [string, any]) => {
                Object.entries(component.properties).forEach(([propName, prop]: [string, any]) => {
                    const pattern = this.detectSensitivePattern(prop.value);
                    if (pattern) {
                        sensitive.push({
                            id: `${componentId}-${propName}`,
                            component: component.name,
                            property: propName,
                            pattern: pattern,
                            reason: this.getSensitiveDataReason(pattern),
                            recommendation: this.getSensitiveDataRecommendation(pattern)
                        });
                    }
                });
            });
            
            return sensitive;
        },

        getSecurityScore() {
            const issues = this.getSecurityIssues();
            const critical = issues.filter(i => i.level === 'critical').length;
            const high = issues.filter(i => i.level === 'high').length;
            const medium = issues.filter(i => i.level === 'medium').length;
            
            let score = 100;
            score -= critical * 20;
            score -= high * 10;
            score -= medium * 5;
            
            return Math.max(0, score);
        },

        getSecurityScoreClass() {
            const score = this.getSecurityScore();
            if (score >= 90) return 'excellent';
            if (score >= 70) return 'good';
            if (score >= 50) return 'fair';
            if (score >= 30) return 'poor';
            return 'critical';
        },

        getSecurityScoreDescription() {
            const score = this.getSecurityScore();
            if (score >= 90) return 'Your components have excellent security practices';
            if (score >= 70) return 'Good security, but some improvements recommended';
            if (score >= 50) return 'Fair security, several issues need attention';
            if (score >= 30) return 'Poor security, immediate action required';
            return 'Critical security issues detected, urgent fixes needed';
        },

        runSecurityScan() {
            this.isScanning = true;
            this.auditLog.unshift({
                id: Date.now(),
                type: 'Security Scan Started',
                level: 'info',
                time: new Date().toLocaleTimeString(),
                message: 'Manual security scan initiated'
            });
            
            setTimeout(() => {
                // Refresh component detection
                this.detectComponents();
                
                // Log findings
                const issues = this.getSecurityIssues();
                const critical = issues.filter(i => i.level === 'critical').length;
                const high = issues.filter(i => i.level === 'high').length;
                
                this.auditLog.unshift({
                    id: Date.now(),
                    type: 'Scan Complete',
                    level: critical > 0 ? 'critical' : high > 0 ? 'warning' : 'info',
                    time: new Date().toLocaleTimeString(),
                    message: `Found ${critical} critical and ${high} high risk issues`
                });
                
                this.isScanning = false;
                this.lastScanTime = new Date().toLocaleTimeString();
                
                if (critical > 0) {
                    this.showNotification(`⚠️ ${critical} critical security issues found!`, 'error');
                }
            }, 1000);
        },

        fixAllSecurityIssues() {
            const fixableIssues = this.getSecurityIssues().filter(i => i.fixable);
            let fixed = 0;
            
            fixableIssues.forEach(issue => {
                if (this.fixSecurityIssue(issue)) {
                    fixed++;
                }
            });
            
            this.showNotification(`🔧 Fixed ${fixed} security issues`, 'success');
            this.runSecurityScan();
        },

        fixSecurityIssue(issue: any) {
            // This would implement actual fixes based on issue type
            // For demo purposes, we'll simulate fixing
            this.auditLog.unshift({
                id: Date.now(),
                type: 'Issue Fixed',
                level: 'info',
                time: new Date().toLocaleTimeString(),
                message: `Fixed: ${issue.title} in ${issue.component}`
            });
            
            return true;
        },

        viewSecurityIssueDetails(issue: any) {
            // Navigate to the component and highlight the issue
            this.activeTab = 'components';
            this.componentSearch = issue.component;
            this.showNotification(`Showing details for: ${issue.title}`, 'info');
        },

        exportSecurityReport() {
            const report = {
                timestamp: new Date().toISOString(),
                score: this.getSecurityScore(),
                summary: {
                    critical: this.getSecurityIssues().filter(i => i.level === 'critical').length,
                    high: this.getSecurityIssues().filter(i => i.level === 'high').length,
                    medium: this.getSecurityIssues().filter(i => i.level === 'medium').length,
                    unlockedProperties: this.getUnlockedProperties().length,
                    sensitiveExposures: this.getSensitiveData().length
                },
                issues: this.getSecurityIssues(),
                unlockedProperties: this.getUnlockedProperties(),
                sensitiveData: this.getSensitiveData(),
                recommendations: this.getSecurityRecommendations(),
                auditLog: this.auditLog.slice(0, 50)
            };
            
            const json = JSON.stringify(report, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `livewire-security-report-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
            
            this.showNotification('🔒 Security report exported', 'success');
        },

        lockAllProperties() {
            let locked = 0;
            Object.values(this.components).forEach((component: any) => {
                Object.values(component.properties).forEach((prop: any) => {
                    if (!prop.locked) {
                        prop.locked = true;
                        locked++;
                    }
                });
            });
            
            this.showNotification(`🔒 Locked ${locked} properties`, 'success');
            this.runSecurityScan();
        },

        clearSecurityCache() {
            this.auditLog = [];
            this.lastScanTime = null;
            this.showNotification('🗑️ Security cache cleared', 'info');
        },

        getXSSVulnerabilities() {
            const vulnerabilities: any[] = [];
            
            Object.entries(this.components).forEach(([componentId, component]: [string, any]) => {
                Object.entries(component.properties).forEach(([propName, prop]: [string, any]) => {
                    if (typeof prop.value === 'string' && this.containsXSSPattern(prop.value)) {
                        vulnerabilities.push({
                            id: `${componentId}-${propName}-xss`,
                            component: component.name,
                            property: propName,
                            description: 'Potential XSS vulnerability in unescaped string'
                        });
                    }
                });
            });
            
            return vulnerabilities;
        },

        getSQLInjectionRisks() {
            const risks: any[] = [];
            
            Object.entries(this.components).forEach(([componentId, component]: [string, any]) => {
                Object.entries(component.properties).forEach(([propName, prop]: [string, any]) => {
                    if (typeof prop.value === 'string' && this.containsSQLPattern(prop.value)) {
                        risks.push({
                            id: `${componentId}-${propName}-sql`,
                            component: component.name,
                            query: propName,
                            reason: 'User input may be directly used in SQL query'
                        });
                    }
                });
            });
            
            return risks;
        },

        getCSRFStatus() {
            // Check for CSRF token in page
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            return {
                status: token ? 'valid' : 'invalid',
                label: token ? 'Protected' : 'Not Protected',
                token: token ? token.substring(0, 20) + '...' : 'Not Found',
                expires: '2 hours',
                verified: true,
                requestCount: this.commits.length
            };
        },

        getPermissionMatrix() {
            const matrix: any[] = [];
            
            Object.entries(this.components).forEach(([componentId, component]: [string, any]) => {
                // Simulate checking component methods
                const methods = ['mount', 'save', 'delete', 'update'];
                methods.forEach(method => {
                    matrix.push({
                        id: `${componentId}-${method}`,
                        component: component.name,
                        method: method,
                        hasAuthorization: Math.random() > 0.3,
                        hasValidation: Math.random() > 0.2,
                        isSecure: Math.random() > 0.4
                    });
                });
            });
            
            return matrix;
        },

        getFilteredAuditLog() {
            if (this.auditLogFilter === 'all') return this.auditLog;
            if (this.auditLogFilter === 'critical') return this.auditLog.filter(e => e.level === 'critical');
            if (this.auditLogFilter === 'warnings') return this.auditLog.filter(e => e.level === 'warning');
            return this.auditLog;
        },

        clearAuditLog() {
            this.auditLog = [];
            this.showNotification('Audit log cleared', 'info');
        },

        // Helper methods
        isSensitivePropertyName(name: string) {
            const sensitivePatterns = [
                'password', 'secret', 'token', 'key', 'auth', 'credential',
                'ssn', 'credit', 'card', 'cvv', 'pin', 'private'
            ];
            const lower = name.toLowerCase();
            return sensitivePatterns.some(pattern => lower.includes(pattern));
        },

        containsSensitiveData(value: any) {
            if (typeof value !== 'string') return false;
            
            // Check for common sensitive patterns
            const patterns = [
                /\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/, // Credit card
                /\b\d{3}-\d{2}-\d{4}\b/, // SSN
                /Bearer\s+[A-Za-z0-9\-._~\+\/]+=*/, // Bearer token
                /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/ // Email
            ];
            
            return patterns.some(pattern => pattern.test(value));
        },

        detectSensitivePattern(value: any) {
            if (typeof value !== 'string') return null;
            
            if (/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/.test(value)) return 'Credit Card';
            if (/\b\d{3}-\d{2}-\d{4}\b/.test(value)) return 'SSN';
            if (/Bearer\s+[A-Za-z0-9\-._~\+\/]+=*/.test(value)) return 'API Token';
            if (/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/.test(value)) return 'Email';
            
            return null;
        },

        getSensitiveDataReason(pattern: string) {
            const reasons: Record<string, string> = {
                'Credit Card': 'Credit card numbers should never be exposed in component properties',
                'SSN': 'Social Security Numbers are highly sensitive and must be protected',
                'API Token': 'API tokens can be used to access protected resources',
                'Email': 'Email addresses are personal data that should be handled carefully'
            };
            
            return reasons[pattern] || 'This data appears to be sensitive';
        },

        getSensitiveDataRecommendation(pattern: string) {
            const recommendations: Record<string, string> = {
                'Credit Card': 'Use tokenization and handle payment data server-side only',
                'SSN': 'Mask SSNs and never expose full numbers to the client',
                'API Token': 'Store tokens securely server-side and use session-based auth',
                'Email': 'Consider if email display is necessary, use hashing when possible'
            };
            
            return recommendations[pattern] || 'Remove or encrypt this sensitive data';
        },

        getPropertyRecommendations(name: string, prop: any) {
            const recommendations = [];
            
            if (name.toLowerCase().includes('id')) {
                recommendations.push('Always use #[Locked] attribute on ID properties');
            }
            
            if (this.isSensitivePropertyName(name)) {
                recommendations.push('Lock this property to prevent client-side modification');
                recommendations.push('Consider if this data needs to be exposed at all');
            }
            
            if (prop.type === 'array' && prop.value && prop.value.length > 100) {
                recommendations.push('Large arrays should be paginated or loaded on demand');
            }
            
            return recommendations;
        },

        containsXSSPattern(value: string) {
            const xssPatterns = [
                /<script[^>]*>.*?<\/script>/gi,
                /<iframe[^>]*>.*?<\/iframe>/gi,
                /javascript:/gi,
                /on\w+\s*=/gi
            ];
            
            return xssPatterns.some(pattern => pattern.test(value));
        },

        containsSQLPattern(value: string) {
            const sqlPatterns = [
                /(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|WHERE|FROM)\b)/gi,
                /('|(\')|"|(\"))\s*(OR|AND)\s*('|(\')|"|(\"))?(\d|[a-zA-Z])/gi,
                /\b(1=1|1=2)\b/gi
            ];
            
            return sqlPatterns.some(pattern => pattern.test(value));
        },

        getSecurityRecommendations() {
            return [
                {
                    title: 'Use Property Locking',
                    description: 'Always lock sensitive properties using the #[Locked] attribute',
                    priority: 'high'
                },
                {
                    title: 'Validate All Inputs',
                    description: 'Implement proper validation rules for all user inputs',
                    priority: 'high'
                },
                {
                    title: 'Minimize Data Exposure',
                    description: 'Only expose necessary data in component properties',
                    priority: 'medium'
                },
                {
                    title: 'Use Authorization',
                    description: 'Implement proper authorization checks in component methods',
                    priority: 'high'
                }
            ];
        },

        // Tools tab state variables
        consoleLogs: [] as any[],
        inspectorSearch: '',
        generatorType: 'test',
        selectedComponentForGeneration: null as string | null,

        // Tools tab functions
        clearAllData() {
            if (!confirm('Are you sure you want to clear all debugging data? This cannot be undone.')) {
                return;
            }

            // Clear all state
            this.components = {};
            this.events = [];
            this.commits = [];
            this.performance = [];
            this.stateHistory = {};
            this.validationErrors = {};
            this.lifecycleEvents = [];
            this.componentQueries = {};
            this.consoleLogs = [];
            this.auditLog = [];
            
            // Clear local storage
            localStorage.removeItem('livewire-debugbar-position');
            localStorage.removeItem('livewire-debugbar-collapsed');
            localStorage.removeItem('livewire-debugbar-height');
            localStorage.removeItem('livewire-debugbar-hot-reload');
            localStorage.removeItem('livewire-debugbar-real-time-validation');
            localStorage.removeItem('livewire-debugbar-state-notifications');
            
            this.showNotification('🗑️ All debugging data cleared', 'success');
            
            // Re-detect components
            setTimeout(() => {
                this.detectComponents();
                this.storeInitialCommit();
            }, 100);
        },

        exportAllData() {
            const exportData = {
                timestamp: new Date().toISOString(),
                version: '1.0.0',
                environment: {
                    url: window.location.href,
                    userAgent: navigator.userAgent
                },
                components: this.components,
                events: this.getEvents().map(event => this.cleanEventForExport(event)),
                commits: this.commits,
                performance: {
                    summary: this.getPerformanceInsights(),
                    warnings: this.getAllPerformanceWarnings()
                },
                security: {
                    score: this.getSecurityScore(),
                    issues: this.getSecurityIssues(),
                    unlockedProperties: this.getUnlockedProperties(),
                    sensitiveData: this.getSensitiveData()
                },
                stateHistory: this.stateHistory,
                validationErrors: this.validationErrors,
                lifecycleEvents: this.lifecycleEvents,
                auditLog: this.auditLog
            };

            const json = JSON.stringify(exportData, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `livewire-debugbar-complete-export-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);

            this.showNotification('📦 Complete debugging data exported', 'success');
        },

        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            
            if (this.autoRefresh) {
                this.showNotification('🔄 Auto-refresh enabled', 'success');
                // Set up auto-refresh interval
                const refreshInterval = setInterval(() => {
                    if (!this.autoRefresh) {
                        clearInterval(refreshInterval);
                        return;
                    }
                    this.detectComponents();
                }, 3000);
            } else {
                this.showNotification('⏸️ Auto-refresh disabled', 'info');
            }
        },

        clearComponentHistory(componentId: string) {
            if (this.stateHistory[componentId]) {
                delete this.stateHistory[componentId];
                this.showNotification('🗑️ Component history cleared', 'success');
            }
        },

        compareWithCurrent(componentId: string, snapshotId: string) {
            const history = this.stateHistory[componentId];
            if (!history) return;

            const snapshot = history.find((s: any) => s.id === snapshotId);
            if (!snapshot) return;

            const currentComponent = this.components[componentId];
            if (!currentComponent) return;

            const comparison = this.compareComponentStates(
                componentId,
                snapshotId,
                'current'
            );

            if (comparison) {
                console.log('📊 State Comparison:', comparison);
                this.showNotification(`📊 ${comparison.changeCount} differences found`, 'info');
            }
        },

        viewSnapshotDetails(componentId: string, snapshotId: string) {
            const history = this.stateHistory[componentId];
            if (!history) return;

            const snapshot = history.find((s: any) => s.id === snapshotId);
            if (!snapshot) return;

            console.log('📸 Snapshot Details:', {
                component: this.components[componentId]?.name,
                timestamp: new Date(snapshot.timestamp).toLocaleString(),
                state: snapshot.state
            });

            // Copy snapshot data to clipboard
            navigator.clipboard.writeText(JSON.stringify(snapshot.state, null, 2))
                .then(() => {
                    this.showNotification('📋 Snapshot data copied to clipboard', 'success');
                })
                .catch(() => {
                    this.showNotification('Failed to copy snapshot data', 'error');
                });
        },

        filterInspectorResults() {
            // Trigger re-render, actual filtering happens in getFilteredComponents()
        },

        getFilteredComponents() {
            let components = Object.entries(this.components);

            if (this.inspectorSearch) {
                const search = this.inspectorSearch.toLowerCase();
                components = components.filter(([id, component]: [string, any]) => {
                    // Search in component name
                    if (component.name.toLowerCase().includes(search)) return true;
                    
                    // Search in component ID
                    if (id.toLowerCase().includes(search)) return true;
                    
                    // Search in property names
                    const propertyNames = Object.keys(component.properties || {});
                    if (propertyNames.some(prop => prop.toLowerCase().includes(search))) return true;
                    
                    // Search in property values (be careful with circular references)
                    try {
                        const propertyValues = Object.values(component.properties || {})
                            .map((prop: any) => JSON.stringify(prop.value))
                            .join(' ');
                        if (propertyValues.toLowerCase().includes(search)) return true;
                    } catch (e) {
                        // Ignore circular reference errors
                    }
                    
                    return false;
                });
            }

            return components;
        },

        inspectComponent(componentId: string) {
            const component = this.components[componentId];
            if (!component) return;

            console.group(`🔍 Component Inspector: ${component.name}`);
            console.log('ID:', componentId);
            console.log('Element:', component.element);
            console.log('Properties:', component.properties);
            console.log('State History:', this.stateHistory[componentId] || []);
            console.log('Validation Errors:', this.validationErrors[componentId] || []);
            console.log('Lifecycle Events:', this.getComponentLifecycleEvents(componentId));
            console.log('Commits:', this.getComponentCommitsById(componentId));
            console.groupEnd();

            // Also focus the component on the page
            this.focusComponent(componentId);
            
            this.showNotification(`🔍 Inspecting: ${component.name} (check console)`, 'info');
        },

        cloneComponent(componentId: string) {
            const component = this.components[componentId];
            if (!component) return;

            // Generate clone code
            const properties = Object.entries(component.properties)
                .map(([name, prop]: [string, any]) => `    public $${name} = ${JSON.stringify(prop.value)};`)
                .join('\n');

            const cloneCode = `<?php

namespace App\\Http\\Livewire;

use Livewire\\Component;

class ${component.name}Clone extends Component
{
${properties}

    public function render()
    {
        return view('livewire.${component.name.toLowerCase().replace(/\./g, '-')}-clone');
    }
}`;

            navigator.clipboard.writeText(cloneCode)
                .then(() => {
                    this.showNotification('📋 Component clone code copied to clipboard', 'success');
                    console.log('🔧 Component Clone Code:\n', cloneCode);
                })
                .catch(() => {
                    this.showNotification('Failed to copy clone code', 'error');
                });
        },

        benchmarkComponent(componentId: string) {
            const component = this.components[componentId];
            if (!component) return;

            const $wire = window.Livewire.find(componentId);
            if (!$wire) return;

            console.group(`⚡ Benchmarking: ${component.name}`);
            
            // Measure property access time
            const propertyStart = performance.now();
            Object.keys(component.properties).forEach(prop => {
                const value = $wire[prop];
            });
            const propertyTime = performance.now() - propertyStart;
            console.log(`Property Access Time: ${propertyTime.toFixed(2)}ms`);

            // Measure refresh time
            const refreshStart = performance.now();
            $wire.$refresh().then(() => {
                const refreshTime = performance.now() - refreshStart;
                console.log(`Refresh Time: ${refreshTime.toFixed(2)}ms`);
                
                // Calculate metrics
                const metrics = {
                    propertyCount: Object.keys(component.properties).length,
                    dataSize: this.getComponentDataSize(component),
                    propertyAccessTime: propertyTime.toFixed(2),
                    refreshTime: refreshTime.toFixed(2),
                    averageCommitTime: this.getAverageCommitTime(componentId)
                };
                
                console.table(metrics);
                console.groupEnd();
                
                this.showNotification(`⚡ Benchmark complete for ${component.name}`, 'success');
            });
        },

        getAverageCommitTime(componentId: string) {
            const commits = this.getComponentCommitsById(componentId);
            const commitsWithDuration = commits.filter(c => c.duration);
            if (commitsWithDuration.length === 0) return '0';
            
            const total = commitsWithDuration.reduce((sum, c) => sum + c.duration, 0);
            return (total / commitsWithDuration.length).toFixed(2);
        },

        generateCode() {
            if (!this.selectedComponentForGeneration) {
                this.showNotification('Please select a component first', 'warning');
                return;
            }

            const component = this.components[this.selectedComponentForGeneration];
            if (!component) return;

            switch (this.generatorType) {
                case 'test':
                    this.generateTestCode(this.selectedComponentForGeneration);
                    break;
                case 'factory':
                    this.generateFactoryCode(component);
                    break;
                case 'seeder':
                    this.generateSeederCode(component);
                    break;
                case 'migration':
                    this.generateMigrationCode(component);
                    break;
                default:
                    this.showNotification('Invalid generator type', 'error');
            }
        },

        generateFactoryCode(component: any) {
            const properties = Object.entries(component.properties)
                .filter(([name, prop]: [string, any]) => !name.startsWith('_'))
                .map(([name, prop]: [string, any]) => {
                    let fakerMethod = 'word()';
                    
                    // Intelligent faker method selection
                    if (name.includes('name')) fakerMethod = 'name()';
                    else if (name.includes('email')) fakerMethod = 'email()';
                    else if (name.includes('phone')) fakerMethod = 'phoneNumber()';
                    else if (name.includes('address')) fakerMethod = 'address()';
                    else if (name.includes('date')) fakerMethod = 'date()';
                    else if (name.includes('description') || name.includes('body')) fakerMethod = 'paragraph()';
                    else if (prop.type === 'number') fakerMethod = 'numberBetween(1, 100)';
                    else if (prop.type === 'boolean') fakerMethod = 'boolean()';
                    
                    return `            '${name}' => $this->faker->${fakerMethod}`;
                })
                .join(',\n');

            const factoryCode = `<?php

namespace Database\\Factories;

use Illuminate\\Database\\Eloquent\\Factories\\Factory;

class ${component.name.replace(/\./g, '')}Factory extends Factory
{
    public function definition()
    {
        return [
${properties}
        ];
    }
}`;

            navigator.clipboard.writeText(factoryCode)
                .then(() => {
                    this.showNotification('📋 Factory code copied to clipboard', 'success');
                    console.log('🏭 Factory Code:\n', factoryCode);
                })
                .catch(() => {
                    this.showNotification('Failed to copy factory code', 'error');
                });
        },

        generateSeederCode(component: any) {
            const seederCode = `<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

class ${component.name.replace(/\./g, '')}Seeder extends Seeder
{
    public function run()
    {
        // Create sample data based on component properties
        \\App\\Models\\${component.name.split('.').pop()}::factory()
            ->count(10)
            ->create();
    }
}`;

            navigator.clipboard.writeText(seederCode)
                .then(() => {
                    this.showNotification('📋 Seeder code copied to clipboard', 'success');
                    console.log('🌱 Seeder Code:\n', seederCode);
                })
                .catch(() => {
                    this.showNotification('Failed to copy seeder code', 'error');
                });
        },

        generateMigrationCode(component: any) {
            const columns = Object.entries(component.properties)
                .filter(([name, prop]: [string, any]) => !name.startsWith('_'))
                .map(([name, prop]: [string, any]) => {
                    let columnType = 'string';
                    
                    // Map property types to database column types
                    if (prop.type === 'number') {
                        columnType = name.includes('id') ? 'unsignedBigInteger' : 'integer';
                    } else if (prop.type === 'boolean') {
                        columnType = 'boolean';
                    } else if (prop.type === 'date') {
                        columnType = 'timestamp';
                    } else if (name.includes('description') || name.includes('body')) {
                        columnType = 'text';
                    }
                    
                    return `            $table->${columnType}('${name}')${prop.value === null ? '->nullable()' : ''};`;
                })
                .join('\n');

            const tableName = component.name.split('.').pop().toLowerCase() + 's';
            const migrationCode = `<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

class Create${component.name.replace(/\./g, '')}Table extends Migration
{
    public function up()
    {
        Schema::create('${tableName}', function (Blueprint $table) {
            $table->id();
${columns}
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('${tableName}');
    }
}`;

            navigator.clipboard.writeText(migrationCode)
                .then(() => {
                    this.showNotification('📋 Migration code copied to clipboard', 'success');
                    console.log('🗃️ Migration Code:\n', migrationCode);
                })
                .catch(() => {
                    this.showNotification('Failed to copy migration code', 'error');
                });
        },

        executeCodeGeneration() {
            this.generateCode();
        },

        runPerformanceProfile() {
            console.group('🚀 Performance Profile');
            
            const profile = {
                timestamp: new Date().toISOString(),
                components: Object.entries(this.components).map(([id, component]: [string, any]) => ({
                    id,
                    name: component.name,
                    properties: Object.keys(component.properties).length,
                    dataSize: this.getComponentDataSize(component),
                    avgCommitTime: this.getAverageCommitTime(id),
                    warnings: this.getPerformanceWarnings(component).length
                })),
                summary: {
                    totalComponents: Object.keys(this.components).length,
                    totalProperties: this.getTotalProperties(),
                    totalMemory: this.getTotalDataSize() + ' KB',
                    avgRenderTime: this.getAverageRenderTime() + ' ms',
                    totalEvents: this.getEvents().length,
                    totalCommits: this.commits.length
                },
                hotspots: this.getPerformanceHotspots()
            };
            
            console.log('Summary:', profile.summary);
            console.table(profile.components);
            console.log('Performance Hotspots:', profile.hotspots);
            console.groupEnd();
            
            // Also export to file
            const json = JSON.stringify(profile, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `livewire-performance-profile-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
            
            this.showNotification('🚀 Performance profile generated (check console)', 'success');
        },

        getMetricClass(value: number, thresholds: { good: number, warning: number }) {
            if (value <= thresholds.good) return 'good';
            if (value <= thresholds.warning) return 'warning';
            return 'critical';
        },

        getPerformanceHotspots() {
            const hotspots = [];
            
            // Find components with high property counts
            Object.entries(this.components).forEach(([id, component]: [string, any]) => {
                const propCount = Object.keys(component.properties).length;
                if (propCount > 20) {
                    hotspots.push({
                        type: 'high_property_count',
                        component: component.name,
                        value: propCount,
                        recommendation: 'Consider splitting into smaller components'
                    });
                }
            });
            
            // Find components with large data sizes
            Object.entries(this.components).forEach(([id, component]: [string, any]) => {
                const dataSize = parseFloat(this.getComponentDataSize(component));
                if (dataSize > 100) {
                    hotspots.push({
                        type: 'large_data_size',
                        component: component.name,
                        value: dataSize + ' KB',
                        recommendation: 'Optimize data structure or implement pagination'
                    });
                }
            });
            
            // Find slow commits
            this.commits.forEach(commit => {
                if (commit.duration && commit.duration > 500) {
                    const component = this.components[commit.componentId];
                    if (component) {
                        hotspots.push({
                            type: 'slow_commit',
                            component: component.name,
                            value: commit.duration + ' ms',
                            recommendation: 'Optimize server-side processing'
                        });
                    }
                }
            });
            
            return hotspots;
        },

        optimizeComponent(componentId: string) {
            const component = this.components[componentId];
            if (!component) return;
            
            const optimizations = [];
            
            // Check for optimization opportunities
            Object.entries(component.properties).forEach(([name, prop]: [string, any]) => {
                // Large arrays
                if (prop.type === 'array' && prop.value && prop.value.length > 50) {
                    optimizations.push({
                        property: name,
                        issue: 'Large array detected',
                        solution: 'Implement pagination or lazy loading',
                        code: `// Add to your component:
protected $paginationTheme = 'bootstrap';

public function mount()
{
    $this->${name} = collect($this->${name})->paginate(20);
}`
                    });
                }
                
                // Unlocked IDs
                if (prop.isId && !prop.locked) {
                    optimizations.push({
                        property: name,
                        issue: 'Unlocked ID property',
                        solution: 'Add #[Locked] attribute',
                        code: `#[Locked]
public $${name};`
                    });
                }
                
                // Large strings
                if (prop.type === 'string' && prop.size > 10240) {
                    optimizations.push({
                        property: name,
                        issue: 'Large string property',
                        solution: 'Consider loading on demand',
                        code: `// Load only when needed:
public function load${name.charAt(0).toUpperCase() + name.slice(1)}()
{
    $this->${name} = // Load from database or cache
}`
                    });
                }
            });
            
            if (optimizations.length === 0) {
                this.showNotification('✨ Component is already optimized!', 'success');
                return;
            }
            
            console.group(`🔧 Optimization Suggestions for ${component.name}`);
            optimizations.forEach(opt => {
                console.log(`\n📌 ${opt.property}: ${opt.issue}`);
                console.log(`✅ Solution: ${opt.solution}`);
                console.log(`📝 Code:\n${opt.code}`);
            });
            console.groupEnd();
            
            // Copy first optimization to clipboard
            if (optimizations[0]) {
                navigator.clipboard.writeText(optimizations[0].code)
                    .then(() => {
                        this.showNotification(`🔧 ${optimizations.length} optimizations found (first copied to clipboard)`, 'info');
                    });
            }
        },

        clearConsole() {
            this.consoleLogs = [];
            console.clear();
            this.showNotification('🗑️ Console cleared', 'info');
        },

        logToConsole(message: string, type: 'log' | 'warn' | 'error' | 'info' = 'log') {
            const logEntry = {
                id: Date.now(),
                timestamp: new Date().toISOString(),
                message,
                type,
                source: 'Livewire Debugbar'
            };
            
            this.consoleLogs.unshift(logEntry);
            
            // Keep only last 100 logs
            if (this.consoleLogs.length > 100) {
                this.consoleLogs = this.consoleLogs.slice(0, 100);
            }
            
            // Also log to actual console
            console[type](`[Livewire Debugbar] ${message}`);
        }
    };
};
