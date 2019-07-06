<?php
namespace Management\Breaks;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class BreaksController extends \Management\ManagementAuthenticatedController
{
    /** @var BreaksModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new BreaksModel();
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
        $this->render('Management/Breaks/BreaksView.php', $data);
    }

    /**
     * Save the xml
     *
     * @return void
     */
    public function saveAction()
    {
        $this->model->initByData($_POST);
        $profileId = $this->authenticationProvider->getProfileId();
        $success = $this->model->save($profileId);
        $error = $this->model->error;
        $result = ['error' => $error, 'success' => $success];
        echo json_encode($result);
    }
}