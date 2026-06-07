---
name: PHP mysqli Unix socket connection
description: The correct PHP mysqli syntax for connecting via Unix socket.
---

# PHP mysqli Unix socket connection

## The rule
To connect PHP mysqli via Unix socket, pass `null` as the host:

```php
$dbconn = new mysqli(null, $username, $password, $dbname, null, $socket);
```

**Why:** Passing `'localhost:' . $socket` causes "Connection refused" — that syntax is not valid for mysqli. The `null` host tells mysqli to use the Unix socket path provided as the 6th argument.

**How to apply:** Always check `file_exists($socket)` before attempting socket connection, fall back to TCP otherwise.
