<?php
namespace Management\Monitors\EditMonitor;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class EditMonitorController extends \Management\ManagementAuthenticatedController
{
    /** @var EditMonitorModel $model The model handling business logic for this controller */
    protected $model;
    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new EditMonitorModel();
        parent::__construct($routeParams, $model);
    }
    /**
     * Edit monitors page
     * @param int $monitorId
     */
    public function indexAction($monitorId)
    {
        if (count($_POST) > 0) {
            $profileId = $this->authenticationProvider->getProfileId();
            $monitor = new \DAL\Entities\Monitor();
            $data = $_POST['params'];
            $data['userId'] = $profileId;
            $data['id'] = $monitorId;
            $monitor->init($data);
            $success = $this->model->editMonitor($monitor);
            if ($success) {
                header("Location: " . SITE_URL . "management/monitors");
                exit();
            }
        }

        $this->model->load($monitorId);
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Monitors/EditMonitor/EditMonitorView.php', $data);

    }

}

?>