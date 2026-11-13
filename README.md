# Filelist

Lightweight private file manager built on [VC Framework](https://github.com/dsoos1290/vcframework) for PHP 5.3 and newer.

Filelist organizes per-user uploads into B, P and W folders, with downloads, renaming and deletion in a compact interface.

## Features

* Per-user file management with upload, download, rename and delete actions.
* Three fixed folders: **B**, **P** and **W**.
* Separate folder tables on the home page; empty folders are automatically hidden.
* Single-file uploads up to **25 MiB**, with duplicate filenames allowed without overwriting existing files.
* Private filesystem storage with random storage names and database-managed metadata.
* Owner-checked downloads served as attachments, without browser previews or server-side execution.
* User login, password changes, CSRF protection and flash messages.
* Responsive tables, inline rename forms and JavaScript deletion confirmations.
* MySQLi, optional database table prefixes, Apache URL rewriting and subdirectory support.
* No Composer dependency; PHP 5.3+ compatible.

## Requirements

* PHP 5.3+ with MySQLi and a secure random source: OpenSSL or `/dev/urandom`.
* MySQL or MariaDB.
* Apache with `mod_rewrite` and support for the supplied `.htaccess` access restrictions.
* Writable private storage and PHP upload temporary directory.
* HTTPS for deployment; a supported PHP version is recommended.

## Installation

1. Download the [latest release](https://github.com/sdaniel1335/filelist/releases/latest) or clone the repository.
2. Rename `private_html/app/config/db-sample.php` to `private_html/app/config/db.php` and configure the database, preferably using `DB_CHAR = utf8mb4`.
3. Import `install.sql` into the configured database (new installations only). If using `DB_PREFIX`, update the table names in the SQL file accordingly.
4. Include `private_html/storage/.htaccess` when uploading the application, and make the storage directory writable by PHP.
5. Point the document root to `public_html`, or use the supplied Apache rewrite rules for a subdirectory installation.
6. Open the application and sign in with `admin` / `admin`.
7. **Immediately change the default password**, then choose a file and its B, P or W destination folder.

**Security:** Change the default credentials before exposing the application to the internet. Keep `private_html` inaccessible to direct HTTP requests and use production configuration. Apache must honor the supplied storage `.htaccess` access restrictions; Nginx requires an equivalent server-side deny rule. Verify that a direct request for a stored file returns 403 or 404.

## Configuration

* Application settings: `private_html/app/config/app.php` (`APP_URL` is optional).
* Database settings: `private_html/app/config/db.php` (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_CHAR`, `DB_TIME`, `DB_PREFIX`).
* Private storage: `FILE_STORAGE_PATH`, defaulting to `private_html/storage`. A custom location outside the web document root is also supported.
* File size limit: `FILE_MAX_BYTES`, defaulting to `25 * 1024 * 1024` bytes. Adjust the upload form's limit text if changing this value.
* Authentication language: `APP_LANG`, with `hu` as the default and `en` supported for authentication messages and forms. The file-management interface is currently Hungarian.

## File storage and upload limits

Files are stored in `private_html/storage` using random, extensionless names; their original names, folder assignments and ownership are stored in MySQL. Renaming changes the download filename, including its extension, without moving the stored content. Deleting a file permanently removes its content and database entry.

Files are not encrypted at rest; server administrators can access their contents. Restrict storage permissions to the PHP user. New directories use mode `0700` and files use `0600` on systems supporting Unix permissions; configure equivalent permissions on Windows. Back up the database and file storage together.

Suggested PHP settings for the default **25 MiB** file limit:

```ini
upload_max_filesize = 25M
post_max_size = 30M
```

The web server must also permit the upload request size. Ensure sufficient temporary and permanent disk space. Uploads are sent as a single request, without chunking or automatic resume.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the release history.
