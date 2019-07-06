<?php
namespace Settings;
/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{
    /**
     * holds an array of configuration entries
     *
     * @var array
     */
	public static $config = [];

    /**
     * Gets the setting value for a key
     *
     * @param string $key
     * @return mixed
     */
	public static function getSetting($key)
	{
		return self::$config[$key];
	}

	public static function init()
	{
		self::$config["DB_HOST"] = getenv("PHP_SETTINGS_host");
		self::$config["DB_NAME"] = getenv("PHP_SETTINGS_dbname");
		self::$config["DB_USER"] = getenv("PHP_SETTINGS_user");
		self::$config["DB_PASSWORD"] = getenv("PHP_SETTINGS_password");
		self::$config["SHOW_ERRORS"] = getenv("PHP_SETTINGS_host") == "DEBUG";

		self::$config["siteurl"] = getenv("PHP_SETTINGS_siteurl");
		self::$config["configuredPlanningTypes"] = 
		[
			"planning.by-day", 
			"planning.by-partnumber"
		];

		self::$config["appname"] = getenv("PHP_SETTINGS_AppName") ? : "MultiCode Board";
		self::$config["company"] = getenv("PHP_SETTINGS_Company") ? : "ZF Timisoara";
		self::$config["developer"] = getenv("PHP_SETTINGS_Developer") ? : "ProfiOPS www.profidocs.com";

		self::$config["ItemsPerPage"] = 10;
		self::$config["LoginExpiresInSeconds"] = 36000;
		
		self::$config["CurrentPageFormatting"] = "%u"; // displaying page number during pagination

		// all settings in local.config will overwrite the settings above
		$settingsFilePath = __DIR__ . '/local.config.php';
		if (file_exists($settingsFilePath)) {
			include $settingsFilePath;
		}

		if (!defined('SITE_URL')) {
			define('SITE_URL', self::$config["siteurl"]);
		}

		// set server timezone
		date_default_timezone_set('Europe/Bucharest');

		$xml = simplexml_load_file(__DIR__ . '/version.xml');

		// set version
		self::$config["version"] = $xml->Number;
		self::$config["AppVersionLine"] = self::$config["appname"].' v'.self::$config["version"]; // Bonus Arena WhiteLabel  1.0.1

		// set copyright information
		$developer = self::$config["developer"];
		$company = self::$config["company"];
		self::$config["CopyrightLine"] = "Copyright $developer @ 2018. Developed for $company";
	}
}
?>