<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

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

$publicRoute = 'headerPublic|footerPublic';
$authRoute = 'headerAuth|footerAuth'; // this is default, check Controller.php -> needs to be refactored
$shopfloorRoute = 'headerShopfloor|footerShopfloor';

/**
 * Routing
 */
$application = new HourlyBoardApplication();
$router = $application->getRouter("Management");

$allowedMethods = ["GET", "POST", "OPTIONS"];

$router->addHttpGetPost('', ['controller' => 'Users\ProfileController', 'action' => 'dashboard', 'feature'=>'management.profile', 'embed' => $authRoute]);

$router->addHttpGetPost('profile', ['controller' => 'Users\ProfileController', 'action' => 'profile', 'feature'=>'management.profile', 'embed' => $authRoute]);
$router->addHttpGetPost('profile/change-password', ['controller' => 'Users\ProfileController', 'action' => 'changeUserPassword', 'feature'=>'management.profile.change-password', 'embed' => $authRoute]);
$router->addHttpGetPost('settings/general', ['controller' => 'Settings\SettingsController', 'action' => 'general', 'feature'=>'management.settings.general', 'embed' => $authRoute]);
$router->addHttpGetPost('settings/routings-per-pn', ['controller' => 'Settings\RoutingsPerPnController', 'action' => 'routingsPerPn', 'feature'=>'management.settings.routings-per-pn', 'embed' => $authRoute]);
$router->addHttpGetPost('settings/rights', ['controller' => 'Users\RightsController', 'action' => 'index', 'feature'=>'management.settings.rights', 'embed' => $authRoute]);
$router->addHttpGetPost('users', ['controller' => 'Users\UsersController', 'action' => 'index', 'feature'=>'management.users', 'embed' => $authRoute]);
$router->addHttpGetPost('users/add', ['controller' => 'Users\UsersController', 'action' => 'add', 'feature'=>'management.users.add', 'embed' => $authRoute]);
$router->addHttpGetPost('users/edit/{userId:\d+}', ['controller' => 'Users\UsersController', 'action' => 'edit', 'feature'=>'management.users.edit', 'embed' => $authRoute]);
$router->addHttpGetPost('users/delete/{profileId:\d+}', ['controller' => 'Users\UsersController', 'action' => 'delete', 'feature'=>'management.users.delete', 'embed' => $authRoute]);
$router->addHttpGetPost('production-lines', ['controller' => 'ProductionLines\ProductionLinesController', 'action' => 'index', 'feature'=>'management.settings.production-lines', 'embed' => $authRoute]);
$router->addHttpGetPost('monitors', ['controller' => 'Monitors\MonitorsController', 'action' => 'index', 'feature'=>'management.monitors', 'embed' => $authRoute]);
$router->addHttpGetPost('monitors/add', ['controller' => 'Monitors\AddMonitor\AddMonitorController', 'action' => 'index', 'feature'=>'management.monitors.add', 'embed' => $authRoute]);
$router->addHttpGetPost('monitors/edit/{monitorId:\d+}', ['controller' => 'Monitors\EditMonitor\EditMonitorController', 'action' => 'index', 'feature'=>'management.monitors.edit', 'embed' => $authRoute]);
$router->addHttpGetPost('monitors/delete/{monitorId:\d+}', ['controller' => 'Monitors\MonitorsController', 'action' => 'delete', 'feature'=>'management.monitors.delete', 'embed' => $authRoute]);

$router->addHttpGetPost('shopfloor/{monitorId:\d+}', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'index', 'feature'=>'management.shopfloor', 'embed' => $shopfloorRoute]);
$router->addHttpGetPost('shopfloor/save-comment', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'saveCommentAction', 'feature'=>'management.shopfloor', 'embed' => $shopfloorRoute]);
$router->addHttpGetPost('shopfloor/save-escalated', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'saveEscalatedToAction', 'feature'=>'management.shopfloor', 'embed' => $shopfloorRoute]);
$router->addHttpGetPost('shopfloor/signoffhour', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'signOffHourAction', 'feature'=>'management.shopfloor', 'embed' => $shopfloorRoute]);
$router->addHttpGetPost('shopfloor/signoffshift', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'signOffShiftAction', 'feature'=>'management.shopfloor', 'embed' => $shopfloorRoute]);
$router->addHttpGetPost('shopfloor/downtime-minutes/{targetHourlyId:\d+}', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'downtimeMinutes', 'feature'=>'management.shopfloor', 'embed' => $shopfloorRoute]);

