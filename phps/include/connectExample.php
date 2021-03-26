<?php

define("host",'YOUR_HOSTNAME_HERE');
define("db", 'YOUR_DB_NAME_HERE');
define("user", 'YOUR_DB_USER_NAME_HERE');
define("pass", 'YOUR_DB_USER_PASSWORD_HERE');
define("charset", 'utf8mb4');

function dbConnect()
{
    $dsn = "mysql:host=". host . ";dbname=" . db . ";charset=" . charset;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, user, pass, $options);
}