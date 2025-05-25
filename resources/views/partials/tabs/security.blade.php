{{-- Security Tab --}}
<div x-show="activeTab === 'security'" class="livewire-debugbar__panel livewire-debugbar__panel--active">
    {{-- Security Scanner --}}
    <div class="security-scanner mb-6">
        <div class="security-scanner-header">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('livewire-debugbar::debugbar.security.security_scanner') ?? 'Security Scanner' }}</span>
            </h3>
            <div class="security-scanner-controls">
                <div class="security-scanner-status" x-bind:class="{ 'scanning': isScanning }">
                    <span class="status-indicator w-2 h-2 rounded-full" 
                          x-bind:class="isScanning ? 'bg-blue-400' : 'bg-gray-400'"></span>
                    <span x-text="isScanning ? 'Scanning...' : 'Idle'"></span>
                </div>
                <button @click="runSecurityScan()" 
                        x-bind:disabled="isScanning"
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-700 text-white rounded text-sm transition-colors">
                    <span x-show="!isScanning">{{ __('livewire-debugbar::debugbar.security.run_scan') ?? 'Run Scan' }}</span>
                    <span x-show="isScanning">{{ __('livewire-debugbar::debugbar.security.scanning') ?? 'Scanning...' }}</span>
                </button>
            </div>
        </div>
        
        {{-- Real-time Security Validation Toggle --}}
        <div class="mt-4 p-3 bg-gray-900 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox"
                               x-model="realTimeValidation"
                               @change="toggleRealTimeValidation()"
                               class="rounded border-gray-600 bg-gray-950 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-300">{{ __('livewire-debugbar::debugbar.security.real_time_protection') ?? 'Real-time Protection' }}</span>
                    </label>
                    <div class="h-4 w-px bg-gray-700"></div>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox"
                               x-model="stateChangeNotifications"
                               @change="toggleStateChangeNotifications()"
                               class="rounded border-gray-600 bg-gray-950 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-300">{{ __('livewire-debugbar::debugbar.security.security_alerts') ?? 'Security Alerts' }}</span>
                    </label>
                </div>
                <div class="text-xs text-gray-500">
                    Last scan: <span x-text="lastScanTime || 'Never'"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Security Score --}}
    <div class="mb-6">
        <div class="security-score-card">
            <div class="security-score-value" x-bind:class="getSecurityScoreClass()" x-text="getSecurityScore()"></div>
            <div class="security-score-label">{{ __('livewire-debugbar::debugbar.security.security_score') ?? 'Security Score' }}</div>
            <div class="mt-2 text-xs text-gray-400" x-text="getSecurityScoreDescription()"></div>
        </div>
    </div>

    {{-- Security Overview Grid --}}
    <div class="security-overview-grid mb-6">
        <div class="security-metric-card" x-bind:class="getSecurityIssues().filter(i => i.level === 'critical').length > 0 ? 'critical' : 'success'">
            <div class="security-metric-value" x-text="getSecurityIssues().filter(i => i.level === 'critical').length"></div>
            <div class="security-metric-label">{{ __('livewire-debugbar::debugbar.security.critical_vulnerabilities') ?? 'Critical Vulnerabilities' }}</div>
        </div>
        
        <div class="security-metric-card warning">
            <div class="security-metric-value text-yellow-400" x-text="getSecurityIssues().filter(i => i.level === 'high').length"></div>
            <div class="security-metric-label">{{ __('livewire-debugbar::debugbar.security.high_risk_issues') ?? 'High Risk Issues' }}</div>
        </div>
        
        <div class="security-metric-card info">
            <div class="security-metric-value text-blue-400" x-text="getUnlockedProperties().length"></div>
            <div class="security-metric-label">{{ __('livewire-debugbar::debugbar.security.exposed_properties') ?? 'Exposed Properties' }}</div>
        </div>
        
        <div class="security-metric-card" x-bind:class="getSensitiveData().length > 0 ? 'warning' : 'success'">
            <div class="security-metric-value text-purple-400" x-text="getSensitiveData().length"></div>
            <div class="security-metric-label">{{ __('livewire-debugbar::debugbar.security.sensitive_exposures') ?? 'Sensitive Exposures' }}</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">{{ __('livewire-debugbar::debugbar.security.quick_actions') ?? 'Quick Actions' }}</h3>
        <div class="security-quick-actions">
            <button @click="fixAllSecurityIssues()" class="quick-action-button">
                <svg class="quick-action-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('livewire-debugbar::debugbar.security.auto_fix') ?? 'Auto Fix Issues' }}</span>
            </button>
            
            <button @click="exportSecurityReport()" class="quick-action-button">
                <svg class="quick-action-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('livewire-debugbar::debugbar.security.export_report') ?? 'Export Report' }}</span>
            </button>
            
            <button @click="lockAllProperties()" class="quick-action-button">
                <svg class="quick-action-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('livewire-debugbar::debugbar.security.lock_all') ?? 'Lock All Properties' }}</span>
            </button>
            
            <button @click="clearSecurityCache()" class="quick-action-button">
                <svg class="quick-action-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('livewire-debugbar::debugbar.security.clear_cache') ?? 'Clear Cache' }}</span>
            </button>
        </div>
    </div>

    {{-- Active Security Issues --}}
    <div x-show="getSecurityIssues().length > 0" class="mb-6">
        <div class="bg-gray-950 rounded-lg border border-gray-700">
            <div class="p-4 border-b border-gray-700">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">
                    {{ __('livewire-debugbar::debugbar.security.active_security_issues') ?? 'Active Security Issues' }}
                    <span class="ml-2 text-xs font-normal text-gray-500">(<span x-text="getSecurityIssues().length"></span> total)</span>
                </h3>
            </div>
            
            <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
                <template x-for="issue in getSecurityIssues()" :key="issue.id">
                    <div class="security-issue" x-bind:class="issue.level">
                        <div class="security-issue-header">
                            <div class="security-issue-title">
                                <svg class="security-issue-icon" fill="currentColor" viewBox="0 0 20 20"
                                     x-bind:class="{
                                         'text-red-400': issue.level === 'critical',
                                         'text-orange-400': issue.level === 'high',
                                         'text-yellow-400': issue.level === 'medium',
                                         'text-blue-400': issue.level === 'low'
                                     }">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium text-sm" x-text="issue.title"></span>
                            </div>
                            <span class="security-issue-badge" x-bind:class="issue.level" x-text="issue.level"></span>
                        </div>
                        
                        <div class="security-issue-details" x-text="issue.message"></div>
                        
                        <div class="security-issue-meta">
                            <div class="security-issue-location">
                                <span>{{ __('livewire-debugbar::debugbar.security.component') }}:</span>
                                <code class="ml-1" x-text="issue.component"></code>
                                <template x-if="issue.property">
                                    <span>
                                        <span class="mx-1">•</span>
                                        <span>{{ __('livewire-debugbar::debugbar.security.property') }}:</span>
                                        <code class="ml-1" x-text="issue.property"></code>
                                    </span>
                                </template>
                            </div>
                            
                            <div class="security-issue-actions">
                                <button x-show="issue.fixable" 
                                        @click="fixSecurityIssue(issue)"
                                        class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs transition-colors">
                                    {{ __('livewire-debugbar::debugbar.security.fix_issue') ?? 'Fix Issue' }}
                                </button>
                                <button @click="viewSecurityIssueDetails(issue)"
                                        class="px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-xs transition-colors">
                                    {{ __('livewire-debugbar::debugbar.security.details') ?? 'Details' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Vulnerability Analysis --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- XSS Vulnerabilities --}}
        <div class="vulnerability-card">
            <div class="vulnerability-header">
                <h4 class="vulnerability-type">{{ __('livewire-debugbar::debugbar.security.xss_vulnerabilities') ?? 'XSS Vulnerabilities' }}</h4>
                <span class="vulnerability-count" x-text="getXSSVulnerabilities().length"></span>
            </div>
            <div class="vulnerability-list">
                <template x-for="vuln in getXSSVulnerabilities().slice(0, 3)" :key="vuln.id">
                    <div class="vulnerability-item">
                        <svg class="vulnerability-item-icon text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <div class="vulnerability-item-content">
                            <div class="vulnerability-item-title" x-text="vuln.component"></div>
                            <div class="vulnerability-item-desc" x-text="vuln.description"></div>
                        </div>
                    </div>
                </template>
                <div x-show="getXSSVulnerabilities().length === 0" class="text-sm text-gray-500 text-center py-4">
                    {{ __('livewire-debugbar::debugbar.security.no_xss_found') ?? 'No XSS vulnerabilities detected' }}
                </div>
            </div>
        </div>

        {{-- SQL Injection Risks --}}
        <div class="vulnerability-card">
            <div class="vulnerability-header">
                <h4 class="vulnerability-type">{{ __('livewire-debugbar::debugbar.security.sql_injection_risks') ?? 'SQL Injection Risks' }}</h4>
                <span class="vulnerability-count" x-text="getSQLInjectionRisks().length"></span>
            </div>
            <div class="vulnerability-list">
                <template x-for="risk in getSQLInjectionRisks().slice(0, 3)" :key="risk.id">
                    <div class="vulnerability-item">
                        <svg class="vulnerability-item-icon text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/>
                            <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/>
                            <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/>
                        </svg>
                        <div class="vulnerability-item-content">
                            <div class="vulnerability-item-title" x-text="risk.query"></div>
                            <div class="vulnerability-item-desc" x-text="risk.reason"></div>
                        </div>
                    </div>
                </template>
                <div x-show="getSQLInjectionRisks().length === 0" class="text-sm text-gray-500 text-center py-4">
                    {{ __('livewire-debugbar::debugbar.security.no_sql_risks') ?? 'No SQL injection risks detected' }}
                </div>
            </div>
        </div>
    </div>

    {{-- CSRF Token Status --}}
    <div class="bg-gray-950 rounded-lg border border-gray-700 p-4 mb-6">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('livewire-debugbar::debugbar.security.csrf_protection') ?? 'CSRF Protection' }}</h3>
            <div class="csrf-status" x-bind:class="getCSRFStatus().status">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span x-text="getCSRFStatus().label"></span>
            </div>
        </div>
        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Token:</span>
                <code class="text-xs text-gray-400 block truncate" x-text="getCSRFStatus().token"></code>
            </div>
            <div>
                <span class="text-gray-500">Expires:</span>
                <span class="text-gray-300 block" x-text="getCSRFStatus().expires"></span>
            </div>
            <div>
                <span class="text-gray-500">Verified:</span>
                <span class="text-gray-300 block" x-text="getCSRFStatus().verified ? 'Yes' : 'No'"></span>
            </div>
            <div>
                <span class="text-gray-500">Requests:</span>
                <span class="text-gray-300 block" x-text="getCSRFStatus().requestCount"></span>
            </div>
        </div>
    </div>

    {{-- Permission Matrix --}}
    <div class="permission-matrix mb-6">
        <div class="permission-matrix-header">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('livewire-debugbar::debugbar.security.permission_matrix') ?? 'Permission Matrix' }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="permission-matrix-table">
                <thead>
                    <tr>
                        <th>{{ __('livewire-debugbar::debugbar.security.component') ?? 'Component' }}</th>
                        <th>{{ __('livewire-debugbar::debugbar.security.method') ?? 'Method' }}</th>
                        <th>{{ __('livewire-debugbar::debugbar.security.authorization') ?? 'Authorization' }}</th>
                        <th>{{ __('livewire-debugbar::debugbar.security.validation') ?? 'Validation' }}</th>
                        <th>{{ __('livewire-debugbar::debugbar.security.status') ?? 'Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="perm in getPermissionMatrix()" :key="perm.id">
                        <tr>
                            <td class="font-mono text-sm" x-text="perm.component"></td>
                            <td class="font-mono text-sm" x-text="perm.method"></td>
                            <td>
                                <span class="permission-status" 
                                      x-bind:class="perm.hasAuthorization ? 'allowed' : 'denied'"
                                      x-text="perm.hasAuthorization ? 'Protected' : 'Unprotected'"></span>
                            </td>
                            <td>
                                <span class="permission-status" 
                                      x-bind:class="perm.hasValidation ? 'allowed' : 'conditional'"
                                      x-text="perm.hasValidation ? 'Validated' : 'No Validation'"></span>
                            </td>
                            <td>
                                <span class="permission-status" 
                                      x-bind:class="perm.isSecure ? 'allowed' : 'denied'"
                                      x-text="perm.isSecure ? 'Secure' : 'At Risk'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Security Recommendations --}}
    <div class="security-recommendations">
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('livewire-debugbar::debugbar.security.security_recommendations') ?? 'Security Recommendations' }}</h3>
        </div>
        
        <div class="recommendation-item">
            <div class="recommendation-header">
                <svg class="recommendation-icon success" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="recommendation-content">
                    <h4 class="recommendation-title">{{ __('livewire-debugbar::debugbar.security.implement_property_locks') ?? 'Implement Property Locks' }}</h4>
                    <p class="recommendation-desc">{{ __('livewire-debugbar::debugbar.security.implement_property_locks_desc') ?? 'Use the #[Locked] attribute on all sensitive properties to prevent client-side manipulation.' }}</p>
                    <pre class="recommendation-code">#[Locked]
