<?php
namespace Management\Planning\ByDay;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class ByDayController extends \Management\ManagementAuthenticatedController
{
    /** @var ByDayModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new ByDayModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows the daily planning page
     *
     * @return void
     */
    public function indexAction()
    {
        $profileId = $this->authenticationProvider->getProfileId();

        if (is_null($profileId)) {
            return;
        }
        $tags = null;
        $this->model->load($profileId, $tags);
        if (isset($_GET['s'])) {
            $this->model->search = $_GET['s'];
        }
        if (isset($_GET['line'])) {
            $lineName = $this->model->getLineName($_GET['line']);
            if (!empty($lineName)) {
                $this->model->search = $lineName;
                $this->model->searchDate = $_GET['date'];
            }
        }
        if (!empty($_GET['debug'])) {
            $this->model->debug = true;
        }
        if (isset($_GET['success'])) {
            /** @var \ServiceProviders\LocalisationServiceProvider|null */
            $localisationProvider = $this->application->getServiceProvider("ServiceProviders\LocalisationServiceProvider");
            if (is_null($localisationProvider)) {
                return;
            }
            $translations = $localisationProvider->getTranslationsForKey("DailyPlanning");
            $message = $translations['SaveSuccess'];
            $this->application->onSuccess($message);
        }
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Planning/ByDay/ByDayView.php', $data);
    }

    /**
     * search ajax action
     *
     * @return void
     */
    public function searchAction()
    {
        $profileId = $this->authenticationProvider->getProfileId();

        if (is_null($profileId)) {
            return;
        }
        $tags = $_POST['tags'];
        $this->model->load($profileId, $tags);
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();
        echo json_encode(['errorHtml' => $data['errorHtml'], 'xmlData' => $data['xmlData']]);
    }

    /**
     * save daily planning xml
     *
     * @return void
     */
    public function saveAction()
    {
        $profileId = $this->authenticationProvider->getProfileId();
        $xml = $_REQUEST["xmlOutput"];
        $errorHtml = null;
        $success = true;
        $error = $this->model->saveByDay($profileId, $xml);
        if (!empty($error)) {
            $this->application->onError($error);
            $success = false;
        }
        $errorHtml = $this->application->displayMessage();
        echo json_encode(['error' => $errorHtml, 'success' => $success]);
    }

}