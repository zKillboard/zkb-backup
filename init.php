<?php

date_default_timezone_set('UTC');

if (version_compare(phpversion(), '7.0', '<')) die('PHP 5.5 or higher is required');

// config load
require_once 'config.php';

// vendor autoload
require 'vendor/autoload.php';

$sql->exec("create table if not exists killhashes (kill_id integer primary key, hash varchar, processed int)");
$sql->exec("create index if not exists i_processed on killhashes(processed)");
$sql->exec("create table if not exists dailies (day varchar primary key, fetched varchar)");

