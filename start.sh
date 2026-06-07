#!/bin/bash
set -e

MARIADB_DIR="/nix/store/a4jsa8kjdn3wlccj2wkvhxqza38rpxzf-mariadb-server-10.11.13"
DATADIR="/home/runner/mysql-data"
RUNDIR="/home/runner/mysql-run"
SOCKET="$RUNDIR/mysql.sock"
DB_PORT="3306"
DB_NAME="${DB_NAME:-velmora_db}"
DB_USER="${DB_USER:-velmora_user}"
DB_PASS="${DB_PASS:-VelmoraPass2024!}"
INIT_FILE="/tmp/mysql_velmora_init.sql"

mkdir -p "$DATADIR" "$RUNDIR"

# Kill any leftover mysqld processes
pkill -f mysqld 2>/dev/null || true
sleep 1
rm -f "$SOCKET"

# Write the init file that MariaDB will execute on startup
cat > "$INIT_FILE" << SQLEOF
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE OR REPLACE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
CREATE OR REPLACE USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQLEOF

wait_for_mysql() {
    local tries=30
    for i in $(seq 1 $tries); do
        if [ -S "$SOCKET" ] && mysql --socket="$SOCKET" -u root --skip-password -e "SELECT 1;" 2>/dev/null; then
            return 0
        fi
        sleep 1
    done
    return 1
}

# Check if MariaDB system tables need to be set up
if [ ! -d "$DATADIR/mysql" ]; then
    echo "[setup] Initializing MariaDB data directory..."

    # Start with skip-grant-tables so no auth is needed
    mysqld --no-defaults \
        --datadir="$DATADIR" \
        --basedir="$MARIADB_DIR" \
        --lc-messages-dir="$MARIADB_DIR/share/mysql" \
        --socket="$SOCKET" \
        --port="$DB_PORT" \
        --bind-address=127.0.0.1 \
        --skip-networking=0 \
        --skip-grant-tables \
        --skip-name-resolve &
    INIT_PID=$!

    echo "[setup] Waiting for MariaDB socket..."
    if wait_for_mysql; then
        echo "[setup] MariaDB ready for initialization."
    else
        echo "[setup] ERROR: MariaDB did not start in time."
        kill "$INIT_PID" 2>/dev/null || true
        exit 1
    fi

    echo "[setup] Loading system tables..."
    mysql --socket="$SOCKET" -u root --skip-password -e "CREATE DATABASE IF NOT EXISTS mysql;" 2>/dev/null || true
    mysql --socket="$SOCKET" -u root --skip-password mysql \
        < "$MARIADB_DIR/share/mysql/mysql_system_tables.sql" 2>/dev/null || true
    mysql --socket="$SOCKET" -u root --skip-password mysql \
        < "$MARIADB_DIR/share/mysql/mysql_system_tables_data.sql" 2>/dev/null || true

    echo "[setup] Creating velmora app user directly in global_priv..."
    # Insert the app user directly using INSERT (works in skip-grant-tables mode)
    mysql --socket="$SOCKET" -u root --skip-password mysql << SQLINIT 2>/dev/null || true
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
INSERT IGNORE INTO global_priv (Host, User, Priv) VALUES
  ('localhost', '$DB_USER',
   JSON_SET('{}', '$.plugin','mysql_native_password',
                  '$.authentication_string', PASSWORD('$DB_PASS'),
                  '$.access', 0)),
  ('127.0.0.1', '$DB_USER',
   JSON_SET('{}', '$.plugin','mysql_native_password',
                  '$.authentication_string', PASSWORD('$DB_PASS'),
                  '$.access', 0));
INSERT IGNORE INTO db (Host, Db, User, Select_priv, Insert_priv, Update_priv, Delete_priv,
  Create_priv, Drop_priv, Grant_priv, References_priv, Index_priv, Alter_priv,
  Create_tmp_table_priv, Lock_tables_priv, Create_view_priv, Show_view_priv,
  Create_routine_priv, Alter_routine_priv, Execute_priv, Event_priv, Trigger_priv)
  VALUES
  ('localhost', '$DB_NAME', '$DB_USER', 'Y','Y','Y','Y','Y','Y','N','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y'),
  ('127.0.0.1', '$DB_NAME', '$DB_USER', 'Y','Y','Y','Y','Y','Y','N','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y');
SQLINIT

    echo "[setup] Stopping init instance..."
    kill "$INIT_PID" 2>/dev/null || true
    wait "$INIT_PID" 2>/dev/null || true
    sleep 2
    rm -f "$SOCKET"
fi

# Start MariaDB with --init-file to ensure user exists
echo "[start] Starting MariaDB with init file..."
mysqld --no-defaults \
    --datadir="$DATADIR" \
    --basedir="$MARIADB_DIR" \
    --lc-messages-dir="$MARIADB_DIR/share/mysql" \
    --socket="$SOCKET" \
    --port="$DB_PORT" \
    --bind-address=127.0.0.1 \
    --skip-networking=0 \
    --skip-name-resolve \
    --init-file="$INIT_FILE" &
MYSQLD_PID=$!

echo "[start] Waiting for MariaDB to be ready..."
for i in $(seq 1 30); do
    if [ -S "$SOCKET" ] && mysql --socket="$SOCKET" -u "$DB_USER" --password="$DB_PASS" -e "SELECT 1;" 2>/dev/null; then
        echo "[start] MariaDB is ready. App user connected."
        break
    fi
    sleep 1
done

# Test connection and init app tables
echo "[start] Testing app database connection..."
DB_SOCKET="$SOCKET" php /home/runner/workspace/create_tables.php 2>&1 || \
    echo "[warn] create_tables.php had warnings"

# Start PHP server
echo "[start] Starting PHP built-in server on 0.0.0.0:5000..."
exec php -S 0.0.0.0:5000 -t /home/runner/workspace /home/runner/workspace/router.php
