<?php

// Base
$baseFile = __FILE__;
$baseDir = dirname($baseFile).'/';
chdir($baseDir);

$sql = new SQLite3("${baseDir}/data/db.sqlite");
