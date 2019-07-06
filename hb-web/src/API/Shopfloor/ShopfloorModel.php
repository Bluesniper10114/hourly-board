<?php
namespace API\Shopfloor;

use DAL\ShopfloorDALv1;
use ServiceProviders\VersionServiceProvider;
use DAL\MonitorsDAL;
use Common\Helpers;
use DAL\Entities\Hour;
use DAL\Entities\BillboardHeader;

/**
 * Model for breaks
 */
class ShopfloorModel extends \Common\Model
{
    /** @var string */
    public $error = '';

    /** @var ShopfloorDALv1 */
    public $dal;

    /** @var BillboardHeader|null Header information */
    public $header = null;

    /** @var Hour[] Hourly intervals */
    public $hours = [];
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
     * @param int $monitorId id of monitor attempting the load
     * @return string Error message or empty string on success
     */
    public function load(int $monitorId)
    {
        $this->monitorId = $monitorId;
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("Shopfloor");
        $this->title = $translations["Title"];

        $errorMessage = '';

        if (!empty($monitorId)) {
            $this->header = $this->dal->getBillboardHeaderV1($monitorId);
        } else {
            $errorMessage .= "Monitor id is null";
            return $errorMessage;
        }
        $this->hours = $this->dal->getBillboardDataV1($monitorId);
        $errorMessage = "";
        if (empty($this->hours)) {
            $errorMessage .= "Missing hours information | ";
        }
        if (is_null($this->header)) {
            $errorMessage .= "Missing header information | ";
        }        
        return $errorMessage;
    }

    /**
     * Gets the comments list the user can select from
     *
     * @return array
     */
    public function getCommentsList()
    {
        $dict = $this->dal->getCommentsList();
        $result = [];
        foreach ($dict as $key => $value) {
            $result[] = ['id' => $key, 'text' => $value];
        }
        return $result;
    }

    /**
     * Gets the escalated list the user can select from
     *
     * @return array
     */
    public function getEscalatedList()
    {
        $dict = $this->dal->getEscalatedList();
        $result = [];
        foreach ($dict as $key => $value) {
            $result[] = ['id' => $key, 'text' => $value];
        }
        return $result;
    }

    /**
     * Gets the footer of the public shopfloor part of the system
     * 
     * @return array
     */
    public function getFooter()
    {

        /** @var VersionServiceProvider|null */
        $versionService = $this->application->getServiceProvider("ServiceProviders\VersionServiceProvider");

        if (is_null($versionService)) {
            return [];
        }

        $data = [
            "softwareVersionLine" => $versionService->getSoftwareVersionLine(),
            "copyrightLine" => $versionService->getCopyrightLine(),
            "datababaseVersion" => $versionService->getDatabaseVersion()
        ];
        return $data;
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
        $listComments = $this->dal->getCommentsList();
        $errorMessage = '';
        foreach ($commentList as $id) {
            if (!array_key_exists($id, $listComments)) {
                $errorMessage = "Missing comment with code $id";
                $comment = null;
                break;
            }

            $comment .= $id . ' - ' . $listComments[$id] . " | ";
            if (strlen($comment) > $trimLength) {
                $comment = substr($comment, 0, $trimLength) . '...';
                break;
            }
        }
        if ($errorMessage === '') {
            $errorMessage = $this->dal->saveComments($hourlyId, $comment);
        }

        $response = [
            "hourlyId" => $hourlyId,
            "comment" => $comment,
            "errorMessage" => $errorMessage
        ];
        return $response;
    }

    /**
     * Save Escalated
     *
     * @param int $hourlyId
     * @param array $escalatedList
     * @return array
     */
    public function saveEscalations($hourlyId, $escalatedList)
    {
        $escalated = '';

        $trimLength = 120;
        $listEscalated = $this->dal->getEscalatedList();
        $errorMessage = '';
        foreach ($escalatedList as $id) {
            if (!array_key_exists($id, $listEscalated)) {
                $errorMessage = "Missing escalation with code $id";
                $escalated = null;
                break;
            }

            $escalated .= $id . ' - ' . $listEscalated[$id] . " | ";
            if (strlen($escalated) > $trimLength) {
                $escalated = substr($escalated, 0, $trimLength) . '...';
                break;
            }
        }
        if ($errorMessage === '') {
            $errorMessage = $this->dal->saveEscalated($hourlyId, $escalated);
        }
        $response = [
            'hourlyId' => $hourlyId, 
            'escalations' => $escalated, 
            'errorMessage' => $errorMessage
        ];
        return $response;
    }

    /**
     * Sign Off Hour
     *
     * @param int $hourlyId
     * @param string $operatorBarcode
     * @return array
     */
    public function signOffHour($hourlyId, $operatorBarcode)
    {
        $error = $this->dal->signOffHour($hourlyId, $operatorBarcode);
        $response = [
            'errorMessage' => is_null($error) ? "" : $error,
            'hourlyId' => $hourlyId,
            'action' => "signOffHour"
        ];
        return $response;
    }

    /**
     * Sign Off Shift
     *
     * @param int $shiftLogSignOffID
     * @param string $operatorBarcode
     * @return array
     */
    public function signOffShift($shiftLogSignOffID, $operatorBarcode)
    {
        $error = $this->dal->signOffShift($shiftLogSignOffID, $operatorBarcode);
        $response = [
            'errorMessage' => is_null($error) ? "" : $error,
            'action' => "signOffShift"
        ];
        return $response;
    }
}