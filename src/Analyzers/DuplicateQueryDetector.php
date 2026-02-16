<?php

namespace Romdh4ne\QueryCraft\Analyzers;

class DuplicateQueryDetector
{
    protected $queries;
    protected $threshold;

    protected array $skipPaths = [
        '/vendor/',
        '/vendor/laravel/',
        'Illuminate/',
        'Sanctum/',
        'QueryCraft/',
        'QueryCollector',
    ];

    public function __construct(array $queries, int $threshold = 2)
    {
        $this->queries = $queries;
        $this->threshold = $threshold;
    }

    /**
     * Detect duplicate queries
     */
    public function detect(): array
    {
        $issues = [];
        $queryMap = [];

        foreach ($this->queries as $index => $query) {
            // Create fingerprint with bindings
            $fingerprint = md5($query['sql'] . json_encode($query['bindings']));

            if (!isset($queryMap[$fingerprint])) {
                $queryMap[$fingerprint] = [];
            }

            $queryMap[$fingerprint][] = [
                'index' => $index,
                'query' => $query,
            ];
        }

        // Find duplicates (identical query + bindings)
        foreach ($queryMap as $fingerprint => $executions) {
            if (count($executions) >= $this->threshold) {
                $totalTime = array_sum(array_column(array_column($executions, 'query'), 'time'));

                $issues[] = [
                    'type' => 'duplicate_query',
                    'severity' => 'medium',
                    'query' => $executions[0]['query']['sql'],
                    'bindings' => $executions[0]['query']['bindings'],
                    'count' => count($executions),
                    'total_time' => round($totalTime, 2),
                    'location' => $this->findSourceLocation($executions[0]['query']['backtrace'] ?? []),
                    'suggestion' => $this->generateSuggestion(
                        $executions[0]['query']['sql'],
                        count($executions)
                    ),
                ];
            }
        }

        return $issues;
    }

    protected function generateSuggestion(string $sql, int $count): string
    {
        $sql = strtolower($sql);

        // Settings / config tables — almost never change
        if (preg_match('/from\s+`?(settings|configurations?|configs?|options?)`?/i', $sql)) {
            return "This looks like a settings query. Cache it permanently:\n"
                . "Cache::rememberForever('settings', fn() => Setting::all())";
        }

        // Lookup / reference tables (roles, permissions, categories)
        if (preg_match('/from\s+`?(roles?|permissions?|categories|types?|statuses?)`?/i', $sql)) {
            return "Reference data rarely changes. Cache it for a long time:\n"
                . "Cache::remember('key', now()->addDay(), fn() => ...)";
        }

        // Auth / user queries — fired on every middleware check
        if (preg_match('/from\s+`?users?`?\s+where.*id\s*=/i', $sql)) {
            return "This looks like an auth query fired by middleware.\n"
                . "Laravel caches the authenticated user per request automatically via Auth::user().\n"
                . "Make sure you're not calling User::find(\$id) manually in multiple places.";
        }

        // COUNT queries — expensive on large tables
        if (str_contains($sql, 'count(')) {
            return "COUNT queries are expensive. Cache the result:\n"
                . "Cache::remember('count_key', 60, fn() => Model::count())";
        }

        // Queries with no WHERE — full table reads
        if (!str_contains($sql, 'where')) {
            return "This query reads the full table every time.\n"
                . "Cache it: Cache::remember('key', 300, fn() => Model::all())\n"
                . "Or check if you can scope it with a WHERE clause.";
        }

        // Queries with WHERE on a specific ID — single record lookup
        if (preg_match('/where.*`?id`?\s*=\s*\?/i', $sql)) {
            return "Same record is being loaded multiple times.\n"
                . "Use find() once and pass the model around instead of re-querying.\n"
                . "Or use Laravel's remember() pattern for expensive lookups.";
        }

        // Default
        return "This query runs {$count}x with identical parameters. Cache it:\n"
            . "Cache::remember('unique_key', 300, fn() => ...)";
    }

    protected function findSourceLocation(array $backtrace): array
    {
        $basePath = base_path();

        foreach ($backtrace as $frame) {
            $file = $frame['file'] ?? '';
            $line = $frame['line'] ?? 0;

            if (empty($file)) {
                continue;
            }

            // Skip every vendor / framework / package frame
            if ($this->shouldSkip($file)) {
                continue;
            }

            // Relativise path so it's readable
            $relativePath = str_replace($basePath, '', $file);

            return [
                'file' => $relativePath,
                'line' => $line,
            ];
        }

        // Second pass: if nothing found, look for any frame inside
        // the project root (i.e., NOT in vendor/)
        foreach ($backtrace as $frame) {
            $file = $frame['file'] ?? '';
            if (
                !empty($file)
                && str_starts_with($file, $basePath)
                && !str_contains($file, '/vendor/')
            ) {
                return [
                    'file' => str_replace($basePath, '', $file),
                    'line' => $frame['line'] ?? 0,
                ];
            }
        }

        return ['file' => 'Unknown', 'line' => 0];
    }

    /**
     * Returns true if this file should be skipped (framework / vendor).
     */
    protected function shouldSkip(string $file): bool
    {
        foreach ($this->skipPaths as $skip) {
            if (str_contains($file, $skip)) {
                return true;
            }
        }
        return false;
    }
}