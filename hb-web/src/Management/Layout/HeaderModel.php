<?php
namespace Management\Layout;

use Management\Users\ProfileModel;
use DAL\UsersDAL;
/**
 *     Model for the management tool header
 */
class HeaderModel extends \Common\Model
{
    /** @var \DAL\UsersDAL */
    public $dal;

    /** @var string|null */
    public $message;

    /** @var array|null */
    public $translations;

    /** @var string|null */
    public $logo;

    /** @var int|null */
    public $profileId;

    /** @var string|null */
    public $menu;

    /** @var boolean */
    public $partialRender = false;

    /** @var \DAL\Entities\User|null Profile which is currently logged in */
    public $profile;

    /** @var string|null */
    public $language;

    /** @var string|null */
    public $helpContent;


    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new UsersDAL();
    }

    /**
     * Loads HeaderModel data
     * @return void
     */
    public function load()
    {
        if (is_null($this->profileId)) {
            return;
        }
        $this->profile = $this->dal->getProfileByProfileId($this->profileId);
        $this->dal->close();
        $path = dirname(dirname(dirname(__DIR__))) . "/language/en/test_help.php";
        if (file_exists($path)) {
            $this->helpContent = file_get_contents($path);
        }
    }
}
?>