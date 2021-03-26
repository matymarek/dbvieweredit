<?php

define("host",'localhost');
define("db", 'c16a2018marema');
define("user", 'c16a2018marema');
define("pass", 'bvuSAH#3');
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