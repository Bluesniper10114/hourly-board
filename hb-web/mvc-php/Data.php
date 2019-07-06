<?php
namespace Core;
use PDO;
use App\Config;

class Data
{
    protected $db;

    function __construct()
    {
        $this->db = static::getDB();
    }

    function close()
    {
    }

    /**
    * Get the PDO database connection
    *
    * @return mixed
    */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null)
        {
            $dsn = 'sqlsrv:Server=' . getSetting("DB_HOST") . ';Database=' . getSetting("DB_NAME") .";ConnectionPooling=0";
            $db = new PDO($dsn, getSetting("DB_USER")  , getSetting("DB_PASSWORD") );

            // Throw an Exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }
}