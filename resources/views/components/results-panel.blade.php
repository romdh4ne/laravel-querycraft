<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

    {{-- Panel Header --}}
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">Analysis Results</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Performance insights and file locations</p>
        </div>
        <div x-show="results" x-cloak>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                  :class="results?.issues?.length === 0
                      ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                      : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'"
                  x-text="results?.issues?.length === 0 ? '✓ No Issues' : results?.issues?.length + ' Issue(s)'">
            </span>
        </div>
        <div x-show="serverError" x-cloak>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-red-600 text-white">
                500 ERROR
            </span>
        </div>
    </div>

    <div class="p-6">

        {{-- ── SERVER ERROR (500) ───────────────────────────────── --}}
        <div x-show="serverError" x-cloak class="animate-fade-in space-y-4">

            {{-- Error Card --}}
            <div class="rounded-xl overflow-hidden border border-red-300 dark:border-red-700">

                {{-- Header --}}
                <div class="px-4 py-3 bg-red-50 dark:bg-red-900/30 border-b border-red-200 dark:border-red-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-red-700 dark:text-red-400">
                            HTTP <span x-text="serverError?.status || 500"></span> — Server Error
                        </div>
                        <div class="text-xs text-red-500 mt-0.5 font-mono truncate"
                             x-text="serverError?.exception_class || 'Exception'"></div>
                    </div>
                    <span class="px-2.5 py-1 bg-red-600 text-white text-xs font-bold rounded-full flex-shrink-0 animate-pulse">
                        CRASHED
                    </span>
                </div>

                {{-- Error Message --}}
                <div class="px-4 py-3 border-b border-red-100 dark:border-red-800/50">
                    <div class="text-xs font-semibold text-red-500 dark:text-red-400 uppercase tracking-wide mb-1.5">
                        Error Message
                    </div>
                    <div class="font-mono text-sm text-red-800 dark:text-red-300 bg-red-50 dark:bg-red-900/20 rounded-lg px-3 py-2.5 break-words leading-relaxed"
                         x-text="serverError?.error_message || serverError?.error || 'Unknown server error'">
                    </div>
                </div>

                {{-- Source Location --}}
                <div x-show="serverError?.exception_file"
                     class="px-4 py-3 border-b border-red-100 dark:border-red-800/50">
                    <div class="text-xs font-semibold text-red-500 dark:text-red-400 uppercase tracking-wide mb-1.5">
                        Where it crashed
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- File --}}
                        <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 rounded-md px-2.5 py-1.5 min-w-0">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <code class="file-path-badge text-gray-700 dark:text-gray-300 truncate max-w-[240px]"
                                  x-text="serverError?.exception_file"
                                  :title="serverError?.exception_file"></code>
                        </div>

                        {{-- Line --}}
                        <div x-show="serverError?.exception_line"
                             class="flex items-center gap-1.5 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md px-2.5 py-1.5 flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            <span class="file-path-badge font-bold text-red-600 dark:text-red-400">
                                Line <span x-text="serverError?.exception_line"></span>
                            </span>
                        </div>

                        {{-- Copy --}}
                        <button @click="navigator.clipboard.writeText((serverError?.exception_file || '') + ':' + (serverError?.exception_line || ''))"
                                class="flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors ml-auto"
                                title="Copy path">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                </div>

                {{-- Stack Trace --}}
                <div x-show="serverError?.exception_trace?.length > 0"
                     class="px-4 py-3 border-b border-red-100 dark:border-red-800/50">
                    <button @click="showTrace = !showTrace"
                            class="flex items-center justify-between w-full text-xs font-semibold text-red-500 dark:text-red-400 uppercase tracking-wide hover:text-red-700 dark:hover:text-red-300 transition-colors">
                        <span>Stack Trace (<span x-text="serverError?.exception_trace?.length"></span> frames)</span>
                        <svg class="w-4 h-4 transition-transform" :class="showTrace ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="showTrace" class="mt-2 space-y-1 max-h-64 overflow-y-auto">
                        <template x-for="(frame, i) in (serverError?.exception_trace || [])" :key="i">
                            <div class="flex items-start gap-2 px-2 py-1.5 rounded bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <span class="text-xs text-gray-400 w-4 flex-shrink-0 text-right mt-0.5"
                                      x-text="i + 1"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <code class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-[200px]"
                                              x-text="frame.file || '(internal)'"></code>
                                        <span x-show="frame.line"
                                              class="text-xs font-bold text-red-500 dark:text-red-400 flex-shrink-0"
                                              x-text="':' + frame.line"></span>
                                    </div>
                                    <div x-show="frame.class || frame.function"
                                         class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 font-mono truncate"
                                         x-text="(frame.class ? frame.class + '::' : '') + (frame.function || '')">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Queries captured before crash --}}
                <div x-show="serverError?.queries_captured > 0" class="px-4 py-3">
                    <div class="flex items-start gap-2.5 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-yellow-800 dark:text-yellow-300">
                            <strong x-text="serverError?.queries_captured"></strong>
                            quer<span x-text="serverError?.queries_captured === 1 ? 'y was' : 'ies were'"></span>
                            captured before the crash — check your query logic for the root cause.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Debug hint --}}
            <div class="flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Set <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">APP_DEBUG=true</code>
                in <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">.env</code>
                for full exception details
            </div>
        </div>

        {{-- ── RESULTS ──────────────────────────────────────────── --}}
        <div x-show="results" x-cloak class="animate-fade-in space-y-6">

            {{-- Performance Score --}}
            <div class="rounded-xl p-5 text-center" :class="scoreHelpers.getBackground(results?.score?.score)">
                <div class="text-5xl font-bold mb-1" :class="scoreHelpers.getColor(results?.score?.score)">
                    <span x-text="results?.score?.score"></span><span class="text-2xl opacity-60">/100</span>
                </div>
                <div class="text-base font-semibold mb-0.5">
                    Grade: <span x-text="results?.score?.grade"></span>
                    <span x-text="scoreHelpers.getEmoji(results?.score?.score)" class="ml-1"></span>
                </div>
                <div class="text-sm opacity-70" x-text="scoreHelpers.getDescription(results?.score?.score)"></div>

                <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                    <div class="bg-white/50 dark:bg-black/20 rounded-lg p-2">
                        <div class="font-bold" x-text="results?.score?.breakdown?.query_count ?? '—'"></div>
                        <div class="text-xs opacity-70">Query Count</div>
                    </div>
                    <div class="bg-white/50 dark:bg-black/20 rounded-lg p-2">
                        <div class="font-bold" x-text="results?.score?.breakdown?.query_time ?? '—'"></div>
                        <div class="text-xs opacity-70">Query Time</div>
                    </div>
                    <div class="bg-white/50 dark:bg-black/20 rounded-lg p-2">
                        <div class="font-bold" x-text="results?.score?.breakdown?.issues ?? '—'"></div>
                        <div class="text-xs opacity-70">Issues</div>
                    </div>
                </div>
            </div>

            {{-- Issues --}}
            <div x-show="results?.issues?.length > 0">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                    Issues Detected
                </h3>

                <div class="space-y-4 max-h-[700px] overflow-y-auto pr-1">
                    <template x-for="(issue, index) in (results?.issues ?? [])" :key="index">

                        <div class="rounded-xl overflow-hidden shadow-sm"
                             :class="severityHelpers.getWrapperClass(issue.severity)">

                            {{-- Issue Header --}}
                            <div class="px-4 py-3 border-b flex items-center justify-between gap-3"
                                 :class="severityHelpers.getHeaderClass(issue.severity)">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-xl flex-shrink-0" x-text="severityHelpers.getIcon(issue.type)"></span>
                                    <span class="font-semibold truncate" x-text="formatIssueType(issue.type)"></span>
                                </div>
                                <span class="flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase"
                                      :class="severityHelpers.getBadgeClass(issue.severity)"
                                      x-text="issue.severity"></span>
                            </div>

                            {{-- Stats Row --}}
                            <div class="px-4 py-2.5 flex flex-wrap gap-x-5 gap-y-1 text-sm border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                                <div x-show="issue.count" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Executed <strong class="text-gray-900 dark:text-white" x-text="issue.count"></strong> times
                                </div>
                                <div x-show="issue.total_time" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Total <strong class="text-gray-900 dark:text-white" x-text="issue.total_time + 'ms'"></strong>
                                </div>
                                <div x-show="issue.avg_time" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Avg <strong class="text-gray-900 dark:text-white" x-text="issue.avg_time + 'ms'"></strong>
                                </div>
                                <div x-show="issue.time" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Duration <strong class="text-gray-900 dark:text-white" x-text="issue.time + 'ms'"></strong>
                                </div>
                                <div x-show="issue.rows_examined" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                    </svg>
                                    <strong class="text-gray-900 dark:text-white" x-text="issue.rows_examined?.toLocaleString()"></strong> rows scanned
                                </div>
                            </div>

                            {{-- File Location --}}
                            <div x-show="issue.location && issue.location?.file !== 'Unknown'"
                                 class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1.5 uppercase tracking-wide">
                                            Source Location
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-700 rounded-md px-2.5 py-1.5 min-w-0">
                                                <code class="file-path-badge text-gray-700 dark:text-gray-300 truncate max-w-[260px]"
                                                      x-text="shortenPath(issue.location?.file)"
                                                      :title="issue.location?.file"></code>
                                            </div>
                                            <div x-show="issue.location?.line > 0"
                                                 class="flex items-center gap-1.5 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md px-2.5 py-1.5 flex-shrink-0">
                                                <svg class="w-3.5 h-3.5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                </svg>
                                                <span class="file-path-badge font-bold text-red-600 dark:text-red-400">
                                                    Line <span x-text="(issue.location?.file ?? 'Unknown') + ':' + (issue.location?.line ?? 0)"
                                                    ></span>
                                                </span>
                                            </div>
                                            <button @click="navigator.clipboard.writeText(issue.location?.file + ':' + issue.location?.line)"
                                                    class="flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors ml-auto"
                                                    title="Copy path">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Query --}}
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-2">Query</div>
                                <div class="bg-gray-900 dark:bg-black rounded-lg p-3 overflow-x-auto">
                                    <code class="text-xs text-gray-300 code-block" x-text="issue.query"></code>
                                </div>
                            </div>

                            {{-- Suggestion --}}
                            <div x-show="issue.suggestion" class="px-4 py-3">
                                <div class="flex gap-2.5 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <svg class="w-4 h-4 text-blue-500 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <div class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-0.5">How to fix:</div>
                                        <div class="text-sm text-blue-900 dark:text-blue-300 code-block" x-text="issue.suggestion"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- No Issues --}}
            <div x-show="results?.issues?.length === 0" class="py-10 text-center">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="text-lg font-semibold mb-1">No Issues Found!</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Your endpoint looks clean and performant.</div>
            </div>

            {{-- All Queries (collapsible) --}}
            <div x-show="results?.queries?.length > 0">
                <button @click="showQueries = !showQueries"
                        class="w-full flex items-center justify-between py-2 text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                    <span>All Queries <span class="normal-case font-normal text-gray-400">(<span x-text="results?.queries?.length"></span>)</span></span>
                    <svg class="w-4 h-4 transition-transform" :class="showQueries ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="showQueries" class="space-y-1.5 max-h-72 overflow-y-auto mt-2">
                    <template x-for="(query, index) in (results?.queries ?? [])" :key="index">

                        <div class="flex items-start gap-3 px-3 py-2.5 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <span class="text-xs font-bold text-gray-300 dark:text-gray-600 mt-0.5 w-4 flex-shrink-0 text-right"
                                  x-text="index + 1"></span>
                            <code class="flex-1 text-xs text-gray-700 dark:text-gray-300 code-block break-all" x-text="query.sql"></code>
                            <span class="text-xs font-semibold text-blue-500 dark:text-blue-400 whitespace-nowrap flex-shrink-0"
                                  x-text="query.time.toFixed(2) + 'ms'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

       {{-- ── GENERIC ERROR ────────────────────────────────────── --}}
