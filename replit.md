# Velmora Bank

A full-featured banking web application built with PHP and MariaDB. Includes a public marketing website, customer dashboard, and administrative control panel.

## Architecture

- **Backend:** PHP 8.2 (procedural with mysqli)
- **Database:** MariaDB 10.11 (running locally via socket)
- **Frontend:** Vanilla HTML/CSS/JS (no build system)
- **Libraries:** PHPMailer (bundled), Swiper (CDN), Bootstrap Icons (CDN)

## Startup

The app starts via `start.sh` which:
1. Initializes the MariaDB data directory on first run
2. Creates the `velmora_db` database and `velmora_user` account
3. Runs `create_tables.php` to set up application tables
4. Starts the PHP built-in server on `0.0.0.0:5000`

## Key Files

- `start.sh` — Main startup script (MariaDB + PHP server)
- `router.php` — PHP built-in server router (handles clean URLs)
- `create_tables.php` — Database schema bootstrapper
- `common-sections/app.php` — Shared PHP bootstrap (session, DB, auth)
- `apps/global.php` — Dashboard/admin shared bootstrap
- `dashboard/` — Customer-facing account management
- `control-panel/` — Admin panel (KYC, transactions, user management)

## Database

- Socket: `/home/runner/mysql-run/mysql.sock`
- Database: `velmora_db`
- User: `velmora_user` (password in `DB_PASS` env var)

## Environment Variables

| Variable | Purpose |
|---|---|
| `DB_HOST` | MariaDB host (default: 127.0.0.1) |
| `DB_PORT` | MariaDB port (default: 3306) |
| `DB_NAME` | Database name (default: velmora_db) |
| `DB_USER` | Database user (default: velmora_user) |
| `DB_PASS` | Database password |
| `DB_SOCKET` | Unix socket path |

## User Preferences

- Keep session_start() calls protected with `if (session_status() === PHP_SESSION_NONE)`
- Database connections should prefer Unix socket over TCP when socket file exists
