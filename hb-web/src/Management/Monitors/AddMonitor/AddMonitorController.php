<?php
namespace Management\Monitors\AddMonitor;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class AddMonitorController extends \Management\ManagementAuthenticatedController
{
    /** @var AddMonitorModel $model The model handling business logic for this controller */
    protected $model;
    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new AddMonitorModel();
        parent::__construct($routeParams, $model);
    }
    /**
     * Add monitors page
     */
    public function indexAction()
    {
        $monitor = null;
        if (count($_POST) > 0) {
            $profileId = $this->authenticationProvider->getProfileId();
            $monitor = new \DAL\Entities\Monitor();
            $data = $_POST['params'];
            $data['userId'] = $profileId;

            $monitor->init($data);
            $success = $this->model->addMonitor($monitor);
            if ($success) {
                header("Location: " . SITE_URL . "management/monitors");
                exit();
            }

        }
        $this->model->load($monitor);
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();

        $this->render('Management/Monitors/AddMonitor/AddMonitorView.php', $data);

    }

}
