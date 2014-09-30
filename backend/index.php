<?php

require (dirname(__FILE__) . "/classes/bp.class.php");
require (dirname(__FILE__) . "/config/bp.config.php");

function get($value) {
    //$result = filter_input(INPUT_GET, $value, FILTER_SANITIZE_SPECIAL_CHARS);
    $result = filter_input(INPUT_GET, $value);
    return $result;
}

function post($value) {
    //$result = filter_input(INPUT_POST, $value, FILTER_SANITIZE_SPECIAL_CHARS);
    $result = filter_input(INPUT_POST, $value);
    return $result;
}

function sendResponse($response) {
    if (isset($_GET['callback'])) { // Si es una petición cross-domain
        header('Content-Type: application/javascript');
        echo $_GET['callback'] . '(' . json_encode($response) . ')';
    } else { // Si es una normal, respondemos de forma normal
        echo json_encode($response);
    }
    return;
}

$bp = new bp();


/*
 * Metodo: getProductsList
 * Descripción: Imprime en formato JSON una la lista de productos
 * Parametros: 
 *      -
 */
if (get("method") && get("method") === "getProductsList") {
    $responseArray = array();

    $responseArray["status"] = "success";
    $responseArray["data"] = $bp->getProductsList();

    sendResponse($responseArray);
}

/*
 * Metodo: searchProductByText
 * Descripción: Imprime en formato JSON una la lista de productos
 * Parametros: 
 *      -
 */
if (get("method") && get("method") === "searchProductByText") {
    $responseArray = array();
    $text = get("text");
    $queryResult = $bp->searchProductByText($text);

    $responseArray["status"] = $queryResult["status"];

    if ($queryResult["status"] === "success") {
        $responseArray["data"] = $queryResult["data"];
    }

    sendResponse($responseArray);
}



/*
 * Metodo: getProductByCode
 * Parametros: 
 *      $_GET["code"]
 */
if (get("method") && get("method") === "getProductByCode") {
    $code = get("code");

    $queryResult = $bp->getProductByCode($code);

    $responseArray["status"] = $queryResult["status"];

    if ($queryResult["status"] === "success") {
        $responseArray["data"] = $queryResult["data"];
    }

    sendResponse($responseArray);
}

/*
 * Metodo: getUpcDatabaseCodeInfo
 * Parametros: 
 *      $_GET["code"]
 */
if (get("method") && get("method") === "getUpcDatabaseCodeInfo") {
    $code = get("code");

    $queryResult = $bp->getUpcDatabaseCodeInfo($code);

    sendResponse($queryResult);
}

/*
 * Metodo: getCartData
 * Parametros: 
 *      $_GET["clientId"]
 */
if (get("method") && get("method") === "getCartData") {
    $clientId = get("clientId");

    $queryResult = $bp->getCartData($clientId);
    
    $responseArray = array(
        "status" => "success",
        "data" => $queryResult
    );

    sendResponse($responseArray);
}

/*
 * Metodo: getCartItems
 * Parametros: 
 *      $_GET["clientId"]
 */
if (get("method") && get("method") === "getCartItems") {
    $clientId = get("clientId");

    $queryResult = $bp->getCartItems($clientId);

    sendResponse($queryResult);
}

/*
 * Metodo: getCartItemsQty
 * Parametros: 
 *      $_GET["clientId"]
 */
if (get("method") && get("method") === "getCartItemsQty") {
    $clientId = get("clientId");

    $queryResult = $bp->getCartItemsQty($clientId);

    sendResponse($queryResult);
}

exit();
