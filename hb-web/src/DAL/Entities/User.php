<?php
namespace DAL\Entities;
use Core\Entity;
/**
 *
 * User Entity class
 */
class User extends Entity
{
    /** @var int */
    public $userId;
   
    /** @var int */
    public $profileId;
   
    /** @var int */
    public $levelId;
    
    /** @var string */
    public $userName;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string User level name */
    public $levelName;

    /** @var string User level description */
    public $levelHelp;

    /**
     * Gets the full name formatted
     * @return string|null
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @inheritDoc
     */
    public function typify()
    {
        $this->userId = intval($this->userId);
        $this->profileId = intval($this->profileId);
        $this->levelId = intval($this->levelId);
        $this->userName = strval($this->userName);
        $this->firstName = strval($this->firstName);
        $this->lastName = strval($this->lastName);
        $this->levelName = strval($this->levelName);
        $this->levelHelp = strval($this->levelHelp);
    }
}