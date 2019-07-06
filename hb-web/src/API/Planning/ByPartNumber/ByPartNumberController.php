<?php
namespace API\Planning\ByPartNumber;

use DAL\PlanningDALv1;
use API\APIAssertions;
use Core\Exceptions\HttpBadRequestException;
use Core\HttpResponse;


/**
 * Supports the form planning by part number
 */
class ByPartNumberController extends \API\AuthenticatedController
{
   /** @var ByPartNumberModel $model The model */
   public $model;

   /**
    * @inheritDoc
    */
   public function __construct($routeParams, $model = null)
   {
       if (is_null($model)) {
           $model = new ByPartNumberModel();
           $model->dal = new PlanningDALv1();
       }
       parent::__construct($routeParams, $model);
   }

   /**
    * POST
    * Get the data for planning a day by part number
    * Parameters are:
    *    lineId
    *    date
    * 
    * @return void
    */
   public function indexAction()
   {
      $data = APIAssertions::assertPost();
      if (!isset($data->lineId) || !isset($data->date)) {
         $ex = new HttpBadRequestException("Invalid parameters");
         if (!isset($data->lineId)){
               $ex->errors[] = "Missing lineId";
         } 
         if (!isset($data->date)){
            $ex->errors[] = "Missing date";
         } 
         throw $ex;
      }

      $profileId = $this->getProfileIdOrThrowException();
      $result = $this->model->getPartNumberPlan($profileId, $data->lineId, $data->date);
      $success = !empty($result);
      $errors = $success ? [] : ['No plan found'];
      $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
      $response->output();
   }

   /**
    * POST
    * Gets the routing for a partnumber
    * Post data must contain the partnumber, 8 digits, no index, e.g. { "partNumber" : "12345678" }
    * @return void
    */
   public function routingAction()
   {
      $data = APIAssertions::assertPost();
      if (!isset($data->partNumber)) {
         $ex = new HttpBadRequestException("Invalid parameters");
         if (!isset($data->partNumber)){
               $ex->errors[] = "Missing partNumber";
         } 
         throw $ex;
      }

      $result = $this->model->getRouting($data->partNumber);
      $success = !empty($result);
      $errors = $success ? [] : ['Part number not found'];
      $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
      $response->output();
   }

   /**
    * Saves the part number plan
    * 
    * @return void
    */
   public function saveAction()
   {
      $doNotDecodeJson = false;
      $json = APIAssertions::assertPost($doNotDecodeJson);
      if (is_null($json)) {
         $ex = new HttpBadRequestException("Invalid parameter");
         throw $ex;
      }
      $profileId = $this->getProfileIdOrThrowException();

      $result = $this->model->savePartNumberPlan($profileId, $json);
      $success = !empty($result);
      $errors = $success ? [] : ['Cannot save for uknown reason. Try again later'];

      $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
      $response->output();
   }
}