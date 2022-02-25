# fla-launch



## Table of Contents
1. Overview
2. Deployment Notes && Must Do Steps
3. TODO List


## Overview
**Source for flalaunch.com website of Florida Launch Alliance, NAR Section 876**

## Deployment Notes && Must Do Steps

### .htaccess

Before deploying to production environment with SSL you must uncomment the following lines:

`#redirect to https, uncomment next 3 lines for production`

`#RewriteCond %{SERVER_PORT} 80`

`#RewriteCond %{HTTP_HOST} ^(www\.)?flalaunch\.com`

`#RewriteRule ^(.*)$ https://www.flalaunch.com/$1 [R,L`

## TODO List
0. ~~General - Email Capability~~
1. User Management - Member Registration
2. User Management - Role Management
3. User Management - Role Enum and Admin Auth
4. Account Management - Update Account (Name, Email, etc)
4. ~~Account Management - Change Password~~
6. Account Management - Reset Password / Forgot Password
7. Account Managmeent - Update Profile
8. Security - # of Failed Login attempts
9. ~~Content Management - Disply Dynamic Page Conent~~
10. Content Management - Create/Edit Dynamic Page Content with BBCode and CSS Support (Need to find WYSIWYG BBCode Editor)
11. Content Management - Role Based Content (Public vs Members Only)
12. Content Managment - Dynamic News Content
13. Content Mangement - Dynamic Front Page Content
14. Security - Role Based Authorization
15. General - CSS Cleanup
16. Gallery - Upload
17. Gallery - Manage
18. Gallery - Display
19. Email Infrastructure Configuration - Removed Hardcoded Values and add to config.inc.php

