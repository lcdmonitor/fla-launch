<?php
$SITE_NAME="Florida Launch Alliance";

$DB_HOST=getenv('FLA_DB_HOST') ?: "localhost";
$DB_NAME=getenv('FLA_DB_NAME') ?: "web";
$DB_PORT=getenv('FLA_DB_PORT') ?: "8889";
$DB_USER=getenv('FLA_DB_USER');
$DB_PASS=getenv('FLA_DB_PASS');

$EMAIL_SERVER=getenv('FLA_EMAIL_SERVER') ?: "smtp.office365.com";
$EMAIL_USER=getenv('FLA_EMAIL_USER');
$EMAIL_PASSWORD=getenv('FLA_EMAIL_PASSWORD');
$EMAIL_FROM=getenv('FLA_EMAIL_FROM') ?: "support@flalaunch.com";
$EMAIL_FROM_NAME=getenv('FLA_EMAIL_FROM_NAME') ?: "Florida Launch Alliance Support";
$EMAIL_DEBUG=filter_var(getenv('FLA_EMAIL_DEBUG'), FILTER_VALIDATE_BOOLEAN);
?>