$router->addHttpGetPost('api/v1/shopfloor/{monitorId:\d+}', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'billboardXml', 'feature'=>'management.shopfloor', 'embed' => $shopfloorRoute]);


$router->addHttpGetPost('shopfloor/savedowntime', ['controller' => 'Shopfloor\ShopfloorController', 'action' => 'saveDowntimeAction', 'feature'=>'management.users', 'embed' => $shopfloorRoute]);

$router->addHttpGetPost('planning/breaks', ['controller' => 'Breaks\BreaksController', 'action' => 'index', 'feature'=>'management.planning.breaks', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/breaks/save', ['controller' => 'Breaks\BreaksController', 'action' => 'save', 'feature'=>'management.planning.breaks.save', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/datasets', ['controller' => 'Planning\Datasets\DatasetsController', 'action' => 'index', 'feature'=>'management.planning.datasets', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/datasets/billboard-update', ['controller' => 'Planning\Datasets\DatasetsController', 'action' => 'billboardUpdate', 'feature'=>'management.planning.datasets', 'embed' => $authRoute]);

$router->addHttpGetPost('planning/by-day', ['controller' => 'Planning\ByDay\ByDayController', 'action' => 'index', 'feature'=>'management.planning.by-day', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/by-day/search', ['controller' => 'Planning\ByDay\ByDayController', 'action' => 'search', 'feature'=>'management.planning.by-day', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/by-day/save', ['controller' => 'Planning\ByDay\ByDayController', 'action' => 'save', 'feature'=>'management.planning.by-day', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/by-partnumber', ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'index', 'feature'=>'management.planning.by-partnumber', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/by-partnumber/upload', ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'upload', 'feature'=>'management.planning.by-partnumber', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/by-partnumber/save', ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'save', 'feature'=>'management.planning.by-partnumber', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/by-partnumber/refresh-routing', ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'refreshRouting', 'feature'=>'management.planning.by-partnumber', 'embed' => $authRoute]);
$router->addHttpGetPost('planning/by-partnumber/load', ['controller' => 'Planning\ByPartNumber\ByPartNumberController', 'action' => 'load', 'feature'=>'management.planning.by-partnumber', 'embed' => $authRoute]);

//reports

$router->addHttpGetPost('reports/historical-billboard', ['controller' => 'Reports\HistoricalBillboardController', 'action' => 'index', 'feature'=>'management.profile', 'embed' => $authRoute]);
$router->addHttpGetPost('reports/historical-billboard/load', ['controller' => 'Reports\HistoricalBillboardController', 'action' => 'load', 'feature'=>'management.profile', 'embed' => $shopfloorRoute]);

// Login
$router->addHttpGetPost('login', ['controller' => 'Login\LoginController', 'action' => 'index', 'feature'=>'management.login', 'embed' => $publicRoute]);
$router->addHttpGetPost('login/process', ['controller' => 'Login\LoginController', 'action' => 'loginProcess', 'feature'=>'management.login', 'embed' => $publicRoute]);
$router->addHttpGetPost('set-language/{languageCode:[a-z][a-z]}', ['controller' => 'Users\UsersController', 'action' => 'setLanguage', 'feature'=>'management.profile', 'embed' => $publicRoute]);
$router->addHttpGetPost('logout', ['controller' => 'Login\LoginController', 'action' => 'logout', 'feature'=>'management.logout']);
$router->dispatch($_SERVER['QUERY_STRING']);