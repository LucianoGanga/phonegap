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
    $searchResult = $bp->searchProductByText($text);
    
    $responseArray["status"] = $searchResult["status"];
    
    if ($searchResult["status"] === "success") {
        $responseArray["data"] = $searchResult["data"];
    }

    sendResponse($responseArray);
}


/*
 * Metodo: getConfigData
 * Parametros: -
 */
if (get("method") && get("method") === "getProductInfo") {
    echo $upcCode = get("upcCode");

    $bp->getProductInfo($upcCode);
    echo json_encode($configData);
}

/*
 * Metodo: newClient
 * Parametros: 
 *      $_GET["data"]
 *      $_GET["clientName"]
 */
if (get("method") && get("method") === "newClient") {
    $clientData = json_decode(get("data"), true);
    $clientName = get("clientName");
    $arr = array();
    if ($clientName !== "") {
        // Actualizo el JSON que hace de base de datos
        $bp->modifyClientsList("add", $clientName, $clientData);
        // Genero el YML para este cliente
        $bp->processClientsYml("add", $clientName, $clientData);
        $responseArray["status"] = "success";
    } else {
        $arr["status"] = "error";
    }
    echo json_encode($arr);
}

exit();
