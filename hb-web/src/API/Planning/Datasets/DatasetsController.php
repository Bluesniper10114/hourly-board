<?php
namespace API\Planning\Datasets;

use API\APIAssertions;
use Core\HttpResponse;
use Core\Exceptions\HttpBadRequestException;

class DatasetsController extends \API\AuthenticatedController
{
    /** @var DatasetsModel $model The model handling planning datasets API */
    protected $model;

    public function __construct($routeParams, $model = null)
    {
        if (is_null($model)) {
            $model = new DatasetsModel();
        }
        parent::__construct($routeParams, $model);
    }

    /**
     * Get dates from today t0 + 2 weeks, t0 - 2 weeks
     * @return void
     */
    public function datesAction()
    {
        APIAssertions::assertGet();

        $dates = $this->model->getDates();
        $success = !empty($dates);
        $errors = $success ? [] : ["No dates found"];
        $response = new HttpResponse($dates, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * Sets the default billboard for a specific date and shift.
     * This is important for dates and shifts which have multiple target types set (eg. Daily and by PartNumber)
     * @return void
     */
    public function activateBillboardAction()
    {

        $data = APIAssertions::assertPost();
        if (!isset($data->dailyTargetID)) {
            $ex = new HttpBadRequestException("Invalid parameters");
            if (!isset($data->dailyTargetID)){
                $ex->errors[] = "Missing daily target id";
            } 
            throw $ex;
        }
        $profileId = $this->getProfileIdOrThrowException();

        $error = $this->model->activateDatasetOnBillboard($profileId, $data->dailyTargetID);
        $success = empty($error);
        $errors = $success ? [] : [$error];

        $response = new HttpResponse(null, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * POST
     * Gets the planning datasets filtered by lines and dates. 
     * Request containing:
     *  int[] "lines" with line ids 
     *  string[] "dates" with string dates in the format '2019-02-15' - only datasets for these dates will be shown
     *  string[] "shifts" array of shifts A/B/C to be retrieved
     * @return void
     */
    public function datasetsAction()
    {
        $data = APIAssertions::assertPost();
        if (!isset($data->lines) || !isset($data->dates) || !isset($data->shifts)) {
            $ex = new HttpBadRequestException("Invalid parameters");
            if (!isset($data->lines)){
                $ex->errors[] = "Missing lines";
            } 
            if (!isset($data->dates)){
                $ex->errors[] = "Missing dates";
            } 
            if (!isset($data->shifts)){
                $ex->errors[] = "Missing shifts";
            } 
            throw $ex;
        }
        $profileId = $this->getProfileIdOrThrowException();
        
        $result = $this->model->getPlanningDatasets($profileId, $data->lines, $data->dates, $data->shifts);
        $success = !empty($result);
        $errors = $success ? [] : ['No planning set available'];

        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }
}