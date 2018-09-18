<?php

class Dailies
{

	public static function fetch($guzzler, $sql)
	{
		$time = strtotime("2007-12-05");
		$today = date('Ymd');
		while ($time < time()) {
			$date = date("Ymd", $time);
			$result = $sql->query("select count(*) count from dailies where day = '$date' and fetched = '$today'");
			$row = $result->fetchArray(SQLITE3_ASSOC);
			if ($row['count'] == 1) {
				$time += 86400;
				continue;
			}
			$p = $sql->prepare("insert or replace into dailies values (:d, :t)");
			$p->bindValue(":d", $date);
			$p->bindValue(":t", $today);
			$p->execute();
			$p->close();
			$url = "https://zkillboard.com/api/history/$date/";
			$guzzler->call($url, "Dailies::success", "Dailies::fail", ['sql' => $sql, 'date' => $date]);
			return;
		}
	}

	public static function success($guzzler, $params, $content)
	{
		$json = json_decode($content, true);
		$sql = $params['sql'];
		$sql->exec("begin");
		foreach ($json as $kill_id => $hash) {
			$p = $sql->prepare("insert or ignore into killhashes values (:k, :h, 0)");
			$p->bindValue(":k", $kill_id);
			$p->bindValue(":h", $hash);
			$p->execute();
			$p->close();
		}
		$sql->exec("commit");
		echo "  Daily: " . $params['date'] . " " . sizeof($json) . "\n";
	}

	public static function fail($guzzler, $params, $ex)
	{
		echo $ex->getCode() . " " . $ex->getMessage() . "\n";
		sleep(1);
		if ($ex->getCode() == 0) $guzzler->call($params['uri'], "Dailies::success", "Dailies::fail", $params);
	}
}
