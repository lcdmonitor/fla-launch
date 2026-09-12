# fla-launch



## Table of Contents
1. Overview
2. Deployment Notes && Must Do Steps
3. TODO List


## Overview
**Source for flalaunch.com website of Florida Launch Alliance, NAR Section 876**

## Deployment Notes && Must Do Steps

Set these environment variables wherever the app runs (your MAMP setup locally, and production): FLA_DB_USER, FLA_DB_PASS, FLA_EMAIL_USER, FLA_EMAIL_PASSWORD (required, no fallback — app will fail without them), plus optional overrides FLA_DB_HOST, FLA_DB_NAME, FLA_DB_PORT, FLA_EMAIL_SERVER, FLA_EMAIL_FROM, FLA_EMAIL_FROM_NAME, FLA_EMAIL_DEBUG.

### .htaccess

Before deploying to production environment with SSL you must uncomment the following lines:

`#redirect to https, uncomment next 3 lines for production`

`#RewriteCond %{SERVER_PORT} 80`

`#RewriteCond %{HTTP_HOST} ^(www\.)?flalaunch\.com`

`#RewriteRule ^(.*)$ https://www.flalaunch.com/$1 [R,L`

## TODO List
1. User Management - Member Registration
4. Account Management - Update Account (Name, Email, etc)
6. Account Management - Reset Password / Forgot Password
7. Account Managmeent - Update Profile
12. Content Managment - Dynamic News Content
13. Content Mangement - Dynamic Front Page Content
15. General - CSS Cleanup


Run Migrations:
Preview

liquibase \
  --classpath="$HOME/.liquibase/drivers/mysql-connector-j.jar" \
  --changelog-file=migrations/changelog-master.sql \
  --url="jdbc:mysql://${FLA_DB_HOST:-localhost}:${FLA_DB_PORT:-8889}/${FLA_DB_NAME:-web}" \
  --username="$FLA_DB_USER" \
  --password="$FLA_DB_PASS" \
  updateSQL


Actual

liquibase \
  --classpath="$HOME/.liquibase/drivers/mysql-connector-j.jar" \
  --changelog-file=migrations/changelog-master.sql \
  --url="jdbc:mysql://${FLA_DB_HOST:-localhost}:${FLA_DB_PORT:-8889}/${FLA_DB_NAME:-web}" \
  --username="$FLA_DB_USER" \
  --password="$FLA_DB_PASS" \
  update
