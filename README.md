# TournoisLMC

REST API for managing esports tournaments, built with PHP and MySQL.
Handles tournaments, teams, rounds, matches and user authentication via JWT.

---

## Requirements

| Tool | Version |
|------|---------|
| WAMP / Apache | 2.4+ |
| PHP | 7.4+ |
| MySQL | 5.7+ |
| Apache module | `mod_rewrite` **must be enabled** |
| curl | Windows 10 v1803+ includes `curl.exe` in System32 |

---

## Installation

### 1. Clone / copy the project

Place the project folder inside `C:\wamp64\www\`:
```
C:\wamp64\www\Aux-Claviers-Citoyens\
```

### 2. Enable mod_rewrite in WAMP

> Without this, **all API routes return 404**.

- Click the WAMP tray icon → `Apache` → `Apache Modules` → check **mod_rewrite**
- Restart Apache

### 3. Enable AllowOverride in Apache

Open `C:\wamp64\bin\apache\apache2.X.X\conf\httpd.conf`
Find the block `<Directory "c:/wamp64/www">` and set:

```apache
AllowOverride All
```

Restart Apache.

### 4. Create the database

1. Open **phpMyAdmin** → create a database named `db`
2. Import `API/db_fixed.sql`

### 5. Configure the connection

Edit `API/config.php`:

```php
'db' => [
    'host'    => '127.0.0.1',
    'name'    => 'db',       // database name
    'user'    => 'root',     // your MySQL user
    'pass'    => '',         // your MySQL password
    'charset' => 'utf8',
],
```

### 6. Debug Tools

## `test_api.php` — Browser interface

```
http://localhost/Aux-Claviers-Citoyens/API/test_api.php
```

A simple web interface to test the API from your browser without any external tool.

**Features:**
- Login / Register form — stores the JWT token in the PHP session automatically
- Endpoint selector with preset routes (GET, POST, PUT, DELETE)
- JSON body editor
- Displays HTTP response code (green = 2xx, red = error) and formatted JSON response

**How to use:**
1. Open the page in your browser
2. Log in with an existing account (e.g. `toto@net.fr` / `toto`)
3. Select an endpoint from the dropdown
4. Fill in the JSON body if needed (pre-filled for common routes)
5. Click **Envoyer** — the result appears below

---

### `test_curl2.bat` — Automated curl test suite

A Windows batch script that runs a full CRUD test suite against the API using `curl.exe` and saves the results to a timestamped log file.

**Requirements:** `curl.exe` in PATH (included in Windows 10 v1803+)

**How to run:**
1. Double-click `API/test_curl2.bat`
   or run from a terminal:
   ```
   cd C:\wamp64\www\Aux-Claviers-Citoyens\API
   test_curl2.bat
   ```
2. When prompted, enter the API base URL or press **Enter** for the default:
   ```
   URL : http://localhost/Aux-Claviers-Citoyens/API
   ```
3. The script checks server connectivity before starting
4. Tests run automatically — results are shown in the console and saved to a log file

**Log file:** `API/log2_YYYY-MM-DD_HH-mm-ss.txt`

**Output format:**
```
[OK]     [CREATE] POST /tournaments (HTTP 201)
[ERREUR] [READ]   GET  /tournaments/99 (HTTP 404)
         HTTP 404 : Ressource introuvable - ID inexistant ou route incorrecte
         error=tournament not found
         Corps: {"error":"tournament not found"}
         Conseil : L'ID utilise (99 / 0 / 0 / 0) n'existe peut-etre pas en base.
[AVERT]  TEAM_ID = 0 - CREATE precedent echoue, test risque d'echouer
```

**Tests covered (in order):**

| File | Operations |
|------|-----------|
| `token.php` / `register.php` / `me.php` | Register, Login, GET /auth/me |
| `rest_user.php` | CREATE, READ ×2, UPDATE ×2, DELETE |
| `rest_tournament.php` | CREATE, READ ×2, UPDATE, DELETE |
| `rest_team.php` | CREATE ×2, READ ×2, UPDATE, DELETE ×2 |
| `rest_round.php` | CREATE, READ ×3, UPDATE, DELETE |
| `rest_match.php` | CREATE, READ ×3, UPDATE (score), DELETE |

All created resources are automatically cleaned up at the end of the run.

---

## Common Issues

| Error | Cause | Fix |
|-------|-------|-----|
| All routes return 404 | `mod_rewrite` not enabled | Enable it in WAMP → Apache Modules |
| All routes return 403 | `AllowOverride None` in httpd.conf | Set `AllowOverride All` and restart Apache |
| `SQLSTATE[HY000]` on insert | MySQL strict mode + missing NOT NULL field | Re-import `db_fixed.sql` (dates are now nullable) |
| `SQLSTATE[23000]` FK fails | Inserting with a foreign key referencing a non-existent row | Inject users before tournaments (`test_data.php`) |
| `invalid_grant` on login | User does not exist in the database | Register first or inject demo data via `test_data.php` |
| `curl.exe` not found | curl not in PATH | Windows 10 v1803+: already in `C:\Windows\System32\` |
| Token always invalid | JWT secret mismatch between machines | Make sure `config.php` has the same `jwt_secret` on all machines |
