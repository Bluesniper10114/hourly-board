<?php

namespace Management\Monitors;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class MonitorsController extends \Management\ManagementAuthenticatedController
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
     * Shows the users listing page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->model->load();
        $ip = \Common\Helpers::getIP();
        $monitorId = $this->model->getMonitorIdFromIp($ip);
        if (!empty($monitorId)) {
            header("Location: " . SITE_URL . "management/shopfloor/" . $monitorId);
            exit;
        }
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Monitors/MonitorsView.php', $data);
    }

    /**
     * Delete monitor action
     *
     * @param int $monitorId The monitor id
     * @return void
     */
    public function deleteAction($monitorId)
    {
        $profileId = $this->authenticationProvider->getProfileId();
        $profileId = isset($profileId) ? $profileId : 0;
        $this->model->deleteMonitor($profileId, $monitorId);
        header("Location: " . SITE_URL . "management/monitors");
    }
}

?>