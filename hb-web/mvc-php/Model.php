<?php

namespace Core;

use Config;

/**
*    The base model for all types of models
*/
abstract class Model
{
    /** @var array Holds the labels for the model */
    public $labels = [];

    /** @var string|null Contains help content for the screen */
    public $helpContent;

    /** @var string|null Contains the title displayed on top of the screen */
    public $title;

    /** @var \Core\Data Contains data access layer functions (database access functions) */
    public $dal;

    /** @var \Core\IApplication Contains details about the application this model belongs to */
    public $application;

    /**
     * Constructor
     */
    public function __construct()
    {        
    }

    /**
     * Serializes the current object's (public) properties into an array.
     * You can use the array to explode it into variables.
     * Variables can then be used to make shorter calls typically in views.
     * @return array An array with object properties (e.g. ['property' => propertyValue] )
     **/
    public function serialize()
    {
        return get_object_vars($this);
    }
}