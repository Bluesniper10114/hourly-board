<?php
namespace API;

use Common\Helpers;
use Core\HttpResponse;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class BoardController extends \Common\Controller
{
    /** @var BoardModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new BoardModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows a JSON with the footer for the public shopfloor area
     *
     * @return void
     */
    public function getFooterAction()
    {
        APIAssertions::assertGet();
        
        $result = $this->model->getFooter();
        $response = new HttpResponse($result);
        $response->output();
    }
}