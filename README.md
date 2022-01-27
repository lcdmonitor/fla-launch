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
0. General - Email Capability
1. User Management - Member Registration
2. User Management - Role Management
3. User Management - Role Enum and Admin Auth
4. Account Management - Update Account (Name, Email, etc)
5. Account Management - Reset Password
6. Account Managmeent - Update Profile
7. Security - # of Failed Login attempts
8. Content Management - Create Dynamic Page Content with BBCode and CSS Support (Need to find WYSIWYG BBCode Editor)
9. Content Management - Role Based Content (Public vs Members Only)
10. Content Managment - Dynamic News Content
11. Content Mangement - Dynamic Front Page Content
12. Security - Role Based Authorization
13. Gallery - Upload
14. Gallery - Manage
15. Gallery - Display

