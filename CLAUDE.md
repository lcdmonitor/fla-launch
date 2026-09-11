# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Source for flalaunch.com, the website of the Florida Launch Alliance (NAR Section 876). Plain PHP (no framework, no autoloader, no Composer/npm), served by Apache/mod_php. Local development runs under MAMP (MySQL on port 8889 by MAMP's default).

## Commands

There is no build step, package manager, linter, or automated test suite in this repo.

- Syntax-check a file: use MAMP's bundled PHP, e.g. `/Applications/MAMP/bin/php/php8.3.30/bin/php -l path/to/file.php` (there's no `php` on the system `PATH`).
- Verification is manual: run the app through MAMP's Apache and exercise the page/feature in a browser.

## Required environment variables

`_config/config.inc.php` reads all DB and SMTP credentials via `getenv()` — there is no `.env` file or fallback for secrets, so the app fails loudly (fatal `mysqli_sql_exception`, etc.) if these aren't set in the web server's environment:

- Required, no fallback: `FLA_DB_USER`, `FLA_DB_PASS`, `FLA_EMAIL_USER`, `FLA_EMAIL_PASSWORD`
- Optional overrides (have sane local-dev defaults): `FLA_DB_HOST`, `FLA_DB_NAME`, `FLA_DB_PORT`, `FLA_EMAIL_SERVER`, `FLA_EMAIL_FROM`, `FLA_EMAIL_FROM_NAME`, `FLA_EMAIL_DEBUG`

On MAMP these must be set via `SetEnv` in Apache's config (`httpd.conf` / vhost) — not in this repo's tracked `.htaccess`, and not via shell `export`, since MAMP's GUI-launched Apache doesn't inherit the shell environment. See the README's "Deployment Notes" section for the same list.

Before deploying to production, `.htaccess` has a commented-out HTTPS-redirect block that must be uncommented (also called out in the README).

## Architecture

**Page shape and routing**: every top-level `.php` page follows `require DOCUMENT_ROOT/includes/header.php` → page content → `require DOCUMENT_ROOT/includes/footer.php`. `.htaccess` strips `.php` extensions for clean URLs, and rewrites `/pages/<slug>` to `pages/contentpage.php?pageid=<slug>` for CMS-style content stored in the `Page` DB table (fetched via `GetPageContent()`).

**Central include hub**: `includes/functions.inc.php` is loaded by `header.php` on every page and is the one place that wires everything together — it loads `_config/config.inc.php` (env-based config) and the vendored `Exception.php`/`PHPMailer.php`/`SMTP.php`, and defines every DB/auth/email helper used app-wide: `GetDBConnection`, `ValidateLogin`, `CreateUser`, `ChangePassword`, `GetPageContent`, `SendEmail`, `ListRoles`, `GetIsUserLoggedIn`, `printSiteName`, `IsGoodPassword`.

**Auth**: plain PHP sessions, no framework/middleware. `login.php` validates credentials and sets `$_SESSION["UserID"|"Username"|"FullName"|"RoleID"]`. Any page requiring a logged-in session must `require includes/auth.inc.php` *before* `header.php`; its `RequireAuthentication()` redirects and exits if `$_SESSION["UserID"]` isn't set. Currently only `account/update.php` and `account/changepassword.php` do this. `RoleID` is captured at login but nothing yet reads it — there is no role-based authorization anywhere in the app.

**DB access**: raw `mysqli` with prepared statements (`mysqli_stmt_init` / `prepare` / `bind_param`) throughout — no ORM or query builder. `GetDBConnection()` opens a fresh connection per call; there's no connection reuse/pooling.

**Email**: PHPMailer (manually vendored under `includes/`, not via Composer) sending through `smtp.office365.com`. Note `SendEmail()` in `functions.inc.php` currently has `SMTPAuth = false` and the `Username`/`Password` assignments commented out (`//TODO fix hacks and make secure`) — so despite `EMAIL_USER`/`EMAIL_PASSWORD` being configured, SMTP auth isn't actually applied yet. `includes/OAuth.php` and `includes/POP3.php` are unused parts of the vendored PHPMailer bundle.

**Content/BBCode**: `includes/bbcode.inc.php` pulls in the vendored `includes/jBBCode-1.3.0` library, used by `pages/contentpage.php` to render `Page.Content` from the DB.

**Frontend**: Bootstrap 5 and Bootstrap Icons loaded from jsDelivr CDN in `includes/header.php` — no bundler or npm packages. The one piece of hand-rolled JS is `js/site.js`'s `AJAXform`, used by the login modal (`includes/header.php`) to POST the form via `XMLHttpRequest` instead of a normal submit.

## Database and Migrations

**Database Migrations and Scripts**: Mysql database, migrations packages need to be created for the following Schema using liquibase, ask clarifying questions and be sure to reiterate migrations commands, and prompt me before executing any migrations, migration packages should be stored migrations/ under the project root

## Database Schema

### Tables

**Page**
Columns:
  `PageID` int(11) Primary Key Auto Increment
  `Title` varchar(255) NOT NULL,
  `PageKey` varchar(255) NOT NULL,
  `Summary` varchar(255) NOT NULL,
  `Content` longtext NOT NULL,
  `MemberOnly` tinyint(4) NOT NULL DEFAULT '0'


 **User**
Columns:
  UserID int, Primary Key Auto Increment, 
  Username varchar(255), 
  FullName nvarchar(255), 
  RoleID int default 0, 
  PasswordHash varchar(255)