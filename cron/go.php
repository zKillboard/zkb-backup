<?php

require_once "../init.php";

$guzzler = new \cvweiss\Guzzler();
$minute = date('Hi');
while ($minute == date('Hi')) {
	Fetcher::doFetches($guzzler, $sql, $baseDir);
	RedisQ::listen($guzzler, $sql);
	Dailies::fetch($guzzler, $sql);
	$guzzler->tick();
	usleep(1000);
}
$guzzler->finish();
