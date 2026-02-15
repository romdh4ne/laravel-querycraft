<p align="center">
  <img src="public/logo-horizontal.svg" width="380" alt="QueryCraft Logo"/>
</p>

<p align="center">
  <strong>A Laravel performance analysis dashboard for detecting N+1 queries, slow queries, missing indexes, and duplicate queries — in real time.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-9%2B%20%7C%2010%2B%20%7C%2011%2B%20%7C%2012%2B-red?style=flat-square&logo=laravel" alt="Laravel"/>
  <img src="https://img.shields.io/badge/PHP-8.1%2B-blue?style=flat-square&logo=php" alt="PHP"/>
  <img src="https://img.shields.io/badge/license-MIT-green?style=flat-square" alt="License"/>
  <img src="https://img.shields.io/packagist/v/romdh4ne/laravel-querycraft?style=flat-square" alt="Packagist"/>
  <img src="https://img.shields.io/packagist/dt/romdh4ne/laravel-querycraft?style=flat-square" alt="Downloads"/>
</p>

---

## ✨ Features

- 🔁 **N+1 Detection** — catches repeated query patterns caused by missing eager loading
- 🐢 **Slow Query Detection** — flags queries exceeding your configured time limit
- 🗂 **Missing Index Detection** — identifies full table scans using MySQL `EXPLAIN`
- 📋 **Duplicate Query Detection** — finds identical queries (including bindings) fired multiple times
- 📍 **Source Location** — shows the exact file and line number in your app that triggered the issue
- 💯 **Performance Score** — grades your endpoint from 0–100 with a letter grade (A–F)
- 🛠 **Live Config Panel** — toggle detectors and adjust thresholds from the dashboard UI
- 🌙 **Dark Mode** — built-in dark/light mode toggle
- 🚨 **500 Error Inspector** — displays full exception details with app-only stack trace
- ⌨️ **Artisan Command** — analyze endpoints directly from your terminal with full body and header support

---

## 📦 Installation

### 1. Require the package via Composer

```bash
composer require romdh4ne/laravel-querycraft
```

### 2. Publish the config file

```bash
php artisan vendor:publish --tag=querycraft-config
```

### 3. Publish the assets (logo, favicon)

```bash
php artisan vendor:publish --tag=querycraft-assets
```

### 4. Publish the views *(optional — only if you want to customize the UI)*

```bash
php artisan vendor:publish --tag=querycraft-views
```

### 5. Clear caches

```bash
php artisan config:clear
php artisan view:clear
```

### 6. Visit the dashboard

```
http://your-app.test/querycraft
```

---

## 🖥 Web Dashboard

### Opening the dashboard

```
http://your-app.test/querycraft
```

Or with a custom route prefix set in `.env`:

```
http://your-app.test/your-custom-prefix
```

### Analyzing an endpoint

