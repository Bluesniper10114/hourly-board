<?php
namespace Management\Reports;
use Management\Shopfloor\ShopfloorModel;
/**
 * Takes input from the HTTP request and sends actions
 * to the model in order to display the view
 */
class HistoricalBillboardController extends \Management\ManagementAuthenticatedController
{
    /** @var ShopfloorModel $model The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new ShopfloorModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * Shows the report page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->model->loadReport();
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Reports/HistoricalBillboardView.php', $data);
    }

    /**
     * load the report page
     *
     * @return void
     */
    public function loadAction()
    {
        $date = $_POST['selectDate'];
        $line = $_POST['line'];
        $shiftType = $_POST['shiftType'];
        
        $this->model->loadHistoricalBillboard($date, $line, $shiftType);
        $data = $this->model->serialize();
        $data['errorHtml'] = $this->application->displayMessage();
        $this->render('Management/Shopfloor/ShopfloorView.php', $data);
    }
     
}