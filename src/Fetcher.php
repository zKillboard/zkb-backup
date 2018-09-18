<?php

class Fetcher
{
	public static function doFetches($guzzler, $sql, $baseDir)
	{
		$result = $sql->query("select * from killhashes where processed = 0 limit 10");
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$kill_id = $row['kill_id'];
			$hash = $row['hash'];
			$url = "https://esi.evetech.net/latest/killmails/$kill_id/$hash/";
			$sql->exec("update killhashes set processed = -1 where kill_id = $kill_id");
			$guzzler->call($url, "Fetcher::success", "Fetcher::fail", ['row' => $row, 'baseDir' => $baseDir, 'sql' => $sql]);
		}
	}

	public static function fail($guzzler, $params, $ex)
	{
		echo $ex->getCode() . " " . $ex->getMessage() . "\n";
		sleep(1);
		if ($ex->getCode() == 0) $guzzler->call($params['uri'], "Fetcher::success", "Fetcher::fail", $params);
	}

	public static function success($guzzler, $params, $content)
	{
		if ($content == "") return;

		$kill_id = $params['row']['kill_id'];
		$dbName = "km" . str_pad(floor($kill_id / 1000), 6, "0", STR_PAD_LEFT);
		$db = self::openDb($params['baseDir'], $dbName);
		$p = $db->prepare("insert or ignore into killmails values (:k, :b)");
		$p->bindValue(":k", $kill_id);
		$p->bindValue(":b", $content);
		$p->execute();
		$p->close();
		$db->close();
		$params['sql']->exec("update killhashes set processed = 1 where kill_id = $kill_id");
		echo "Fetched: $kill_id complete\n";
	}

	protected static function openDb($baseDir, $dbName)
	{
		$db = new SQLite3($baseDir . "/data/$dbName.sqlite");
		$db->exec("create table if not exists killmails (kill_id integer primary key, mail blob)");
		return $db;
	}
}
