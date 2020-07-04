<?php
if(!defined('INITIALIZED'))
	exit;

class Website
{
	public static $serverConfig;
	public static $websiteConfig;
	public static $vocations;
	public static $groups;
	public static $SQL;
	private static $passwordsEncryption = 'sha1';

	public static function setDatabaseDriver($value)
	{
		self::$SQL = null;

		switch($value)
		{
			case Database::DB_MYSQL:
				self::$SQL = new Database_MySQL();
				break;
		}
	}

	public static function getDBHandle()
	{
		if(isset(self::$SQL))
			return self::$SQL;
		else
			throw new RuntimeException('#C-9 Database driver not set.');
	}	

	public static function loadWebsiteConfig()
	{
		self::$websiteConfig = new ConfigPHP();
		global $config;
		self::$websiteConfig->setConfig($config['site']);
	}

	public static function getWebsiteConfig()
	{
		if(!isset(self::$websiteConfig))
			self::loadWebsiteConfig();

		return self::$websiteConfig;
	}

	public static function loadServerConfig()
	{
		self::$serverConfig = new ConfigPHP();
		global $config;
		self::$serverConfig->setConfig($config['server']);
	}

	public static function getServerConfig()
	{
		if(!isset(self::$serverConfig))
			self::loadServerConfig();

		return self::$serverConfig;
	}

	public static function getFileContents($path)
	{
		$file = file_get_contents($path);

		if($file === false)
			throw new RuntimeException('Cannot read from file: <b>' . htmlspecialchars($path) . '</b>');

		return $file;
	}

	public static function putFileContents($path, $data, $append = false)
	{
		if($append)
			$status = file_put_contents($path, $data, FILE_APPEND);
		else
			$status = file_put_contents($path, $data);

		if($status === false)
            throw new RuntimeException('Cannot write to: <b>' . htmlspecialchars($path) . '</b>');

		return $status;
	}

	public static function deleteFile($path)
	{
		unlink($path);
	}

	public static function fileExists($path)
	{
		return file_exists($path);
	}

	public static function encryptPassword($password, $account = null)
	{
		return hash(self::$passwordsEncryption, $password);
	}

	public static function loadVocations()
	{
		$path = self::getWebsiteConfig()->getValue('serverPath');
		self::$vocations = new Vocations($path . 'data/XML/vocations.xml');
	}

	public static function getVocations()
	{
		if(!isset(self::$vocations))
			self::loadVocations();

		return self::$vocations;
	}

	public static function getVocationName($id)
	{
		if(!isset(self::$vocations))
			self::loadVocations();

		return self::$vocations->getVocationName($id);
	}

	public static function loadGroups()
	{
		$path = self::getWebsiteConfig()->getValue('serverPath');
		self::$groups = new Groups($path . 'data/XML/groups.xml');
	}

	public static function getGroups()
	{
		if(!isset(self::$groups))
			self::loadGroups();

		return self::$groups;
	}

	public static function getGroupName($id)
	{
		if(!isset(self::$groups))
			self::loadGroups();

		return self::$groups->getGroupName($id);
	}

	public static function getCountryCode($IP)
	{
		$a = explode(".",$IP);
		if($a[0] == 10) // IPs 10.0.0.0 - 10.255.255.255 = private network, so can't geolocate
			return 'unknown';
		if($a[0] == 127) // IPs 127.0.0.0 - 127.255.255.255 = local network, so can't geolocate
			return 'unknown';
		if($a[0] == 172 && ($a[1] >= 16 && $a[1] <= 31)) // IPs 172.16.0.0 - 172.31.255.255 = private network, so can't geolocate
			return 'unknown';
		if($a[0] == 192 && $a[1] == 168) // IPs 192.168.0.0 - 192.168.255.255 = private network, so can't geolocate
			return 'unknown';
		if($a[0] >= 224) // IPs over 224.0.0.0 are not assigned, so can't geolocate
			return 'unknown';
		$longIP = $a[0] * 256 * 256 * 256 + $a[1] * 256 * 256 + $a[2] * 256 + $a[3]; // we need unsigned value
		if(!file_exists('cache/flags/flag' . $a[0]))
		{
			$flagData = @file_get_contents('http://country-flags.ots.me/flag' . $a[0]);
			if($flagData === false)
				return 'unknown';
			if(@file_put_contents('cache/flags/flag' . $a[0], $flagData) === false)
				return 'unknown';
		}
		$countries = unserialize(file_get_contents('cache/flags/flag' . $a[0])); // load file
		$lastCountryCode = 'unknown';
		foreach($countries as $fromLong => $countryCode)
		{
			if($fromLong > $longIP)
				break;
			$lastCountryCode = $countryCode;
		}
		return $lastCountryCode;
	}
}
