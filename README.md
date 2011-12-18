# OSTSurvey

A tool to create surveys

## Basic Goals

* The system should handle multiple users and associate surveys and votes with a logged in user.
* Users should be able to create and edit a survey via the web.
* Users should be able to vote on a survey via the web.
* Users should be able to view the results of a survey on the web.
* Users should be able to browse other users' surveys.
* Keep track of who voted on each survey and don't let a user vote for the same answer twice.

## Additional Goals

* Build a simple RESTful API

## Configuration

php.ini:

```
display_errors = On
```

.htaccess:

```
<IfModule mod_rewrite.c>
RewriteEngine On

# Send all non valid-file requests through the routing script
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?q=$1 [QSA,L]

# Not Found
ErrorDocument 404 /404.php

</IfModule>
```