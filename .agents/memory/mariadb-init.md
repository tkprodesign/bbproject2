---
name: MariaDB init on Replit
description: How to initialize and run MariaDB 10.11 in a Replit sandbox where mysql_install_db and piped stdin to subprocesses are blocked.
---

# MariaDB initialization on Replit

## The rule
Use a two-phase startup: (1) run mysqld with `--skip-grant-tables` to init data dir + load system tables, (2) restart with `--init-file` to create app users with password auth.

**Why:** `mysql_install_db` crashes due to sandbox `/proc` restrictions. Piped stdin to `mysqld --bootstrap` also fails. The only working path is:
1. Start mysqld with `--skip-grant-tables` (background process in the workflow script)
2. Connect via socket with `-u root --skip-password` to run system table SQL and create app user via direct INSERT into `global_priv` and `db` tables
3. Shut down, then restart with `--init-file` to run `CREATE OR REPLACE USER` and `GRANT` statements properly

**How to apply:**
- System tables: load `$MARIADB_DIR/share/mysql/mysql_system_tables.sql` and `mysql_system_tables_data.sql` via socket
- App user insert in `global_priv` uses JSON format: `{"plugin":"mysql_native_password","authentication_string":PASSWORD(...),"access":0}`
- Also insert into `db` table for per-database privileges
- On restart, `--init-file` runs `CREATE OR REPLACE USER ... IDENTIFIED BY` and `GRANT ALL PRIVILEGES` then `FLUSH PRIVILEGES`
- MariaDB 10.4+ uses `global_priv` table (not `user` table) for auth; root defaults to `unix_socket` auth
- Socket path: `/home/runner/mysql-run/mysql.sock`
- MariaDB binary path: `/nix/store/a4jsa8kjdn3wlccj2wkvhxqza38rpxzf-mariadb-server-10.11.13`
