<?php
namespace API\Monitors;

use Common\Helpers;
use API\Monitors\MonitorsModel;
use Core\HttpResponse;
use API\APIAssertions;
use DAL\Entities\Monitor;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class MonitorsController extends \API\AuthenticatedController
{
    /** @var MonitorsModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new MonitorsModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows a JSON with the monitorId corresponding to an IP address
     *
     * @return void
     */
    public function getMonitorIdAction()
    {
        APIAssertions::assertGet();

        $ipAddress = Helpers::getIP();

        $monitor = $this->model->getMonitorFromIp($ipAddress);
        $success = $monitor instanceof Monitor;
        $errors = $success ? [] : ["Monitor not found"];
        $response = new HttpResponse($monitor, $success, HttpResponse::HTTP_OK);
        $response->output();
    }

    /**
     * Gets a list of monitors.
     * 
     * @return void
     */
    public function getMonitors()
    {
        APIAssertions::assertGet();

        $result = $this->model->getMonitors();
        $response = new HttpResponse($result);
        $response->output();
    }
}