<?php
namespace Management\Settings;
use Former\Facades\Former;

use Illuminate\Validation;
use Illuminate\Filesystem;
use Illuminate\Translation;
/**
 * Settings controller
 *
 * PHP version 7.0
 */
class SettingsController extends \Management\ManagementAuthenticatedController
{
    /** @var SettingsModel The model handling business logic for this controller */
    protected $model;

    /**
    * @inheritDoc
    */
    public function __construct($routeParams)
    {
        $model = new SettingsModel();
        parent::__construct($routeParams, $model);
    }

    /**
     * General Settings action page
     *
     * @return void
     */
    public function generalAction()
    {
        if (count($_POST) > 0){
            $filesystem = new Filesystem\Filesystem();
            $fileLoader = new Translation\FileLoader($filesystem, '');
            $translator = new Translation\Translator($fileLoader, 'en_US');

            $factory = new Validation\Factory($translator);

            foreach ($_POST['data'] as $dataRow) {
                $validator = $factory->make($dataRow, $this->model->rules, $this->model->errorMessages);
                if ($validator->fails()) {
                    $this->model->errors = $validator->errors()->all();
                    Former::withErrors($validator);
                    break;
                } else {
                    $this->model->initByData($dataRow);
                    $this->model->save();
                }
            }
        }
        $this->model->load();
        $this->model->getSettingList();
        $data = $this->model->serialize();

        $this->render('Management/Settings/SettingsView.php', $data);
    }

}