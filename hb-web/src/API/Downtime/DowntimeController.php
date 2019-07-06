<?php
namespace API\Downtime;
use Common\Helpers;
use Core\HttpResponse;
use Core\Exceptions\HttpBadRequestException;
use API\APIAssertions;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class DowntimeController extends \Common\Controller
{
    /** @var DowntimeModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new DowntimeModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Get data for the billboard contents table of a specific monitor
     *
     * @param int $hourlyId id of time interval
     * @return void
     */
    public function indexAction($hourlyId)
    {
        APIAssertions::assertGet();

        $this->model->load($hourlyId);
        $response = new HttpResponse($this->model->data);
        $response->output();
    }

    /**
     * Shows a JSON with the list of available downtime reasons
     *
     * @return void
     */
    public function getDowntimeReasonsAction()
    {
        APIAssertions::assertGet();

        $result = $this->model->getDowntimeReasonsDictionary();
        $response = new HttpResponse($result);
        $response->output();
    }

    /**
     * Save downtime minutes
     *
     * @return void
     * @throws HttpBadRequestException
     */
    public function saveDowntimeReasonsAction()
    {
        $data = APIAssertions::assertPost();

        if (!isset($data->hourlyId) || !isset($data->downtimeReasons) || !isset($data->timeStamp)) {
            $ex = new HttpBadRequestException("Invalid parameters");
            if (!isset($data->hourlyId)){
                $ex->errors[] = "Missing hourlyId";
            } 
            if (!isset($data->downtimeReasons)){
                $ex->errors[] = "Missing downtimeReasons";
            } 
            if (!isset($data->hourlytimeStampId)){
                $ex->errors[] = "Missing timeStamp";
            } 
            throw $ex;
        }

        $error = $this->model->saveDowntimeReasons($data->hourlyId, $data->downtimeReasons, $data->timeStamp);
        $success = empty($error);
        $result = ["hourlyId" => $data->hourlyId];
        $errors = $success ? [] : [$error];
        $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

}