<?php
namespace Management\Shopfloor;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class ShopfloorController extends \Common\Controller
{
    /** @var ShopfloorModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new ShopfloorModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows the users listing page
     *
     * @param int $monitorId id of monitor attempting the load
     * @return void
     */
    public function indexAction($monitorId)
    {
        $this->model->load($monitorId);
        $data = $this->model->serialize();
        $data['partialRender'] = true;
        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Shopfloor/ShopfloorView.php', $data);
    }
    /**
     * Ajax support to save a comment
     *
     * @return void
     */
    public function saveCommentAction()
    {
        $hourlyId = $_REQUEST["hourlyId"];
        $commentList = $_REQUEST["comment"];
        $json = $this->model->saveComments($hourlyId, $commentList);

        echo json_encode($json);
    }

    /**
     * Ajax support to save an escalatedTo value
     *
     * @return void
     */
    public function saveEscalatedToAction()
    {
        $hourlyId = $_REQUEST["hourlyId"];
        $escalatedList = $_REQUEST["escalated"];

        $json = $this->model->saveEscalated($hourlyId, $escalatedList);

        echo json_encode($json);
    }

    /**
     * save Downtime minutes
     *
     * @return void
     */
    public function saveDowntimeAction()
    {
        $hourlyId = $_REQUEST["hourlyId"];
        $xml = $_REQUEST["xmlOutput"];
        $error = $this->model->saveDowntime($hourlyId, $xml);
        if (!empty($error)) {
            $this->application->onError($error);
        }
        header('location:' . SITE_URL . 'management/shopfloor/downtime-minutes/' . $hourlyId);
    }

    /**
     * sign Off open Hour interval
     *
     * @return void
     */
    public function signOffHourAction()
    {
        $monitorId = $_REQUEST["monitorId"];
        $hourlyId = $_REQUEST["hourlyId"];
        $operatorBarcode = $_REQUEST["operatorBarcode"];
        $error = $this->model->signOffHour($hourlyId, $operatorBarcode);
        if (!empty($error)) {
            $this->application->onError($error);
        }
        header('location:' . SITE_URL . 'management/shopfloor/' . $monitorId);
    }

    /**
     * sign Off open Shift
     *
     * @return void
     */
    public function signOffShiftAction()
    {
        $monitorId = $_REQUEST["monitorId"];
        $shiftLogSignOffID = $_REQUEST["shiftLogSignOffID"];
        $operatorBarcode = $_REQUEST["operatorBarcode"];
        $error = $this->model->signOffShift($shiftLogSignOffID, $operatorBarcode);
        if (!empty($error)) {
            $this->application->onError($error);
        }
        header('location:' . SITE_URL . 'management/shopfloor/' . $monitorId);
    }

    /**
     * Downtime minutes
     *
     * @param int $targetHourlyId id
     * @return void
     */
    public function downtimeMinutesAction($targetHourlyId)
    {
        $this->model->loadDowntimeMinutes($targetHourlyId);
        $data = $this->model->serialize();
        if (!empty($_GET['report'])) {
            $data['readOnly'] = true;
        }

        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Shopfloor/DowntimeMinutesView.php', $data);
    }
}