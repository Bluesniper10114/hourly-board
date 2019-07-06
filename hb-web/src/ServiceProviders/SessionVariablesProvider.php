<?php
namespace ServiceProviders;

use Core\Persistence\PersistentObject;
use Core\ServiceProviders\ServiceProvider;

/**
 * Stores all the persistent objects in the app
 */
class SessionVariablesProvider extends ServiceProvider
{
    /**
     * An array of persistent objects
     *
     * @var array(PersistentObject)
     */
    private $sessions = [];

    /**
     * Gets the stored session value for a key
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue($key)
    {
        $this->lazyInstantiation($key);
        return $this->sessions[$key]->getValue();
    }

    /**
     * Permanently stores the session value for a key
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function storeSessionValue($key, $value)
    {
        $this->lazyInstantiation($key);
        $this->sessions[$key]->save($value);
    }

    /**
     * Clears the given session, but keeps the persistent object
     *
     * @param string $key
     * @return void
     */
    public function clear($key)
    {
        $this->lazyInstantiation($key);
        $this->sessions[$key]->clear();
    }

    /**
     * Clears the all session
     *
     * @return void
     */
    public function clearAll()
    {
        foreach ($this->sessions as $key => $val) {
            $this->lazyInstantiation($key);
            $this->sessions[$key]->clear();
        }
    }

    /**
     * Instantiates the persistent object if the key is not found
     *
     * @param string $key
     * @return void
     */
    private function lazyInstantiation($key)
    {
        if (!isset($this->sessions[$key])) {
            $this->sessions[$key] = new PersistentObject($key);
        }
    }
}