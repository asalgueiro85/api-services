<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require '../../Slim/Slim.php';
require '../../Slim/RedBean/rb.php';
require 'DBFunctions.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */

R::setup('mysql:host=127.0.0.1;dbname=andohappy_db', 'root', '');
R::freeze(true);

$app = new \Slim\Slim();

//route get for country
$app->get('/country', function () use ($app) {
    $request = $app->request();
    $id = $request->get('id');
    $country = $request->get('country');
    $locale = $request->get('locale');
    $q = '';
    $join = '';
    $token = $request->get('token');
    $app->response()->header('Content-Type', 'application/json');

    if ($token != 'ando_happy') {
        echo '{"error":{"code":' . '403' . ',"text":' . 'Server failed to authenticate the request. Make sure the value of the Authorization header is formed correctly including the signature.' . '}}';
        return;
    } else {
        $q = 'WHERE a.active = 1 ';
        if (!empty($id) && is_numeric($id)) {
            $q .= " AND a.id = '" . $id . "'";
        }

        if (!empty($country)) {
            $q .= " AND b.content = '" . $country . "'";
            $join = ' inner join country_translation b on a.id=b.foreign_key ';
        }

        $sql = "SELECT DISTINCT a.id,a.iso_code,a.lat,a.lng FROM country a ";
        if ($join != '')
            $sql .= $join;
        $sql .= $q . " ORDER BY id ASC";

        try {
            $db = DBFunctions::getConnection();
            $stmt = $db->query($sql);
            $countries = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            for ($i = 0; $i < count($countries); $i++) {


                if ($locale == ''){
                    $temp =  R::getAll( 'SELECT locale, content FROM country_translation WHERE foreign_key = :id',
                        [
                        ':id' => $countries[$i]->id
                        ]
                        );
                }
                
                else{

                 $temp =  R::getAll( 'SELECT locale, content FROM country_translation WHERE foreign_key = :id AND locale = :locale',
                    [
                    ':id' => $countries[$i]->id,
                    'locale' => $locale
                    ]
                    );
             }
             $countries[$i]->translations = $temp;
         }
         echo '{"countries": ' . json_encode($countries) . '}';
     } catch (PDOException $e) {
//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }

}


});

//route get for state
$app->get('/state', function () use ($app) {
    $request = $app->request();
    $id = $request->get('id');
    $country = $request->get('country');
    $country_id = $request->get('country_id');
    $state = $request->get('state');
    $locale = $request->get('locale');
    $q = '';
    $join = '';
    $token = $request->get('token');
    $app->response()->header('Content-Type', 'application/json');

    if ($token != 'ando_happy') {
        echo '{"error":{"code":' . '403' . ',"text":' . 'Server failed to authenticate the request. Make sure the value of the Authorization header is formed correctly including the signature.' . '}}';
        return;
    } else {
        $q = 'WHERE a.active = 1 ';
        if (!empty($id) && is_numeric($id)) {
            $q .= " AND a.id = '" . $id . "'";
        }

        if (!empty($state)) {
            $q .= " AND t.content = '" . $state . "'";
            $join .= ' inner join state_translations t on a.id=t.foreign_key ';
        }

        if (!empty($country)) {
            $q .= " AND c.content = '" . $country . "'";
            $join .= ' inner join country b on a.country_id=b.id';
            $join .= ' inner join country_translation c on b.id=c.foreign_key ';
        }
        if (!empty($country_id) && is_numeric($country_id)) {

            if (empty($country)) {
                $q .= " AND b.id = '" . $country_id . "'";
                $join .= ' inner join country b on a.country_id=b.id';
            } else {
                $q .= " AND b.id = '" . $country_id . "'";
            }


        }

        $sql = "SELECT DISTINCT a.id,a.lat,a.lng FROM state a ";
        if ($join != '')
            $sql .= $join;
        $sql .= $q . " ORDER BY id ASC";
        try {
            $db = DBFunctions::getConnection();
            $stmt = $db->query($sql);
            $states = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            for ($i = 0; $i < count($states); $i++) {


                if ($locale == ''){
                    $temp =  R::getAll( 'SELECT locale, content FROM state_translations WHERE foreign_key = :id',
                        [
                        ':id' => $states[$i]->id
                        ]
                        );
                }
                
                else{

                 $temp =  R::getAll( 'SELECT locale, content FROM state_translations WHERE foreign_key = :id AND locale = :locale',
                    [
                    ':id' => $states[$i]->id,
                    'locale' => $locale
                    ]
                    );
             }
             $states[$i]->translations = $temp;
         }
         echo '{"states": ' . json_encode($states) . '}';
     } catch (PDOException $e) {
//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }

}


});

//route get for category
$app->get('/category', function () use ($app) {
    $request = $app->request();
    $id = $request->get('id');
    $all = $request->get('all');
    $locale = $request->get('locale');
    $token = $request->get('token');

//    $token = 'ando_happy';
//    $all = true;
//    $locale = '';
    $q = '';
    $join = '';
    $app->response()->header('Content-Type', 'application/json');

    if ($token != 'ando_happy') {
        echo '{"error":{"code":' . '403' . ',"text":' . 'Server failed to authenticate the request. Make sure the value of the Authorization header is formed correctly including the signature.' . '}}';
        return;
    } else {
        $q = 'WHERE a.id is not null';
        if (empty($id) || $all == true) {
            $q .= " AND a.parent is null";
        }
        if (!empty($id)) {
            $q = "WHERE a.id is not null AND a.parent = '" . $id . "'";
        }

        $sql = "SELECT DISTINCT a.id, a.isleaf, a.media_id, a.history, a.parent FROM category a ";
        if ($join != '')
            $sql .= $join;
        $sql .= $q . " ORDER BY id ASC";
        try {
            $db = DBFunctions::getConnection();
            $stmt = $db->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            for ($i = 0; $i < count($categories); $i++) {
                if ($locale == ''){
                    $temp =  R::getAll( 'SELECT locale, content FROM category_translations WHERE foreign_key = :id',
                        [
                        ':id' => $categories[$i]->id
                        ]
                        );
                }
                
                else{

                 $temp =  R::getAll( 'SELECT locale, content FROM category_translations WHERE foreign_key = :id AND locale = :locale',
                    [
                    ':id' => $categories[$i]->id,
                    'locale' => $locale
                    ]
                    );
             }

             $categories[$i]->translations = $temp;

             if ($all == true) {
                $sql = "SELECT DISTINCT a.id, a.isleaf, a.media_id, a.history, a.parent FROM category a WHERE a.parent = '" . $categories[$i]->id . "'";

                $db = DBFunctions::getConnection();
                $stmt = $db->query($sql);
                $childTemp = $stmt->fetchAll(PDO::FETCH_OBJ);
                $db = null;
                $tempC = null;
                for ($j = 0; $j < count($childTemp); $j++) {
                    if ($locale == '') {
                        $tempC =  R::getAll( 'SELECT locale, content FROM category_translations WHERE foreign_key = :id',
                            [
                            ':id' => $childTemp[$j]->id
                            ]
                            );
                    } else {
                        $tempC =  R::getAll( 'SELECT locale, content FROM category_translations WHERE foreign_key = :id AND locale = :locale',
                            [
                            ':id' => $childTemp[$j]->id,
                            'locale' => $locale
                            ]
                            );
                    }

                    $childTemp[$j]->translations = $tempC;
                }

                $categories[$i]->child = $childTemp;


            }
        }

        echo '{"categories": ' . json_encode($categories) . '}';
    } catch (PDOException $e) {
//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }

}


});

//route get for places
$app->get('/places', function () use ($app) {
    $request = $app->request();
    $id = $request->get('id');
    $country_id = $request->get('country_id');
    $city_id = $request->get('city_id');
    $state_id = $request->get('state_id');
    $category_id = $request->get('category_id');
    $locale = $request->get('locale');
    $q = '';
    $join = '';
    $token = $request->get('token');
    $app->response()->header('Content-Type', 'application/json');

    if ($token != 'ando_happy') {
        echo '{"error":{"code":' . '403' . ',"text":' . 'Server failed to authenticate the request. Make sure the value of the Authorization header is formed correctly including the signature.' . '}}';
        return;
    } else {
        $q = ' WHERE p.active = 0 ';
        if (!empty($id) && is_numeric($id)) {
            $q .= " AND p.id = '" . $id . "'";
        }
        if (!empty($category_id) && is_numeric($category_id)) {
            $q .= " AND p.category_id = '" . $category_id . "'";
        }

        if (!empty($country_id)) {
            $q .= " AND c.id = '" . $country_id . "'";
            $join = ' inner join city ct on p.city_id = ct.id
            inner join state s on ct.state_id = s.id 
            inner join country c on s.country_id = c.id';
        }
        if (!empty($city_id)) {
            $q .= " AND ct.id = '" . $city_id . "'";
            $join = ' inner join city ct on p.city_id = ct.id';
        }
        if (!empty($state_id)) {
            $q .= " AND s.id = '" . $state_id . "'";
            $join = ' inner join city ct on p.city_id = ct.id
            inner join state s on ct.state_id = s.id';
        }

        $sql = "SELECT DISTINCT p.id,p.phone,p.email,p.lat,p.lng,p.category_id,p.city_id FROM places p ";
        if ($join != '')
            $sql .= $join;
        $sql .= $q . " ORDER BY id ASC";
//print_r($sql);die;
        try {
            $db = DBFunctions::getConnection();
            $stmt = $db->query($sql);
            $places = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            for ($i = 0; $i < count($places); $i++) {


                if ($locale == ''){
                    $temp =  R::getAll( 'SELECT locale, field, content FROM places_translations WHERE foreign_key = :id',
                        [
                        ':id' => $places[$i]->id
                        ]
                        );
                }
                
                else{

                 $temp =  R::getAll( 'SELECT locale, field, content FROM places_translations WHERE foreign_key = :id AND locale = :locale',
                    [
                    ':id' => $places[$i]->id,
                    'locale' => $locale
                    ]
                    );
             }

             $link =  R::getAll( 'SELECT id, url, type FROM link l inner join places_link pl on l.id = pl.link_id WHERE places_id = :id',
                [
                ':id' => $places[$i]->id
                ]
                );
             $media =  R::getAll( 'SELECT id, url, type FROM media m inner join places_media pm on m.id = pm.media_id WHERE places_id = :id',
                [
                ':id' => $places[$i]->id
                ]
                );

             $places[$i]->translations = $temp;
             $places[$i]->link = $link;
             $places[$i]->media = $media;
         }
         echo '{"places": ' . json_encode($places) . '}';
     } catch (PDOException $e) {
//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }

}


});

//route get for country
$app->get('/information', function () use ($app) {
    $request = $app->request();
    $id = $request->get('id');
    $country_id = $request->get('country_id');
    $type = $request->get('type');
    $locale = $request->get('locale');
    $q = '';
    $join = '';
    $token = $request->get('token');
    $app->response()->header('Content-Type', 'application/json');

    if ($token != 'ando_happy') {
        echo '{"error":{"code":' . '403' . ',"text":' . 'Server failed to authenticate the request. Make sure the value of the Authorization header is formed correctly including the signature.' . '}}';
        return;
    } else {
        $q = ' WHERE i.id is not null ';
        if (!empty($id) && is_numeric($id)) {
            $q .= " AND i.id = '" . $id . "'";
        }
        if (!empty($country_id) && is_numeric($country_id)) {
            $q .= " AND i.country_id = '" . $country_id . "'";
        }

        if (!empty($type)) {
            $q .= " AND i.type = '" . $type . "'";
        }

        $sql = "SELECT DISTINCT i.id,c.value_no_translations FROM information i inner join content c on i.id = c.information_id ";
        if ($join != '')
            $sql .= $join;
        $sql .= $q . " ORDER BY id ASC";
//print_r($sql);die;
        try {
            $db = DBFunctions::getConnection();
            $stmt = $db->query($sql);
            $informations = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            $my_sql_content = array();
            $my_sql_link = array();
            $my_sql_media = array();
            $temp_content_id = array();



           // print_r($informations);die;
            for ($i = 0; $i < count($informations); $i++) {


                if ($locale == ''){
                    $temp =  R::getAll( 'SELECT locale, content FROM information_translations WHERE foreign_key = :id',
                        [
                        ':id' => $informations[$i]->id
                        ]
                        );

                    $temp_content_id =  R::getAll( 'SELECT id FROM content WHERE information_id = :id',
                        [
                        ':id' => $informations[$i]->id
                        ]
                        ); 
                    $my_sql_content = array();
                    $my_sql_link = array();
                    $my_sql_media = array();
                    foreach($temp_content_id as $my_content){
                        $my_sql_content[] = 'foreign_key LIKE '."'".'%'.$my_content['id'].'%'."'";
                        $my_sql_link[] = 'pl.content_id LIKE '."'".'%'.$my_content['id'].'%'."'";
                        $my_sql_media[] = 'pm.content_id LIKE '."'".'%'.$my_content['id'].'%'."'";
                    }

                    $my_sql_content_result = 'SELECT * FROM content_translations WHERE '.implode(" OR ", $my_sql_content); 
//print_r($my_sql_content);die;
                    $temp_content =  R::getAll( $my_sql_content_result);
//print_r($temp_content);die;
                }
                
                else{

                 $temp =  R::getAll( 'SELECT locale, content FROM information_translations WHERE foreign_key = :id AND locale = :locale',
                    [
                    ':id' => $informations[$i]->id,
                    'locale' => $locale
                    ]
                    );
                 $temp_content =  R::getAll( 'SELECT locale, content FROM content_translations WHERE foreign_key = :id AND locale = :locale',
                    [
                    ':id' => $informations[$i]->content_id,
                    'locale' => $locale
                    ]
                    );


                 $temp_content_id =  R::getAll( 'SELECT id FROM content WHERE information_id = :id',
                    [
                    ':id' => $informations[$i]->id
                    ]
                    ); 

                 foreach($temp_content_id as $my_content){
                    $my_sql_content[] = 'foreign_key LIKE '."'".'%'.$my_content['id'].'%'."'";
                    $my_sql_link[] = 'pl.content_id LIKE '."'".'%'.$my_content['id'].'%'."'";
                    $my_sql_media[] = 'pm.content_id LIKE '."'".'%'.$my_content['id'].'%'."'";
                }

                $my_sql_content_result = 'SELECT * FROM content_translations WHERE locale = :locale'.implode(" OR ", $my_sql_content,
                    [
                    ':locale' => $locale
                    ]); 

                $temp_content =  R::getAll( $my_sql_content_result);



            }


            $my_sql_link_result = 'SELECT id, url, type FROM link l inner join content_link pl on l.id = pl.link_id WHERE '.implode(" OR ", $my_sql_link); 

            $link =  R::getAll( $my_sql_link_result);
            $my_sql_media_result = 'SELECT id, url, type FROM media m inner join content_media pm on m.id = pm.media_id WHERE '.implode(" OR ", $my_sql_media); 

            $media =  R::getAll( $my_sql_media_result);
//print_r($temp_content);die('asasas');
            $informations[$i]->translations = $temp;
            $informations[$i]->content_translations = $temp_content;

            $informations[$i]->link = $link;
            $informations[$i]->media = $media;
        }
      //  print_r($informations);die('asasas');
        echo '{"informations": ' . json_encode($informations) . '}';
    } catch (PDOException $e) {
//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }

}


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
