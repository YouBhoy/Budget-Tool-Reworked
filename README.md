## BudgetFlix (PHP/MySQL) — InfinityFree Compatible

Requirements satisfied:
- PHP + MySQL (MariaDB) only; no external services
- No email, cron, or background processes
- Manual backup via web UI
- Linux-friendly relative paths
- Lightweight assets

### Project Structure
```
config/           # app + DB config
inc/              # session, csrf, auth, layout
assets/css, js    # styles and scripts
index.php         # landing
login.php         # auth
register.php      # auth
logout.php        # session end
dashboard.php     # analytics + search
help.php          # support info
backup.php        # manual SQL export
database.sql      # schema + seed
.htaccess         # basic routing/config
```

### Local Setup (XAMPP)
1. Copy files into htdocs/Budget-Tool-Reworked
2. Create DB in phpMyAdmin (e.g., `budgetflix`).
3. Import `database.sql`.
4. Update `config/config.php` DB settings if needed.
5. Visit `http://localhost/Budget-Tool-Reworked/`.

### InfinityFree Deployment
1. Create an account and a free hosting site.
2. In the control panel, create a MySQL database. Note the host, name, user, password (host is not `localhost`).
3. Upload this project via File Manager to `htdocs/`.
4. Edit `config/config.php` to set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
5. Open phpMyAdmin from the InfinityFree panel and import `database.sql`.
6. Visit your domain. Register a new account (no email required).

Notes:
- SSL: enable Free SSL from client area; site uses HTTPS cookies if available.
- `.htaccess` avoids unsupported directives and blocks `config/` and `inc/` from web.
- No email or cron used. All actions are manual.

### Manual Backup
- Go to `Backup` page while logged in and click "Download Backup". This exports your user rows from `transactions`, `goals`, and `recurring` as SQL INSERTs.
- To restore: import the SQL into a database via phpMyAdmin.

### Next Steps
- Add forms to create/edit transactions, goals, and recurring items.
- Add family mode: support `assigned_to` values and filters.
- Add accessibility testing pass.


