<?php

use Settings\Config;

// place this file outside of the source code folder, at the same level
Config::$config["DB_HOST"] = getenv("DB_HOST");
Config::$config["DB_NAME"] = getenv("DB_NAME");
Config::$config["DB_USER"] = getenv("DB_USER");
Config::$config["DB_PASSWORD"] = getenv("DB_PASSWORD");
Config::$config["SHOW_ERRORS"] = getenv("SHOW_ERRORS");; // for debug purposes, this should be set to "TRUE"

Config::$config["DEFAULT_LANGUAGE"] = getenv("DEFAULT_LANGUAGE"); // for debug purposes, this should be set to "ro" on the live machine


Config::$config["siteurl"] = getenv("SITE_ROOT_URL");
Config::$config["apiurl"] = getenv("SITE_API_URL");
