<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QueryCraft - Performance Analysis</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('vendor/querycraft/favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        * { transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .code-block {
            font-family: 'SF Mono', 'Monaco', 'Cascadia Code', 'Courier New', monospace;
            font-size: 0.8rem;
            line-height: 1.6;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        .file-path-badge {
            font-family: 'SF Mono', 'Monaco', 'Cascadia Code', 'Courier New', monospace;
            font-size: 0.75rem;
        }
        input[type=range]::-webkit-slider-thumb { cursor: pointer; }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
<div x-data="queryAnalyzer()" x-init="init()" class="min-h-screen">

    @include('querycraft::components.header')
    @include('querycraft::components.config-panel')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('querycraft::components.stats-cards')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @include('querycraft::components.request-panel')
            @include('querycraft::components.results-panel')
        </div>
    </main>

    @include('querycraft::components.footer')

</div>

<script>
function queryAnalyzer() {
    return {
        // ─── State ────────────────────────────────────────────
        url: '',
        method: 'GET',
        headers: [{ key: 'Accept', value: 'application/json' }],
        body: '',
        loading: false,
        results: null,
        error: null,
        serverError: null,
        clientError: null,
        showTrace: false,
        darkMode: false,
        showHeaders: false,
        showQueries: false,

        // ─── Config state ─────────────────────────────────────
        showConfig:    false,
        configLoading: false,
        configSaving:  false,
        configSaved:   false,
        configError:   null,
        config: {
            detectors: {
                n1:              true,
                slow_query:      true,
                missing_index:   true,
                duplicate_query: true,
            },
            thresholds: {
                n1_count:        5,
                slow_query_ms:   100,
                duplicate_count: 2,
            },
            weights: {
                query_count: 40,
                query_time:  30,
                issues:      30,
            },
        },

        // ─── Computed ─────────────────────────────────────────
        get weightsTotal() {
            return (+this.config.weights.query_count)
                 + (+this.config.weights.query_time)
                 + (+this.config.weights.issues);
        },

        // ─── Score Helpers ────────────────────────────────────
        scoreHelpers: {
            getColor(score) {
                if (score >= 90) return 'text-green-600 dark:text-green-400';
                if (score >= 70) return 'text-yellow-600 dark:text-yellow-400';
                if (score >= 50) return 'text-orange-600 dark:text-orange-400';
                return 'text-red-600 dark:text-red-400';
            },
            getBackground(score) {
                if (score >= 90) return 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800';
                if (score >= 70) return 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800';
                if (score >= 50) return 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800';
                return 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800';
            },
            getEmoji(score) {
                if (score >= 90) return '🟢';
                if (score >= 70) return '🟡';
                if (score >= 50) return '🟠';
                return '🔴';
            },
            getDescription(score) {
                if (score >= 90) return 'Excellent performance — keep it up!';
                if (score >= 80) return 'Good performance';
                if (score >= 70) return 'Acceptable — some improvements needed';
                if (score >= 60) return 'Below average — review issues';
                return 'Critical issues detected — action required';
            },
        },

        // ─── Severity Helpers ─────────────────────────────────
        severityHelpers: {
            getWrapperClass(severity) {
                const map = {
                    critical: 'border-red-300 dark:border-red-700 bg-white dark:bg-gray-800',
                    high:     'border-orange-300 dark:border-orange-700 bg-white dark:bg-gray-800',
                    medium:   'border-yellow-300 dark:border-yellow-700 bg-white dark:bg-gray-800',
                    low:      'border-blue-300 dark:border-blue-700 bg-white dark:bg-gray-800',
                };
                return 'border ' + (map[severity] || 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800');
            },
            getHeaderClass(severity) {
                const map = {
                    critical: 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                    high:     'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
                    medium:   'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                    low:      'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
                };
                return map[severity] || 'bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700';
            },
            getBadgeClass(severity) {
                const map = {
                    critical: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 ring-1 ring-red-300 dark:ring-red-700',
                    high:     'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-400 ring-1 ring-orange-300 dark:ring-orange-700',
                    medium:   'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-400 ring-1 ring-yellow-300 dark:ring-yellow-700',
                    low:      'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 ring-1 ring-blue-300 dark:ring-blue-700',
                };
                return map[severity] || 'bg-gray-100 text-gray-700 ring-1 ring-gray-300';
            },
            getIcon(type) {
                const map = {
                    'n+1':             '🔁',
                    'missing_index':   '🗂',
                    'slow_query':      '🐢',
                    'duplicate_query': '📋',
                };
                return map[type] || '⚠️';
            },
        },

        // ─── Helpers ──────────────────────────────────────────
        formatIssueType(type) {
            const map = {
                'n+1':             'N+1 Query Problem',
                'missing_index':   'Missing Index',
                'slow_query':      'Slow Query',
                'duplicate_query': 'Duplicate Query',
            };
            return map[type] || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        shortenPath(path) {
            if (!path) return 'Unknown';
            const parts = path.split('/');
            const idx = parts.findIndex(p => ['app','routes','resources','database'].includes(p));
            return idx !== -1 ? '/' + parts.slice(idx).join('/') : path;
        },

        // ─── Init ─────────────────────────────────────────────
        init() {
            this.darkMode = localStorage.getItem('theme') === 'dark';
            this.updateTheme();
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            this.updateTheme();
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        },

        updateTheme() {
            document.documentElement.classList.toggle('dark', this.darkMode);
        },

        loadExample(url, method) {
            this.url    = url;
            this.method = method;
            this.body   = method === 'POST' ? '{\n  "title": "Example",\n  "body": "Content"\n}' : '';
        },

        // ─── Config methods ───────────────────────────────────
        async openConfig() {
            this.showConfig    = true;
            this.configLoading = true;
            this.configSaved   = false;
            this.configError   = null;

            try {
                const res  = await fetch('/querycraft/config', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success) {
                    this.config = data.config;
                }
            } catch (e) {
                this.configError = 'Failed to load config';
            } finally {
                this.configLoading = false;
            }
        },

        async doSaveConfig() {
            this.configSaving = true;
            this.configSaved  = false;
            this.configError  = null;

            try {
                const res  = await fetch('/querycraft/config', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.config),
                });
                const data = await res.json();

                if (data.success) {
                    this.config      = data.config;
                    this.configSaved = true;
                    setTimeout(() => this.configSaved = false, 3000);
                } else {
                    this.configError = data.message || 'Save failed';
                }
            } catch (e) {
                this.configError = 'Network error';
            } finally {
                this.configSaving = false;
            }
        },

        async doResetConfig() {
            this.configSaving = true;
            this.configError  = null;

            try {
                const res  = await fetch('/querycraft/config', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await res.json();

                if (data.success) {
                    this.config      = data.config;
                    this.configSaved = true;
                    setTimeout(() => this.configSaved = false, 3000);
                }
            } catch (e) {
                this.configError = 'Reset failed';
            } finally {
                this.configSaving = false;
            }
        },

        // ─── Analyze ─────────────────────────────────────────
        async analyze() {
            if (!this.url) return;

            this.loading     = true;
            this.results     = null;
            this.error       = null;
            this.serverError = null;
            this.clientError = null;
            this.showTrace   = false;

            try {
                const headersObj = {};
                this.headers.forEach(h => {
                    if (h.key && h.value) headersObj[h.key] = h.value;
                });

                const response = await fetch('/querycraft/analyze', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        url:     this.url,
                        method:  this.method,
                        headers: headersObj,
                        body:    this.body,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.results = data.analysis;

                } else if (data.error_type === 'server_error') {
                    this.serverError = data;

                } else if (data.error_type === 'client_error') {
                    this.error       = data.error;
                    this.clientError = data;

                } else if (data.error_type === 'route_not_found') {
                    this.error = data.error + (data.suggestions?.length
                        ? '\n\nSimilar routes:\n' + data.suggestions.map(s => s.uri).join('\n')
                        : '');

                } else {
                    this.error = data.error || 'Analysis failed';
                }

            } catch (err) {
                this.error = 'Network error: ' + err.message;
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
</body>
</html>