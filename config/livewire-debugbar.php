<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Livewire Debugbar Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the Livewire debugbar. This should typically be false
    | in production environments.
    |
    */
    'enabled' => env('LIVEWIRE_DEBUGBAR_ENABLED', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    |
    | The position of the debugbar on the screen.
    | Options: 'bottom', 'top'
    |
    */
    'position' => env('LIVEWIRE_DEBUGBAR_POSITION', 'bottom'),

    /*
    |--------------------------------------------------------------------------
    | Hot Reload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the hot reload functionality for automatic component refreshing
    | when files are modified during development.
    |
    */
    'hot_reload' => [
        'enabled' => env('LIVEWIRE_DEBUGBAR_HOT_RELOAD', true),
        'interval' => env('LIVEWIRE_DEBUGBAR_INTERVAL', 3000), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    |
    | Configure the thresholds for performance alerts. When these limits
    | are exceeded, warnings will be displayed in the debugbar.
    |
    */
    'thresholds' => [
        'max_properties' => 50,              // Maximum number of public properties
        'max_serialized_size' => 1024 * 10, // 10KB maximum serialized size
        'slow_render_time' => 100,           // 100ms maximum render time
        'max_queries' => 10,                 // Maximum queries per component
        'slow_query_time' => 100,            // 100ms maximum query time
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Collectors
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific data collectors. Disabling collectors
    | can improve performance if you don't need certain features.
    |
    */
    'collectors' => [
        'components' => true,    // Component state and properties
        'properties' => true,    // Property details and manipulation
        'events' => true,        // Event dispatching and lifecycle
        'performance' => true,   // Performance metrics and alerts
        'queries' => true,       // Database query tracking
        'hot_reload' => true,    // Hot reload functionality
    ],

    /*
    |--------------------------------------------------------------------------
    | Interface Configuration
    |--------------------------------------------------------------------------
    |
    | Customize the debugbar interface appearance and behavior.
    |
    */
    'interface' => [
        'default_tab' => 'components',          // Default active tab
        'collapsed_by_default' => false,       // Start collapsed
        'show_memory_usage' => true,           // Display memory metrics
        'show_query_count' => true,            // Display query counts
        'animate_updates' => true,             // Animate component updates
        'keyboard_shortcuts' => true,         // Enable keyboard shortcuts
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Configuration
    |--------------------------------------------------------------------------
    |
    | Configure event capturing and manipulation settings.
    |
    */
    'events' => [
        'capture_lifecycle' => true,          // Capture mount, hydrate, etc.
        'capture_dispatched' => true,         // Capture dispatch events
        'max_events_history' => 100,          // Maximum events to keep in memory
        'auto_clear_on_page_load' => true,    // Clear events on page refresh
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security settings for the debugbar.
    |
    */
    'security' => [
        'allowed_ips' => [                    // Restrict access by IP (empty = allow all)
            // '127.0.0.1',
            // '::1',
        ],
        'hide_sensitive_data' => true,        // Hide potentially sensitive information
        'max_data_size' => 1024 * 50,        // 50KB max data per component
    ],
];
