# Deployment Guide: BSU Scholarship System (Hostinger)

This guide details the steps to deploy the BSU Scholarship Laravel application to a shared hosting environment like Hostinger.

## 1. Local Preparation

Before uploading your project, you need to prepare the codebase.

### Build Assets
Since shared hosting typically doesn't run Node.js/Vite, you must build your frontend assets locally.

```bash
npm run build
```

This will generate the `public/build` directory containing your compiled CSS and JS.

### Clean & Zip
1.  Remove the `node_modules` folder (do not upload this; it's huge and unnecessary).
2.  Remove the `vendor` folder (it's better to install dependencies on the server, or upload it if you can't run composer on the server - **Recommended for beginners: Upload `vendor` if checked, but best practice is to run composer install on server**).
    *   *Note: If you have SSH access (Hostinger Premium/Business plans), do NOT upload `vendor`. If you don't use SSH, you MUST upload the `vendor` folder (run `composer install --optimize-autoloader --no-dev` locally first).*
3.  Zip the entire project folder.

## 2. Database Setup (Hostinger)

1.  Log in to your Hostinger hPanel.
2.  Go to **Databases** -> **Management**.
3.  Create a new MySQL Database.
    *   **Database Name**: (e.g., `u123456789_bsu_db`)
    *   **Username**: (e.g., `u123456789_admin`)
    *   **Password**: (Make sure to save this)
4.  Enter phpMyAdmin and **Import** your local database dump (export your local database to a `.sql` file and import it here).

## 3. Uploading Files

1.  Go to **File Manager** in hPanel.
2.  Navigate to `public_html`.
3.  **Ideally**, for security, you should place your application files *outside* `public_html`.
    *   Create a folder named `bsu_app` at the same level as `public_html` (go up one level).
    *   Upload and Extract your zip file into `bsu_app`.
4.  **Public Folder**:
    *   Move the *contents* of `bsu_app/public` into `public_html`.
    *   (Or simpler: keep everything in `public_html` but protect dotfiles. The "Folder Structure" depends on your preference. **Standard Secure Method Below**):

### Recommended Folder Structure for Shared Hosting
*   `/home/u123456789/domains/yourdomain.com/bsu_app` (Contains app, bootstrap, config, vendor, etc.)
*   `/home/u123456789/domains/yourdomain.com/public_html` (Contains contents of your `public` folder: index.php, build, images)

### modifying index.php
If you separated the core files from public files (as recommended):
Edit `public_html/index.php`:

```php
// Change these lines to point to your bsu_app folder
require __DIR__.'/../bsu_app/vendor/autoload.php';
$app = require __DIR__.'/../bsu_app/bootstrap/app.php';
```

## 4. Environment Configuration

1.  In `bsu_app` (or wherever your root is), rename `.env.example` to `.env`.
2.  Edit `.env` with your production details:

```env
APP_NAME="BSU Scholarship"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE (Copy from local .env)
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_bsu_db
DB_USERNAME=u123456789_admin
DB_PASSWORD=your_password
```

## 5. Storage Linking

Images uploaded to `storage/app/public` are not accessible unless linked.
In Hostinger (via SSH or Cron Job if SSH is unavailable):

**Via SSH (Terminal):**
```bash
cd bsu_app
php artisan storage:link
```
*Note: This creates a symlink from `public/storage` to `storage/app/public`.*

**If you moved the public folder contents to `public_html`:**
You might need to manually create the symlink or adjust the command.
A common trick without SSH is to create a PHP route to run the command:
```php
Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return 'Storage Linked';
});
```
Visit `your-domain.com/link-storage` once, then remove the route.

## 6. Permissions

Ensure the `storage` and `bootstrap/cache` directories are writable.
*   Right-click folders in File Manager -> Permissions -> Set to `775` or `755`.

## 7. Mail Configuration (SMTP)

Update the Mail settings in `.env` to send emails (using Hostinger email or Gmail SMTP).

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Common Issues & Fixes

*   **Vite Manifest Not Found**: Ensure you uploaded the `public/build` folder.
*   **500 Server Error**: Check `storage/logs/laravel.log`. Usually a permission issue or wrong database credentials.
*   **CSS Not Loading**: Check your `APP_URL` in `.env`.
