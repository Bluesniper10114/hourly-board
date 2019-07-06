<?php
namespace Core;

abstract class Entity
{
    /**
     * Initialize the entity data
     *
     * @param array $data
     */
    public function init($data)
    {
        $cls = get_class($this);
        foreach ($data as $key => $value) {
            if (property_exists($cls, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * to avoid an automated property creation you could to use the magic __set() method to filter the properties out
     * Source: https://phpdelusions.net/pdo/objects
     * 
     * @param string $name
     * @param object $value
     * @return void
     */
    public function __set($name, $value) {
    }
    
    public abstract function typify();        
}