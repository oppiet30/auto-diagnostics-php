# Auto Diagnostics PHP

Plain PHP + PDO + MariaDB diagnostic questionnaire. First milestone: **engine won’t crank** for conventional 12-volt starting systems. No Composer, AI API, or scan tool required.

Questions, explanations, answer transitions, and conclusions live in MariaDB. PHP replays the session answers from the root on every request. Includes Yes/No/Unknown answers, “Why are you asking?”, answer history, restart, CSRF protection, escaped output, and prepared database queries. Answers stay in the PHP session; no personal information or permanent diagnostic history is collected.

## Install on Fedora / Apache

Requires PHP 8.2+ with PDO MySQL, MariaDB, and Apache with your PHP handler configured.

```bash
sudo dnf install php php-mysqlnd mariadb-server
sudo systemctl enable --now mariadb
```

As a database administrator, create the database and a dedicated user (replace the password):

```sql
CREATE DATABASE auto_diagnostics CHARACTER SET utf8mb4;
CREATE USER 'auto_diagnostics'@'localhost' IDENTIFIED BY 'choose-a-password';
GRANT SELECT ON auto_diagnostics.* TO 'auto_diagnostics'@'localhost';
```

Import as an administrator with permission to create tables and insert seed data:

```bash
sudo mariadb auto_diagnostics < database/schema.sql
sudo mariadb auto_diagnostics < database/seed.sql
cp config/database.example.php config/database.php
```

Edit `config/database.php` with the application credentials. Both SQL files are for a fresh installation; they do not drop tables. Do not reimport them into an existing installation. The application needs only SELECT permission.

For a local development check:

```bash
php -S 127.0.0.1:8080 -t public
```

Open http://127.0.0.1:8080. For Apache, set the virtual host DocumentRoot to the repository's **public/** directory, never its root. Example (adjust the path):

```apache
<VirtualHost *:80>
    ServerName auto-diagnostics.local
    DocumentRoot /var/www/auto-diagnostics-php/public
    <Directory /var/www/auto-diagnostics-php/public>
        Require all granted
        AllowOverride None
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
```

Add your server IP and hostname to the browsing computer's hosts file. On Fedora, give the deployment the appropriate httpd SELinux file labels; when using TCP database access, enable `httpd_can_network_connect_db` if needed. Keep database.php readable only by the deployment owner and PHP service, and use HTTPS for network deployment. No URL rewriting is needed.

## Verify

With the test database configured and imported:

```bash
find app config public tests -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/engine_test.php
```

The integration test reads the real MariaDB rules, traverses every reachable answer path, checks known outcomes, and rejects invalid answers and skipped questions. GitHub Actions provisions a fresh MariaDB instance and runs these checks. In the browser, also check Why, Unknown, restart, history, and two-tab stale-form rejection.

## Knowledge and scope

The starter rules are original educational content, not imported repair-manual text or validated manufacturer procedures. Conclusions are possible causes and next checks, not guaranteed diagnoses or measured probabilities. Unknown answers stop for further observation. No live vehicle control, high-voltage work, starter bypassing, or medical diagnosis is included. Verify rules against vehicle-specific service information before practical use.

## Next milestones

- Review starter diagnostic content with a qualified automotive technician.
- Add crank-but-no-start and rough-running question sets.
- Track rule provenance, review date, and vehicle applicability.
- Add an authenticated administration interface.
- Import a separately licensed and attributed trouble-code dataset after verifying provenance.

No third-party diagnostic dataset is bundled in this milestone.
