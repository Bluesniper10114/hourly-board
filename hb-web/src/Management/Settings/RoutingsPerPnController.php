<?php
namespace Management\Settings;
use Former\Facades\Former;

use Illuminate\Validation;
use Illuminate\Filesystem;
use Illuminate\Translation;
/**
 * Deals controller
 *
 * PHP version 7.0
 */
class RoutingsPerPnController extends \Management\ManagementAuthenticatedController
{
    /** @var RoutingsPerPnModel The model handling business logic for this controller */
    protected $model;

    /**
     * @inheritDoc
     */
    public function __construct($routeParams)
    {
        $model = new RoutingsPerPnModel();
        parent::__construct($routeParams, $model);
    }


    /**
     * Routing PN action page
     *
     * @return void
     */
    public function routingsPerPnAction()
    {
        if (!empty($_GET)) {
            $this->model->initByData($_GET);
        }
        $this->model->load();
        $data = $this->model->serialize();

        $this->render('Management/Settings/RoutingsPerPnView.php', $data);
    }

}