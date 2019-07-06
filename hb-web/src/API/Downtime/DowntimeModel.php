<?php
namespace API\Downtime;

use DAL\ShopfloorDALv1;
use ServiceProviders\VersionServiceProvider;
use DAL\MonitorsDAL;
use DAL\Entities\DowntimeReasonJsonToXmlConverter;

/**
 * Model for breaks
 */
class DowntimeModel extends \Common\Model
{
    /** @var string */
    public $error = '';

    /** @var ShopfloorDALv1 */
    public $dal;

    public $data;

    public $hourlyId;

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new ShopfloorDALv1();
    }

    /**
     * Loads ShopfloorModel
     * 
     * @param int $targetHourlyId id of hourly interval showing downtime
     * @return void
     */
    public function load(int $targetHourlyId)
    {
        $this->hourlyId = $targetHourlyId;
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Shopfloor");
        $this->title = $translations["DowntimeLabels"]["Title"];
        $this->translationDowntime = $translations["DowntimeLabels"];
        if (!empty($targetHourlyId)) {
            $this->data = $this->dal->getDowntime($targetHourlyId);
        }
    }

    /**
     * Gets the dropdown for downtime reasons
     * 
     * @return array
     */
    public function getDowntimeReasonsDictionary()
    {
        $dict = $this->dal->getDowntimeDictionary();
        $result = [];
        foreach ($dict as $key => $value) {
            $result[] = ['id' => $key, 'text' => $value];
        }
        return $result;
    }

    /**
     * Saves the comments for a specific hourly id
     * 
     * @param int $hourlyId Identifies the hourly interval
     * @param array $downtimeReasons Reasons for each downtime interval
     * @param string $timeStamp A timestamp when the reasons where changed
     * @return string|null An error or empty string on success
     */
    public function saveDowntimeReasons($hourlyId, $downtimeReasons, $timeStamp)
    {
        $transformer = new DowntimeReasonJsonToXmlConverter();
        $transformer->reasonsDictionary = $this->dal->getDowntimeDictionary();
        $transformer->hourlyId = $hourlyId;
        $transformer->downtimeReasons = $downtimeReasons;
        $transformer->timeStamp = $timeStamp;

        $error = $this->dal->saveDowntimeReasons($transformer);
        return $error;
    }

}

