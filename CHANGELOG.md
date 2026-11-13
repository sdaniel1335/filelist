# Changelog

All notable changes to this project will be documented in this file.

## [0.1.0] - 2026-11-13

### Added

- Initial Filelist application built on [VC Framework](https://github.com/dsoos1290/vcframework) with PHP 5.3+ compatibility.
- Per-user file uploads, downloads, renaming and deletion.
- Fixed B, P and W folders with separate home-page tables and automatic hiding of empty folders.
- Single-file uploads up to 25 MiB, allowing duplicate filenames without overwriting stored content.
- Private storage in `private_html/storage`, random storage names and MySQL-backed file metadata.
- Apache access restrictions for stored files and support for a custom storage path outside the web document root.
- Owner-checked attachment downloads and filename validation.
- User authentication, password changes, CSRF protection and flash messages.
- Responsive tables, inline rename forms and deletion confirmations.
- MySQLi database support, optional table prefixes, Apache URL rewriting and subdirectory installation support.
- Application security and view checks without a database dependency.
- No Composer dependency.
