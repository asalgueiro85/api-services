<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require '../Slim/Slim.php';
require '../Slim/RedBean/rb.php';
require '../Functions.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */

R::setup('pgsql:host=192.168.10.1;dbname=cbook_crm_db','cbook_crm_usr','ZyAua6MZN9Sx8Kyw');
R::freeze(true);

$app = new \Slim\Slim();

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */

//// GET route
//$app->get(
//    '/',
//    function () {
//        $template = <<<EOT
//
//EOT;
//        echo $template;
//    }
//);

$app->post('/search', function () use ($app) {
        // get and decode JSON request body
        $request = $app->request();
        $body = $request->getBody();
        $input = json_decode($body);
        $app->response()->header('Content-Type', 'application/json');
    $indoor = $input->indoor;
    $beActive = $input->be_active;
    $interestingPlaces = $input->interesting_place;
//    $location = $request->request->get('location', null);
//    $location = !empty($location) ? explode(';', $location) : array();

    if(empty($be_actives) && empty($interestingPlaces) && empty($indoor)){
        $data_response = array(
            'result' => 0,
            'countries' => [],
            'cities' => [],
            'zones' => []
        );
        $response = new JsonResponse($data_response);
        return $response;
    }

    $func = new Functions();

    $data =$func->searchResult($indoor, $beActive, $interestingPlaces, array());

    if (count($data) > 0) {
        $data = json_decode($data[0]);
        if (is_null($data)) {
            $data = array();
        }
    }

    $zones = array();
    $countries = array();
    $cities = array();

    $countries_dic = array();
    $countries_index = 0;

    $cities_dic = array();
    $cities_index = 0;

    foreach ($data as $record) {
        if (!array_key_exists($record->country_id, $countries_dic)) {
            $countries_dic[$record->country_id] = $countries_index;
            $countries_index += 1;

            $countries[] = array(
                'count' => 1,
                'id' => $record->country_id,
                'lat' => $record->lat_country,
                'lng' => $record->lng_country
            );
        } else {
            $countries[$countries_dic[$record->country_id]]['count'] += 1;
        }

        $zones[] = array(
            'id' => $record->zone_id,
            'lat' => $record->lat_zone,
            'lng' => $record->lng_zone,
            //                'url' => $assets->getUrl('media/images/' . $record->url)
        );
    }

    $data_response = array(
        'result' => count($zones),
        'countries' => $countries,
        'cities' => $cities,
        'zones' => $zones
    );
    echo json_encode($data_response);

//    $response = new JsonResponse($data_response);
//    return $response;




    });

// POST route
$app->post(
    '/post',
    function () {
        echo 'This is a POST route';
    }
);

// PUT route
$app->put(
    '/put',
    function () {
        echo 'This is a PUT route';
    }
);

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete(
    '/delete',
    function () {
        echo 'This is a DELETE route';
    }
);

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
