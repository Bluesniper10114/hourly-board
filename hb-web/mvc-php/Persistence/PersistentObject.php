<?php

namespace Core\Persistence;

/**
 * A persistent object (saved to $_SESSION)
 **/
class PersistentObject
{
	private $sessionKey;

	/**
	 * Builds a persistent object (saved to $_SESSION)
	 * @param string $key The key used to store information in the $_SESSION object
	 * @return object Stored value or default value
	 * @throws \Exception when the key is not a string
	 **/
	public function __construct($key)
	{
		if (!is_string($key)) {
			throw new \Exception("Invalid key when building a persistent object " . $key);
		}
		$this->sessionKey = $key;
	}

	/**
	 * Gets the saved value or a default one if none was saved
	 * @param object|array|string|null $defaultValue Default value if none saved
	 * @return object|array|string|null Stored value or default value
	 **/
	public function getValue($defaultValue = null)
	{
		global $_SESSION;

		if (isset($_SESSION[$this->getKey()])) {
			return $_SESSION[$this->getKey()];
		}
		return $defaultValue;
	}

	/**
	 * The key of the storage array
	 * @return string Key
	 **/
	public function getKey()
	{
		return $this->sessionKey;
	}

	/**
	 * Saves the value
	 * @param object|array|string $value Value to save
	 * @return void
	 **/
	public function save($value)
	{
		global $_SESSION;

		$_SESSION[$this->getKey()] = $value;
	}

	/**
	 * Clears the value
	 * @return void
	 **/
	public function clear()
	{
		global $_SESSION;

		unset($_SESSION[$this->getKey()]);
	}


}
?>