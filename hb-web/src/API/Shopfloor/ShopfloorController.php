<?php
namespace API\Shopfloor;

use Common\Helpers;
use Core\Exceptions\HttpBadRequestException;
use Core\HttpResponse;
use API\APIAssertions;

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
     * Get data for the billboard contents table of a specific monitor
     *
     * @param int|null $monitorId id of monitor attempting the load
     * @return void
     * @throws HttpBadRequestException
     */
    public function indexAction($monitorId)
    {
        APIAssertions::assertGet();

        if (!isset($monitorId) || (0 === $monitorId)) {
            throw new HttpBadRequestException("MonitorId is null");
        }
        $error = $this->model->load($monitorId);
        $success = empty($error);        
        $errors = $success ? [] : ["There is no billboard for monitor with id $monitorId"];
        $result = [
            "header" => $this->model->header,
            "hours" => $this->model->hours,
            "monitorId" => $monitorId
        ];
        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * Shows a JSON with the list of available comments
     *
     * @return void
      * @throws HttpBadRequestException
    */
    public function getCommentsAction()
    {
        APIAssertions::assertGet();

        $result = $this->model->getCommentsList();
        $response = new HttpResponse($result);
        $response->output();
    }

    /**
     * Shows a JSON with the list of available escalations
     *
     * @return void
     * @throws HttpBadRequestException
     */
    public function getEscalationsAction()
    {
        APIAssertions::assertGet();

        $result = $this->model->getEscalatedList();
        $response = new HttpResponse($result);
        $response->output();
    }

    /**
     * Saves a comment for a specific hourly interval
     *
     * params int hourlyId Identifies the hourly interval
     * params int[] comments List of comment ids 
     * 
     * @return void
     * @throws HttpBadRequestException
     */
    public function saveCommentsAction()
    {
        $data = APIAssertions::assertPost();

        if (!isset($data->hourlyId) || !isset($data->comments)) {
            $ex = new HttpBadRequestException();
            if (!isset($data->hourlyId)) {
                $ex->errors[] = "Missing hourlyId";
            } 
            if (!isset($data->comments)) {
                $ex->errors[] = "Missing comments";
            } 
            throw $ex;
        }

        $hourlyId = $data->hourlyId;
        $comments = $data->comments;

        $result = $this->model->saveComments($hourlyId, $comments);
        $error = $result["errorMessage"];
        $success = empty($error);
        $errors = $success ? [] : [$error];
        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * Saves escalation reasons for a specific hourly interval
     *
     * params int hourlyId Identifies the hourly interval
     * params int[] escalations List of escalation ids 
     * 
     * @return void
     * @throws HttpBadRequestException
     */
    public function saveEscalationsAction()
    {
        $data = APIAssertions::assertPost();

        if (!isset($data->hourlyId) || !isset($data->escalations)) {
            $ex = new HttpBadRequestException();
            if (!isset($data->hourlyId)) {
                $ex->errors[] = "Missing hourlyId";
            }
            if (!isset($data->escalations)) {
                $ex->errors[] = "Missing escalations";
            } 
            throw $ex;
        }

        $hourlyId = $data->hourlyId;
        $escalations = $data->escalations;

        $result = $this->model->saveEscalations($hourlyId, $escalations);
        $error = $result["errorMessage"];
        $success = empty($error);
        $errors = $success ? [] : [$error];
        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * Sign Off open Hour interval
     * 
     * params int hourlyId Identifies the hourly interval
     * params string barcode Barcode of operator signing off the hourly interval  
     *
     * @return void
     * @throws HttpBadRequestException
     */
    public function signOffHourAction()
    {
        $data = APIAssertions::assertPost();

        if (!isset($data->hourlyId) || !isset($data->operatorBarcode)) {
            $ex = new HttpBadRequestException();
            if (!isset($data->hourlyId)) {
                $ex->errors[] = "Missing hourlyId";
            } 
            if (!isset($data->operatorBarcode)) {
                $ex->errors[] = "Missing operatorBarcode";
            } 
            throw $ex;
        }

        $hourlyId = $data->hourlyId;
        $operatorBarcode = $data->operatorBarcode;

        $result = $this->model->signOffHour($hourlyId, $operatorBarcode);
        $error = $result["errorMessage"];
        $success = empty($error);
        $errors = $success ? [] : [$error];
        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * Sign Off open Shift
     *
     * params int shiftLogSignOffID Identifies the date / shift / location of this billboArd 
     * params string barcode Barcode of operator signing off the hourly interval  
     * 
     * @return void
     * @throws HttpBadRequestException
     */
    public function signOffShiftAction()
    {
        $data = APIAssertions::assertPost();

        if (!isset($data->shiftLogSignOffId) || !isset($data->operatorBarcode)) {
            $ex = new HttpBadRequestException();
            if (!isset($data->shiftLogSignOffId)) {
                $ex->errors[] = "Missing shiftLogSignOffId";
            } 
            if (!isset($data->operatorBarcode)) {
                $ex->errors[] = "Missing operatorBarcode";
            } 
            throw $ex;
        }

        $shiftLogSignOffID = $data->shiftLogSignOffId;
        $operatorBarcode = $data->operatorBarcode;
        $result = $this->model->signOffShift($shiftLogSignOffID, $operatorBarcode);
        $error = $result["errorMessage"];
        $success = empty($error);
        $errors = $success ? [] : [$error];
        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }
}