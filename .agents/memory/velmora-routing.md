---
name: Velmora routing & paths
description: How router.php resolves page files and what include paths pages must use
---

## Rule
`router.php` calls `chdir($pageDir)` before `require`-ing each page's `index.php`. This means every page's relative includes resolve from its own directory.

**Why:** The PHP built-in server doesn't have an Apache-style document root for includes; chdir() normalises the working directory per-page.

**How to apply:**
- Dashboard pages: `include('../app.php')` (one level up from the page dir inside dashboard/)
- Dashboard security/accounts sub-pages: `include('../../app.php')` (two levels up)
- Control-panel pages: `include('../app.php')` (one level up)
- `dashboard/app.php` uses `require_once __DIR__ . '/../common-sections/app.php'` (absolute)
- `common-sections/control-panel-header.php` — shared nav header for all control-panel sub-pages; include as `include('../../common-sections/control-panel-header.php')` from control-panel sub-dirs
