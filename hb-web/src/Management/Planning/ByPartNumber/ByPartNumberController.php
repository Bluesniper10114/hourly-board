<?php
namespace Management\Planning\ByPartNumber;

use \PhpOffice\PhpSpreadsheet\IOFactory;
use \Common\Spreadsheet\XlsReadFilter;

/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class ByPartNumberController extends \Management\ManagementAuthenticatedController
{
    /** @var ByPartNumberModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new ByPartNumberModel();
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

        if (isset($_GET['line']) && isset($_GET['date'])) {
            $this->model->searchLine = $_GET['line'];
            $this->model->searchDate = $_GET['date'];
        }
        $this->model->load($profileId);
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Planning/ByPartNumber/ByPartNumberView.php', $data);
    }

    /**
     * Load the daily planning page by line and date
     *
     * @return void
     */
    public function loadAction()
    {
        $profileId = $this->authenticationProvider->getProfileId();
        $data = [];
        if (is_null($profileId)) {
            return;
        }
        $xml = $this->model->loadByLineDate($profileId, $_POST['line'], $_POST['date']);
        $data['errorHtml'] = $this->application->displayMessage();
        $data['xml'] = $xml;
        echo json_encode($data);
    }

    /**
     * Shows the upload page
     *
     * @return void
     */
    public function uploadAction()
    {
        $errorHtml = null;
        $success = true;
        $returnData = null;
        if (0 < $_FILES['file']['error']) {
            $error = 'Error: ' . $_FILES['file']['error'] . '<br>';
            $success = false;
        } else {
            $dir = sys_get_temp_dir();
            $ext = explode(".", $_FILES['file']['name']);
            $ext = end($ext);
            if (in_array($ext, ['xls', 'xlsx'])) {
                $inputFileName = $dir . "/" . $_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], $inputFileName);

                $inputFileType = 'Xls';

                /**  Create a new Reader of the type defined in $inputFileType  **/
                /** @var \PhpOffice\PhpSpreadsheet\Reader\BaseReader */
                $reader = IOFactory::createReader($inputFileType);
                $reader->setReadFilter(new XlsReadFilter());
                /**  Advise the Reader to load all Worksheets  **/
                $reader->setLoadAllSheets();
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $spreadsheet = $reader->load($inputFileName);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                $dataRows = [];
                foreach ($sheetData as $index => $value) {
                    if ($index > 1) {
                        if (empty($value['A']) || empty($value['B'])) {
                            $errorHtml = "invalid_xls_data";
                            $success = false;
                            break;
                        }
                        $data = array_values($value);
                        $data[0] = preg_replace('~\D~', '', $data[0]);
                        $dataRows[] = ['partNumber' => $data[0], 'totals' => $data[1], 'routing' => null];
                    }
                }

                $returnData = $this->model->updateRoutingByPartNumber($dataRows);
                if (!empty($returnData['error'])) {
                    $this->application->onError($returnData['error']);
                    $success = false;
                }
            } else {
                $errorHtml = "file_format";
                $success = false;
            }

        }
        $errorHtml = $this->application->displayMessage();
        echo json_encode(['errorHtml' => $errorHtml, 'data' => $returnData['dataRows']]);
    }

    /**
     * save planning by part xml
     *
     * @return void
     */
    public function saveAction()
    {
        $profileId = $this->authenticationProvider->getProfileId();
        $xml = $_REQUEST["xmlOutput"];
        $errorHtml = null;
        $success = true;
        $error = $this->model->saveByPartNumber($profileId, $xml);
        if (!empty($error)) {
            $this->application->onError($error);
            $success = false;
        }
        $errorHtml = $this->application->displayMessage();
        echo json_encode(['error' => $errorHtml, 'success' => $success]);
    }

    /**
     * save planning by part xml
     *
     * @return void
     */
    public function refreshRoutingAction()
    {
        $id = $_POST['partNumber'];
        $errorHtml = null;
        // remove non number characters
        $id = preg_replace('~\D~', '', $id);
        $returnData = $this->model->getRoutingByPartNumber($id);
        if (!empty($returnData['error'])) {
            $this->application->onError($returnData['error']);
            $success = false;
        }
        if (intval($returnData['value']) === 0) {
            $errorHtml = 'invalidRouting';
            $success = false;
        } else {
            $errorHtml = $this->application->displayMessage();
        }
        echo json_encode(['errorHtml' => $errorHtml, 'routing' => $returnData['value'], 'partNumber' => $id]);
    }

}