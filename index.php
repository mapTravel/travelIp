<?php
define("CSV",dirname(__FILE__).DIRECTORY_SEPARATOR.'csv');
define("GEOIP_URL",'http://freegeoip.net/');
define("GEOCODE_URL",'https://maps.googleapis.com/maps/api/geocode/');

define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','test_work_map_travel');
define('DB_HOST','localhost');

require __DIR__ . '/protected/start.php';

use Routing\Router;
use Routing\MatchedRoute;
use Routing\Request;

try {
	$request = new Request();


    $router = new Router(GET_HTTP_HOST());
    $router->add('map', '/', 'AppController:mapAction');
	$router->add('user_import', '/user_import', 'AppController:user_importAction');

	$router->add('ajaxGetAllUsers', '/ajax/get_all_users', 'AjaxController:get_all_usersAction','POST');
	$router->add('ajaxGetAllPlaces', '/ajax/get_all_places', 'AjaxController:get_all_placesAction','POST');
	$router->add('ajaxImportFile', '/ajax/import_file', 'AjaxController:import_fileAction','POST');
	$router->add('ajaxPopular', '/ajax/popular', 'AjaxController:popularAction','POST');



    $router->add('user', '/user/(id:num)', 'AppController:userAction');
    $router->add('keyword', '/keyword', 'AppController:ebayActionPost','POST');



    $route = $router->match(GET_METHOD(), GET_PATH_INFO());

    if (is_null($route)) {
        $route = new MatchedRoute('AppController:error404Action');
    }

    list($class, $action) = explode(':', $route->getController(), 2);

    call_user_func_array(array(new $class($router), $action), $route->getParameters());

} catch (Exception $e) {

	$pos = strripos($request->getPathInfo(), '/ajax');
	if($pos !== false){

		$data['status'] = 'error';
		$data['msg'] = $e->getMessage();

		echo json_encode($data);
	}else{
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		echo $e->getMessage();
		//echo $e->getTraceAsString();
	}
    exit;
}