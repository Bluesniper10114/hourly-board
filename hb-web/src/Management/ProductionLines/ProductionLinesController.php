<?php
namespace Management\ProductionLines;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class ProductionLinesController extends \Management\ManagementAuthenticatedController
{
    /** @var ProductionLinesModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new ProductionLinesModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows the users listing page
     *
     * @return void
     */
    public function indexAction()
    {
        $profileId = $this->authenticationProvider->getProfileId();
        $this->model->load($profileId);
        $data = $this->model->serialize();
        $this->render('Management/ProductionLines/ProductionLinesView.php', $data);
    }
}