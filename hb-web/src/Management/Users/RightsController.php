<?php
namespace Management\Users;

/**
 * Profile controller
 *
 * PHP version 7.0
 */
class RightsController extends \Management\ManagementAuthenticatedController
{
    /** @var RightsModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new RightsModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows the rights page
     *
     * @return void
     */
    public function indexAction()
    {
        $profileId = $this->authenticationProvider->getProfileId();
        if (count($_POST) > 0){
            $this->model->initByData($_POST);
            $this->model->save($profileId);
        }
        $this->model->load($profileId);
        $data = $this->model->serialize();
        $this->render('Management/Users/RightsView.php', $data);
    }



}