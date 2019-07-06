<?php
namespace Management\Shopfloor;

use DAL\ShopfloorDAL;

/**
 * Model for breaks
 */
class ShopfloorModel extends \Common\Model
{
    /** @var string|null */
    public $xml;

    /** @var string */
    public $error = '';

    /** @var array|null */
    public $translationHeader;

    /** @var array|null */
    public $translationBody;

    /** @var array|null */
    public $translationDowntime;

    /** @var array|null */
    public $translationPopup;

    /** @var array */
    public $commentsList;

    /** @var array */
    public $escalatedList;

    /** @var array */
    public $linesList;

    /** @var array */
    public $shiftTypesList;

    /** @var array */
    public $downtimeDictionary;

    /** @var ShopfloorDAL */
    public $dal;

    /** @var int  */
    public $monitorId;

    /** @var int  */
    public $hourlyId;

    /** @var bool  */
    public $readOnly = false;

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new ShopfloorDAL();
        $this->downtimeDictionary = $this->dal->getDowntimeDictionary();
    }

    /**
     * Loads ShopfloorModel
     * @param int $monitorId id of monitor attempting the load
     * @return void
     */
    public function load(int $monitorId)
    {
        $this->monitorId = $monitorId;
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Shopfloor");
        $this->title = $translations["Title"];
        $this->translationHeader = $translations["HeaderLabels"];
        $this->translationBody = $translations["BodyLabels"];
        $this->translationPopup = $translations["PopupLabels"];

        if (!empty($monitorId)) {
            $this->xml = $this->dal->getBillboardXml($monitorId);
            $this->commentsList = $this->dal->getCommentsList();
            $this->escalatedList = $this->dal->getEscalatedList();
        }
    }

    /**
     * Loads Downtime Minutes 
     * @param int $targetHourlyID id of monitor attempting the load
     * @return void
     */
    public function loadDowntimeMinutes($targetHourlyID)
    {
        $this->hourlyId = $targetHourlyID;
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Shopfloor");
        $this->title = $translations["DowntimeLabels"]["Title"];
        $this->translationDowntime = $translations["DowntimeLabels"];
        if (!empty($targetHourlyID)) {
            $this->xml = $this->dal->getDowntimeMinutes($targetHourlyID);
        }
    }

    /**
     * Loads ShopfloorModel for Reports page
     * @return void
     */
    public function loadReport()
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("HistoricalBillboard");
        $this->title = $translations["Title"];
        $this->translationHeader = $translations["HeaderLabels"];
        $this->linesList = $this->getLinesList();
        $this->shiftTypesList = $this->getShiftTypeList();
    }

    /**
     * Loads Historical Billboard report
     * @param string $date selected date
     * @param string $line selected line
     * @param string $shiftType selected shiftType
     * @return void
     */
    public function loadHistoricalBillboard($date, $line, $shiftType)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Shopfloor");
        $this->title = $translations["Title"];
        $this->translationHeader = $translations["HeaderLabels"];
        $this->translationBody = $translations["BodyLabels"];
        $this->translationPopup = $translations["PopupLabels"];
        $this->readOnly = true;
        $result = $this->dal->getHistoricalBillboard($date, $line, $shiftType);
        if (!empty($result['error'])) {
            $this->getApplication->onError($result['error']);
        } else {
            $this->xml = $result['xml'];
        }
        $this->commentsList = [];
        $this->escalatedList = [];
    }
    
    /**
     * Gets Comments List
     *
     * @return array
     */
    public function getCommentsList()
    {
        $list = $this->dal->getCommentsList();
        return $list;
    }

    /**
     * Gets Escalated List
     *
     * @return array
     */
    public function getEscalatedList()
    {
        $list = $this->dal->getEscalatedList();
        return $list;
    }

    /**
     * Gets Lines for report
     *
     * @return array
     */
    public function getLinesList()
    {
        $list = $this->dal->getLinesList();
        return $list;
    }

    /**
     * Gets Shift Type List
     *
     * @return array
     */
    public function getShiftTypeList()
    {
        $list = $this->dal->getShiftTypeList();
        return $list;
    }

    /**
     * Save Escalated
     *
     * @param int $hourlyId
     * @param array $escalatedList
     * @return array
     */
    public function saveEscalated($hourlyId, $escalatedList)
    {
        $escalated = '';

        $trimLength = 120;
        $listEscalated = $this->model->getEscalatedList();
        foreach ($escalatedList as $id) {
            $escalated .= $id . ' - ' . $listEscalated[$id] . " | ";
            if (strlen($escalated) > $trimLength) {
                $escalated = substr($escalated, 0, $trimLength) . '...';
                break;
            }
        }
        $error = $this->dal->saveEscalated($hourlyId, $escalated);
        $json = array('id' => $hourlyId, 'escalated' => $escalated, 'errorMessage' => $error);
        return $json;
    }

    /**
     * Save Downtime
     *
     * @param int $hourlyId
     * @param string $xml
     * @return string|null
     */
    public function saveDowntime($hourlyId, $xml)
    {
        $error = $this->dal->saveDowntime($hourlyId, $xml);
        return $error;
    }

    /**
     * Save Comments
     *
     * @param int $hourlyId
     * @param array $commentList
     * @return array
     */
    public function saveComments($hourlyId, $commentList)
    {
        $comment = '';
        $trimLength = 120;
        $listComments = $this->getCommentsList();
        foreach ($commentList as $id) {
            $comment .= $id . ' - ' . $listComments[$id] . " | ";
            if (strlen($comment) > $trimLength) {
                $comment = substr($comment, 0, $trimLength) . '...';
                break;
            }
        }
        $error = $this->dal->saveComments($hourlyId, $comment);
        $json = array('id' => $hourlyId, 'comment' => $comment, 'errorMessage' => $error);
        return $json;
    }

    /**
     * Sign Off Hour
     *
     * @param int $hourlyId
     * @param string $operatorBarcode
     * @return string|null
     */
    public function signOffHour($hourlyId, $operatorBarcode)
    {
        $error = $this->dal->signOffHour($hourlyId, $operatorBarcode);
        return $error;
    }

    /**
     * Sign Off Shift
     *
     * @param int $shiftLogSignOffID
     * @param string $operatorBarcode
     * @return string|null
     */
    public function signOffShift($shiftLogSignOffID, $operatorBarcode)
    {
        $error = $this->dal->signOffShift($shiftLogSignOffID, $operatorBarcode);
        return $error;
    }
}