1. Enter your endpoint URL (e.g. `/api/users`)
2. Select the HTTP method (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`)
3. Optionally add custom headers (e.g. `Authorization: Bearer token`)
4. Optionally add a JSON request body for `POST`/`PUT` requests
5. Click **Analyze Request**

QueryCraft fires an internal request to your endpoint, collects all queries, runs them through all detectors, and displays the results instantly.

### Reading the results

| Element | Description |
|---|---|
| **Score card** | 0–100 performance grade with letter (A–F) and emoji indicator |
| **Query count** | Total number of queries executed by the endpoint |
| **Total time** | Combined execution time of all queries in milliseconds |
| **Issue cards** | Each problem with severity, stats, source location and fix suggestion |
| **Source Location** | Exact file path and line number in your app (vendor files filtered out) |
| **All Queries** | Collapsible list of every query fired with individual execution time |

### 500 Error Inspector

When your endpoint crashes, QueryCraft catches it and displays:

- Exception class (e.g. `ErrorException`, `QueryException`)
- Error message
- Exact file and line number in your app where it crashed
- Stack trace showing only your app files — no vendor noise
- Number of queries captured before the crash

> Set `APP_DEBUG=true` in your `.env` for full exception details.

### Client Error Display

When your endpoint returns a 4xx response (e.g. 422 validation error, 404), QueryCraft shows:

- The HTTP status code
- The error message returned by your API
- Suggestions for similar routes if 404

### Config Panel

Click the ⚙️ icon in the top-right header to open the config panel:

- Toggle each detector on/off individually
- Adjust thresholds using sliders
- Tune score weights (must total 100%)
- Click **Save** — changes are written to your `.env` immediately
- Click **Reset** to restore all defaults

---

## ⌨️ Artisan Command

Analyze endpoints directly from your terminal without opening a browser.

### Signature

```
php artisan querycraft:analyze
    {--url=         : The endpoint URL to analyze}
    {--method=GET   : HTTP method (GET, POST, PUT, PATCH, DELETE)}
    {--user=        : Authenticate as a specific user ID}
    {--show-queries : Print all executed queries in the output}
    {--body=        : JSON body as an inline string}
    {--body-file=   : Path to a JSON file to use as the request body}
    {--header=*     : Custom headers in Key:Value format (repeatable)}
```

### Examples

```bash
# Simple GET
php artisan querycraft:analyze --url=/users

# With authentication
php artisan querycraft:analyze --url=/dashboard --user=1

# POST with inline JSON body
php artisan querycraft:analyze --url=/api/posts --method=POST \
  --body='{"title":"Hello","body":"World","category_id":1}'

# POST with many fields — use --body-file to keep it clean
php artisan querycraft:analyze --url=/api/orders --method=POST \
  --body-file=./payload.json

# PUT with body and auth
php artisan querycraft:analyze --url=/api/users/1 --method=PUT --user=1 \
  --body='{"name":"John","email":"john@example.com"}'

# With custom headers
php artisan querycraft:analyze --url=/api/secret \
  --header="Authorization:Bearer your-token" \
  --header="X-Team-Id:42"

# Everything combined
php artisan querycraft:analyze --url=/api/orders --method=POST --user=1 \
  --body-file=./payload.json \
  --header="X-Source:querycraft" \
  --show-queries
```

### Sending a large body with `--body-file`

When your request has many fields, create a `payload.json` file instead of cramming everything inline:

```json
{
  "customer_id": 5,
  "shipping_address": {
    "street": "123 Main St",
    "city": "Paris",
    "zip": "75001"
  },
  "items": [
    { "product_id": 1, "qty": 2, "price": 29.99 },
    { "product_id": 3, "qty": 1, "price": 49.99 }
  ],
  "coupon": "SAVE10",
  "notes": "Leave at door"
}
```

Then run:

```bash
php artisan querycraft:analyze --url=/api/orders --method=POST \
  --body-file=./payload.json --user=1 --show-queries
```

> Use `--body` for small payloads. Use `--body-file` for large or complex payloads — it avoids shell escaping issues and is much easier to read and reuse.

### Example output

```
🔍 Analyzing: GET /users

✅ Response: 200

📊 Summary:
+----------------+----------+
| Metric         | Value    |
+----------------+----------+
| Total Queries  | 23       |
| Total Time     | 182.5 ms |
| Avg Query Time | 7.93 ms  |
| Response Status| 200      |
+----------------+----------+

⚠️  Found 2 issue(s):

🔴 Issue #1: N+1
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Severity: HIGH
Occurrences: 20
Total Time: 140ms
Location: /app/Http/Controllers/UserController.php:45

Query:
  select * from `companies` where `id` = ?

💡 Suggestion:
  Add eager loading: ->with('company')

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚡ Performance Score: 62/100 (Grade: D) 🟠
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🎯 Top Improvements:
  • Too many queries (+25 points)
  • 2 performance issues detected (+20 points)
```

> If your endpoint requires authentication, pass `--user=1` to run as a specific user. QueryCraft calls `auth()->login($user)` before firing the request.

---

## ⚙️ Configuration

After publishing, the config file is at `config/querycraft.php`. All values can be overridden via `.env`:

```env
# Enable or disable the package entirely
QUERY_DEBUGGER_ENABLED=true

# Detectors — toggle individually
QUERYCRAFT_DETECTOR_N1=true
QUERYCRAFT_DETECTOR_SLOW_QUERY=true
QUERYCRAFT_DETECTOR_MISSING_INDEX=true
QUERYCRAFT_DETECTOR_DUPLICATE_QUERY=true

# Thresholds
QUERY_DEBUGGER_N1_THRESHOLD=5        # Flag N+1 after this many repetitions (default: 5)
QUERY_DEBUGGER_SLOW_THRESHOLD=100    # Flag queries slower than this in ms (default: 100)
QUERYCRAFT_DUPLICATE_COUNT=2         # Flag duplicates after this many repeats (default: 2)

# Score weights — must total 100
QUERYCRAFT_WEIGHT_QUERY_COUNT=40
QUERYCRAFT_WEIGHT_QUERY_TIME=30
QUERYCRAFT_WEIGHT_ISSUES=30

# Dashboard route prefix (default: querycraft)
QUERYCRAFT_DASHBOARD_ROUTE=querycraft
```

### Full config reference

```php
// config/querycraft.php
return [
    'enabled' => env('QUERY_DEBUGGER_ENABLED', true),

    'detectors' => [
        'n1'             => env('QUERYCRAFT_DETECTOR_N1', true),
        'slow_query'     => env('QUERYCRAFT_DETECTOR_SLOW_QUERY', true),
        'missing_index'  => env('QUERYCRAFT_DETECTOR_MISSING_INDEX', true),
        'duplicate_query'=> env('QUERYCRAFT_DETECTOR_DUPLICATE_QUERY', true),
    ],

    'thresholds' => [
        'n1_count'        => env('QUERY_DEBUGGER_N1_THRESHOLD', 5),
        'slow_query_ms'   => env('QUERY_DEBUGGER_SLOW_THRESHOLD', 100),
        'duplicate_count' => env('QUERYCRAFT_DUPLICATE_COUNT', 2),
    ],

    'weights' => [
        'query_count' => env('QUERYCRAFT_WEIGHT_QUERY_COUNT', 40),
        'query_time'  => env('QUERYCRAFT_WEIGHT_QUERY_TIME', 30),
        'issues'      => env('QUERYCRAFT_WEIGHT_ISSUES', 30),
    ],
];
```

> **Tip:** All settings can also be changed from the dashboard ⚙️ config panel — changes are saved directly to your `.env`.

---

## 🔬 How Detectors Work

### 🔁 N+1 Detection

Normalizes every query (replaces values with `?`) and groups them by pattern. If the same pattern fires more than `n1_count` times, it's flagged. The exact file and line in your app that triggered the repeated query is shown.

**Example:**
```php
// ❌ N+1 — fires one query per user
$users = User::all();
foreach ($users as $user) {
    echo $user->company->name;
}

// ✅ Fix — one query total
$users = User::with('company')->get();
```

| Count | Severity |
|---|---|
| 5–10× | low |
| 10–20× | medium |
| 20–50× | high |
| 50×+ | critical |

---

### 🐢 Slow Query Detection

Flags any query exceeding `slow_query_ms` milliseconds (default: 100ms). Automatically suggests a fix based on the query structure.

| Time | Severity |
|---|---|
| > 200ms | low |
| > 500ms | medium |
| > 1000ms | high |
| > 1000ms | critical |

**Suggestions shown:**
- `SELECT *` → use specific columns
- `ORDER BY` without `LIMIT` → add a limit
- `LIKE` queries → consider full-text search
- `COUNT(*)` on large tables → consider caching

---

### 🗂 Missing Index Detection

Runs MySQL `EXPLAIN` on each query and flags full table scans, filesorts, and temporary table usage.

**Triggers:**
- `type = ALL` with more than 1,000 rows examined
- `Extra` contains `Using filesort`
- `Extra` contains `Using temporary`

**Example:**
```php
User::where('email', $email)->first(); // no index on email

// Fix — in a migration:
$table->index('email');
$table->index(['status', 'created_at']); // composite
```

| Rows examined | Severity |
|---|---|
| > 1,000 | low |
| > 10,000 | medium |
| > 100,000 | high |
| > 100,000 | critical |

---

### 📋 Duplicate Query Detection

Creates an `md5` fingerprint of `sql + bindings`. If the exact same query with the same parameter values runs more than `duplicate_count` times (default: 2), it's flagged.

**Example:**
```php
// ❌ Duplicate — same query + same values fired twice
$settings = Setting::all();
// ... somewhere else in the same request ...
$settings = Setting::all();

// ✅ Fix
$settings = Cache::remember('settings', 3600, fn() => Setting::all());
```

---

## 💯 Performance Score

Calculated as a weighted average across three dimensions:

| Dimension | Default Weight | Description |
|---|---|---|
| Query Count | 40% | Fewer queries = higher score |
| Query Time | 30% | Faster total time = higher score |
| Issues Found | 30% | Fewer/lower severity issues = higher score |

| Score | Grade | Status |
|---|---|---|
| 90–100 | A 🟢 | Excellent |
| 80–89 | B 🟡 | Good |
| 70–79 | C 🟡 | Acceptable |
| 60–69 | D 🟠 | Below average |
| 0–59 | F 🔴 | Critical issues |

Weights are configurable from the dashboard config panel or via `.env`.

---

## 🔒 Security

QueryCraft is intended for **local development only**. Always disable it in production:

```env
# .env.production
QUERY_DEBUGGER_ENABLED=false
```

---

## 🔄 Updating

```bash
composer update romdh4ne/laravel-querycraft
php artisan vendor:publish --tag=querycraft-views --force
php artisan vendor:publish --tag=querycraft-assets --force
php artisan config:clear
php artisan view:clear
```

---

## 🗑 Uninstalling

```bash
composer remove romdh4ne/laravel-querycraft
rm config/querycraft.php
rm -rf resources/views/vendor/querycraft
rm -rf public/vendor/querycraft
# Remove QUERYCRAFT_* and QUERY_DEBUGGER_* lines from your .env
```

---

## 🤝 Contributing

### Setup

```bash
git clone https://github.com/YOUR_USERNAME/laravel-querycraft.git
cd laravel-querycraft
composer install
```

### Local test app (separate project)

In a separate Laravel app, add to `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../laravel-querycraft",
        "options": { "symlink": true }
    }
]
```

Then:

```bash
composer require romdh4ne/laravel-querycraft:@dev
php artisan vendor:publish --tag=querycraft-config
php artisan vendor:publish --tag=querycraft-assets
php artisan config:clear
php artisan serve
```

Visit `http://localhost:8000/querycraft`. Any change you make in the package reflects instantly thanks to the symlink.


## 📄 License

QueryCraft is open-source software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

Made by [Romdh4ne](https://github.com/Romdh4ne)

If this package helps you, give it a ⭐ on GitHub!