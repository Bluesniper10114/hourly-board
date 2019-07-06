<?php
namespace API\Planning\ByDay;
use API\APIAssertions;
use Core\HttpResponse;
use DAL\LayoutDALv1;
use DAL\PlanningDALv1;
use Core\Exceptions\HttpBadRequestException;

/**
 * Support for the daily planning form
 */
class ByDayController extends \API\AuthenticatedController
{
   /** @var ByDayModel $model The model handling planning by day API */
   protected $model;
   
    /**
     * @inheritDoc
     */
    public function __construct($routeParams, $model = null)
    {
      if (!isset($model)) {
         $model = new ByDayModel();
         $model->dal = new PlanningDALv1();
      }
      parent::__construct($routeParams, $model);
   }

   /**
     * Gets line by line content for the daily planning form
     * @return void
     */
    public function indexAction()
    {
      $data = APIAssertions::assertPost();
      if (!isset($data->tags)) {
         $ex = new HttpBadRequestException("Invalid parameters");
         if (!isset($data->tags)){
               $ex->errors[] = "Missing tags";
         } 
         throw $ex;
      }
      $profileId = $this->getProfileIdOrThrowException();      

      $result = $this->model->getByDayPlan($profileId, "AUDI");
      $success = !empty($result);
      $errors = $success ? [] : ['No result for this tag'];   
      $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
      $response->output();
    }

    /**
     * Saves the daily planning
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

      $result = $this->model->saveByDayPlan($profileId, $json);
      $success = !empty($result);
      $errors = $success ? [] : ['Cannot save for uknown reason. Try again later'];
      
      $response = new HttpResponse($result, $success, HttpResponse::HTTP_OK, $errors);
      $response->output();
    }

    /**
     * Gets the two weeks for the header
     * @return void
     */
    public function headerAction()
    {
        APIAssertions::assertGet();

        $tags = $this->model->getWeeks();
        $success = !empty($tags);
        $errors = $success ? [] : ["No tags found"];
        $response = new HttpResponse($tags, $success, HttpResponse::HTTP_OK, $errors);
        $response->output();
    }
}