<div x-show="error && !serverError" x-cloak class="py-10 text-center animate-fade-in">
    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </div>
    <div class="text-lg font-semibold mb-2">Request Failed</div>

    {{-- Main error --}}
    <div class="text-sm text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg px-4 py-2.5 inline-block max-w-sm break-words mb-3"
         x-text="error"></div>

    {{-- Client error message --}}
    <div x-show="clientError"
            class="mt-2 mx-auto max-w-sm rounded-xl border border-orange-200 dark:border-orange-800 overflow-hidden">
            <div class="px-4 py-2 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span class="text-xs font-semibold text-orange-700 dark:text-orange-400 uppercase tracking-wide">
                    HTTP <span x-text="clientError?.status"></span> — Error Message
                </span>
            </div>
            <div class="px-4 py-3 bg-white dark:bg-gray-800">
                <code class="text-sm text-orange-800 dark:text-orange-300 break-words"
                    x-text="clientError?.error_message"></code>
            </div>
        </div>
    </div>

        {{-- ── EMPTY STATE ──────────────────────────────────────── --}}
        <div x-show="!results && !loading && !error && !serverError" class="py-20 text-center">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="text-lg font-semibold text-gray-400 dark:text-gray-500 mb-1">No Analysis Yet</div>
            <div class="text-sm text-gray-400 dark:text-gray-500">
                Configure a request on the left and click<br>
                <strong class="text-gray-600 dark:text-gray-400">Analyze Request</strong> to begin
            </div>
        </div>

        {{-- ── LOADING ──────────────────────────────────────────── --}}
        <div x-show="loading" x-cloak class="py-20 text-center">
            <div class="relative w-16 h-16 mx-auto mb-5">
                <svg class="animate-spin w-16 h-16 text-red-100 dark:text-red-900/30" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                </svg>
                <svg class="animate-spin w-16 h-16 text-red-600 absolute inset-0" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"
                            stroke-dasharray="20 60" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="text-lg font-semibold mb-1">Analyzing...</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Running queries through all detectors</div>
        </div>
    </div>
</div>