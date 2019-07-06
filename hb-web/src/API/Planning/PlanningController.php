<?php
namespace API\Planning;

use DAL\LayoutDALv1;
use API\APIAssertions;
use Core\HttpResponse;

/**
 * General planning support
 */
class PlanningController extends \API\AuthenticatedController
{
    /** @var PlanningModel $model The model */
    public $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams, $model = null)
    {
        if (is_null($model)) {
            $model = new PlanningModel();
            $model->dal = new LayoutDALv1();
        }
        parent::__construct($routeParams, $model);
    }

    /**
     * Get all the lines available
     * @return void
     */
    public function linesAction()
    {
        APIAssertions::assertGet();

        $lines = $this->model->getLines();
        $success = !empty($lines);
        $errors = $success ? [] : ["No lines found"];
        $response = new HttpResponse($lines, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
    * Gets the tags for the Search box in the daily planning form
    * @return void
    */
    public function tagsAction()
    {
        APIAssertions::assertGet();

        $tags = $this->model->getTags();
        $success = !empty($tags);
        $errors = $success ? [] : ["No tags found"];
        $response = new HttpResponse($tags, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }

    /**
     * Gets the full list of lines / cells and machines
     * 
     * @return void
     */
    public function allLinesCellsAndMachinesAction()
    {
        APIAssertions::assertGet();
        $profileId = $this->getProfileIdOrThrowException();

        $elements = $this->model->getLinesCellsAndMachines($profileId);
        $success = !empty($elements);
        $errors = $success ? [] : ["No lines found. Cannot get details"];    

        $response = new HttpResponse($elements, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }
 

}