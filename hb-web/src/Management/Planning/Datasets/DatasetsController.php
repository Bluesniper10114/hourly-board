<?php
namespace Management\Planning\Datasets;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class DatasetsController extends \Management\ManagementAuthenticatedController
{
    /** @var DatasetsModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new DatasetsModel();
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
        $this->render('Management/Planning/Datasets/DatasetsView.php', $data);
    }

    /**
     * Set onbillboard 
     *
     * @return void
     */
    public function billboardUpdateAction()
    {
        $data = [];
        $profileId = $this->authenticationProvider->getProfileId();
        $this->model->setDatasetOnBillboard($profileId, $_POST['dailyTargetID']);
        
        $data['errorHtml'] = $this->application->displayMessage();
        echo json_encode($data);
    }
}