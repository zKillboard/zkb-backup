<?php

class RedisQ 
{
	private static $inFlight = false;

	public static function listen($guzzler, $sql)
	{
		if (self::$inFlight) return;
		$guzzler->call('https://redisq.zkillboard.com/listen.php?ttw=5', "RedisQ::success", "RedisQ::fail", ['sql' => $sql]);
		self::$inFlight = true;
	}

	public static function success($guzzler, $params, $content)
	{
		$json = json_decode($content, true);
		if (!isset($json['package'])) return;

		$killmail = $json['package'];
		if ($killmail == null) return;

		$sql = $params['sql'];
		$killID = $killmail['killID'];
		$hash = $killmail['zkb']['hash'];
		$p = $sql->prepare("insert or ignore into killhashes values (:k, :h, 0)");
		$p->bindValue(":k", $killID);
		$p->bindValue(":h", $hash);
		$p->execute();
		$p->close();
		echo " RedisQ: Adding $killID $hash\n";
		self::$inFlight = false;
	}

	public static function fail($guzzler, $params, $ex)
	{
		echo $ex->getCode() . " " . $ex->getMessage() . "\n";
		sleep(1);
		self::$inFlight = false;
	}
}
