<?php

use Common\Filters\AuthenticationFilter;
use Common\Filters\FeatureFilter;
use Core\HttpResponse;
use Core\Exceptions\HttpInternalServerErrorException;
use Core\Exceptions\HttpUnauthorizedAccessException;
use Core\Exceptions\HttpBadRequestException;

setlocale(LC_ALL, 'en_US.UTF-8');


if (!session_id())
{
    @session_start();
}
/**
 * Composer
 */
require '../vendor/autoload.php';

\Settings\Config::init();

if (getSetting('SHOW_ERRORS')) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

/**
 * Routing
 */
$application = new HourlyBoardApi();
$router = $application->getRouter("API");
$version = "v1";

// Monitors
$router->addHttpGet("$version/board/footer", ['controller' => 'BoardController', 'action' => 'getFooter', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/monitors/monitorId", ['controller' => 'Monitors\MonitorsController', 'action' => 'getMonitorId', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/monitors/all", ['controller' => 'Monitors\MonitorsController', 'action' => 'getMonitors', 'feature'=>'management.shopfloor', 'embed' => '']);

// Shopfloor
$router->addHttpGet("$version/shopfloor/{monitorId:\d+}", ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'index', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/shopfloor/comments", ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'getComments', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/shopfloor/escalations", ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'getEscalations', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/shopfloor/comments/save", ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'saveComments', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/shopfloor/escalations/save", ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'saveEscalations', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/shopfloor/signoffhour", ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'signOffHour', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/shopfloor/signoffshift", ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'signOffShift', 'feature'=>'management.shopfloor', 'embed' => '']);

// Downtime
$router->addHttpGet("$version/downtime/{hourlyId:\d+}", ['controller' => 'Downtime\DowntimeController', 'action' => 'index', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/downtime/reasons", ['controller' => 'Downtime\DowntimeController', 'action' => 'getDowntimeReasons', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/downtime/save", ['controller' => 'Downtime\DowntimeController', 'action' => 'saveDowntimeReasons', 'feature'=>'management.shopfloor', 'embed' => '']);

// Login
$router->addHttpPost("$version/login", ['controller' => 'Login\LoginController', 'action' => 'loginProcess', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/logout", ['controller' => 'Login\LoginController', 'action' => 'logout', 'feature'=>'management.shopfloor', 'embed' => '']);

// Planning overview
$router->addHttpPost("$version/planning/datasets", ['controller' => 'Planning\Datasets\DatasetsController', 'action' => 'datasets', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/planning/dates", ['controller' => 'Planning\Datasets\DatasetsController', 'action' => 'dates', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/planning/lines", ['controller' => 'Planning\PlanningController', 'action' => 'lines', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/planning/datasets/activate-billboard", ['controller' => 'Planning\Datasets\DatasetsController', 'action' => 'activateBillboard', 'feature'=>'management.shopfloor', 'embed' => '']);

// Planning by day
$router->addHttpGet("$version/planning/tags", ['controller' => 'Planning\PlanningController', 'action' => 'tags', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/planning/byday", ['controller' => 'Planning\ByDay\ByDayController', 'action' => 'index', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/planning/byday/save", ['controller' => 'Planning\ByDay\ByDayController', 'action' => 'save', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/planning/byday/header", ['controller' => 'Planning\ByDay\ByDayController', 'action' => 'header', 'feature'=>'management.shopfloor', 'embed' => '']);

// Planning by partNumber
$router->addHttpPost("$version/planning/bypn", ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'index', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpGet("$version/planning/full-lines", ['controller' => 'Planning\PlanningController', 'action' => 'allLinesCellsAndMachines', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/planning/bypn/routing", ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'routing', 'feature'=>'management.shopfloor', 'embed' => '']);
$router->addHttpPost("$version/planning/bypn/save", ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'save', 'feature'=>'management.shopfloor', 'embed' => '']);

try
{
    $router->dispatch($_SERVER['QUERY_STRING']);
}
catch(HttpUnauthorizedAccessException $apiEx)
{
    $response = $apiEx->getHttpResponse();
    $response->output();
}
catch(HttpBadRequestException $apiEx)
{
    $response = $apiEx->getHttpResponse();
    $response->output();
}
catch(HttpInternalServerErrorException $apiEx)
{
    $response = $apiEx->getHttpResponse();
    $response->output();
}
catch(\Exception $ex)
{
    $exception = new HttpInternalServerErrorException($ex->getMessage(), $ex->getCode(), $ex);
    throw $exception; 
}