public $userId;

#[Locked] 
public $isAdmin = false;</pre>
                </div>
            </div>
        </div>
        
        <div class="recommendation-item">
            <div class="recommendation-header">
                <svg class="recommendation-icon warning" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="recommendation-content">
                    <h4 class="recommendation-title">{{ __('livewire-debugbar::debugbar.security.validate_all_inputs') ?? 'Validate All User Inputs' }}</h4>
                    <p class="recommendation-desc">{{ __('livewire-debugbar::debugbar.security.validate_all_inputs_desc') ?? 'Always validate user inputs using Laravel\'s validation rules in your component methods.' }}</p>
                    <pre class="recommendation-code">protected $rules = [
    'email' => 'required|email',
    'password' => 'required|min:8'
];</pre>
                </div>
            </div>
        </div>
        
        <div class="recommendation-item">
            <div class="recommendation-header">
                <svg class="recommendation-icon error" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="recommendation-content">
                    <h4 class="recommendation-title">{{ __('livewire-debugbar::debugbar.security.authorize_actions') ?? 'Authorize All Actions' }}</h4>
                    <p class="recommendation-desc">{{ __('livewire-debugbar::debugbar.security.authorize_actions_desc') ?? 'Use Laravel\'s authorization features to protect sensitive component methods.' }}</p>
                    <pre class="recommendation-code">public function deletePost($postId)
{
    $this->authorize('delete', Post::find($postId));
    // Delete logic here
}</pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Security Audit Log --}}
    <div class="security-audit-log">
        <div class="audit-log-header">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">{{ __('livewire-debugbar::debugbar.security.security_audit_log') ?? 'Security Audit Log' }}</h3>
            <div class="audit-log-filters">
                <select x-model="auditLogFilter" class="text-xs bg-gray-800 border-gray-700 rounded">
                    <option value="all">{{ __('livewire-debugbar::debugbar.security.all_events') ?? 'All Events' }}</option>
                    <option value="critical">{{ __('livewire-debugbar::debugbar.security.critical_only') ?? 'Critical Only' }}</option>
                    <option value="warnings">{{ __('livewire-debugbar::debugbar.security.warnings') ?? 'Warnings' }}</option>
                </select>
                <button @click="clearAuditLog()" class="text-xs text-gray-400 hover:text-gray-300">
                    {{ __('livewire-debugbar::debugbar.security.clear_log') ?? 'Clear Log' }}
                </button>
            </div>
        </div>
        
        <div class="max-h-64 overflow-y-auto">
            <template x-for="entry in getFilteredAuditLog()" :key="entry.id">
                <div class="audit-log-entry">
                    <div class="audit-log-entry-header">
                        <div class="audit-log-entry-type">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"
                                 x-bind:class="{
                                     'text-red-400': entry.level === 'critical',
                                     'text-yellow-400': entry.level === 'warning',
                                     'text-blue-400': entry.level === 'info'
                                 }">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span x-text="entry.type"></span>
                        </div>
                        <span class="audit-log-entry-time" x-text="entry.time"></span>
                    </div>
                    <div class="audit-log-entry-details" x-text="entry.message"></div>
                </div>
            </template>
            
            <div x-show="getFilteredAuditLog().length === 0" class="p-4 text-center text-sm text-gray-500">
                {{ __('livewire-debugbar::debugbar.security.no_audit_entries') ?? 'No audit log entries' }}
            </div>
        </div>
    </div>
</div>