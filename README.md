# TransitOps

TransitOps is a PHP and MySQL fleet operations website.

## Requirements

- PHP 8.1 or newer
- MySQL 5.7+ or MariaDB 10.3+
- PHP extensions: `pdo`, `pdo_mysql`, `fileinfo`, and `session`
- Apache with `mod_rewrite` and `.htaccess` enabled

## Deploying to cPanel

1. In cPanel, select PHP 8.1 or newer in **MultiPHP Manager** and enable the required PHP extensions.
2. Create a MySQL database and a database user in **MySQL Databases**. Add the user to the database with all required privileges. cPanel commonly prefixes both names with the account name.
3. Upload the project files into the domain's document root (usually `public_html`) using File Manager or SFTP. Keep the `uploads` directory and its `.htaccess` file.
4. In phpMyAdmin, select the database you created, then import `database/schema.sql`. Select the same database and import `database/seed.sql` only if you want the sample data and demo accounts.
5. Set the application settings as Apache environment variables in the document-root `.htaccess`. Add real values on the server only; do not commit them:

   ```apache
   SetEnv TRANSITOPS_ENV production
   SetEnv TRANSITOPS_DB_HOST localhost
   SetEnv TRANSITOPS_DB_NAME your_cpanelprefix_database
   SetEnv TRANSITOPS_DB_USER your_cpanelprefix_user
   SetEnv TRANSITOPS_DB_PASSWORD your_database_password
   SetEnv TRANSITOPS_BASE_URL https://your-domain.example
   SetEnv TRANSITOPS_TIMEZONE UTC
   ```

   Replace the example database names, password, and domain. If the host supplies a database hostname other than `localhost`, use the value shown by cPanel. Do not enable development mode on a public site.
6. In cPanel File Manager, make `uploads` and its `vehicles`, `drivers`, and `documents` subdirectories writable by the PHP account. Start with the usual `755` permissions; use a more permissive mode only if the host specifically requires it.
7. Open `https://your-domain.example/login`. If routing returns 404 errors, confirm that Apache allows `.htaccess` overrides and `mod_rewrite` is enabled. If the database connection fails, check the cPanel-prefixed database name/user, password, host, and privileges.

### Sample account warning

The optional `database/seed.sql` file creates public demo accounts with the password `admin123`. Import it only for a private demo, and change every seeded account password before exposing the website to the internet. Do not use the sample accounts for production data.

## Local Laragon setup

Create a local database named `transitops`, import `database/schema.sql`, then optionally import `database/seed.sql`. The app defaults to `localhost`, database `transitops`, user `root`, and an empty password outside production. To see detailed errors locally, configure `TRANSITOPS_ENV=development` in the local web-server environment.

## Validation before launch

- Confirm the site loads over HTTPS and the login page is displayed.
- Sign in with a non-demo account, verify permitted pages and actions, then sign out.
- Test creating and editing records and uploading an image/document.
- Confirm the browser's network panel has no failed local asset or route requests.
- Review the cPanel PHP error log after these checks. Keep application errors hidden from visitors.
