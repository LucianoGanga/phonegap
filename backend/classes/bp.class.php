<?php

class bp {

    public function __construct() {
        
    }

    private function filter_by_value($array, $index, $value) {
        $newarray = array();
        $temp = array();
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                if (isset($array[$key][$index])) {
                    $temp[$key] = $array[$key][$index];

                    if (stripos($temp[$key], $value) !== false) {
                        $newarray[$key] = $array[$key];
                    }
                }
            }
        }
        return $newarray;
    }

    /*
     * Obtiene contenido de una URL
     */

    private function getUrlData($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /*
     * Devuelve un arreglo con el listado de productos
     */

    public function getProductsList() {
        global $config;

        $json = file_get_contents($config["productsJson"]);
        $products = json_decode($json, true);

        return $products;
    }

    /*
     * Devuelve un arreglo con el listado de productos que se ajusten a una busqueda
     */

    public function searchProductByText($text) {
        $products = $this->getProductsList();

        $searchs = array(
            "0" => $this->filter_by_value($products, "categoria3", $text),
            "1" => $this->filter_by_value($products, "producto", $text)
        );

        $searchResult = call_user_func_array('array_merge', $searchs);

        $responseArray = array("status" => "success", "data" => array());
        foreach ($searchResult as $key => $product) {
            $responseArray["data"][$product["id"]] = $product;
        }

        return $responseArray;
    }

    /*
     * Dado un determinado codigo de barras, consulta informacion en upcdatabase.org
     * Ejemplo: 
     *      http://localhost/phonegap/backend/?method=getUpcDatabaseCodeInfo&code=7792200000210
     */

    public function getUpcDatabaseCodeInfo($code) {
        global $config;

        $url = $config["upcDatabaseUrl"] . $code;
        $urlContent = $this->getUrlData($url);

        return json_decode(htmlspecialchars_decode($urlContent), true);
    }

    /*
     * Sube a upcdatabase.org un codigo de barras con su descripcion
     */

    public function submitProductInfo($productUPC) {
        global $config;
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'http://api.upcdatabase.org/submit/curl.php',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'upc' => '0000000000000',
                'mrsp' => '0.00',
                'apikey' => $config["upcApiKey"],
                'title' => 'Title of product',
                'alias' => 'Title alias',
                'description' => 'Optional lengthy description of product.',
                'unit' => 'Per case'
        ))));
        $server_output = curl_exec($ch);
        curl_close($ch);
        var_dump($server_output);
        if ($server_output == 'OK') {
            echo 'Everything went alright!';

            return $server_output;
        } else {
            echo 'An error occured: ', $server_output;
        }
    }

    /*
     * Dado un determinado codigo de producto (proveniente del codigo de barras), devuelve datos del producto correspondiente
     */

    public function getProductByCode($productCode) {
        $products = $this->getProductsList();

        $searchs = array(
            "0" => $this->filter_by_value($products, "code", $productCode)
        );
        $searchResult = call_user_func_array('array_merge', $searchs);

        $responseArray = array("status" => "success", "data" => array());
        foreach ($searchResult as $key => $product) {
            $responseArray["data"] = $product;
        }

        return $responseArray;
    }

    /*
     * Busca un producto por su ID
     */

    public function getProductByKey($key, $text) {
        $products = $this->getProductsList();
        $searchResult = $this->filter_by_value($products, "id", $text);

        $responseArray = array("status" => "success", "data" => array());
        foreach ($searchResult as $key => $product) {
            $responseArray["data"] = $product;
        }
        
        return $responseArray;
    }

    public function getCartData($userId) {
        global $config;

        $json = file_get_contents($config["clientsBasketJson"]);
        $cartData = json_decode($json, true);

        $itemsQty = 0;
        foreach ($cartData[$userId]["itemsList"] as $itemId => $itemData) {
            $productData = $this->getProductByKey("id", strval($itemId))["data"];
            $cartData[$userId]["itemsList"][$itemId] = array_merge($productData, $itemData);
            $itemsQty += $itemData["qty"];
        }
        $cartData[$userId]["itemsQty"] = $itemsQty;
        return $cartData[$userId];
    }

    public function getCartItems($userId) {
        $cartData = $this->getCartData($userId);

        return $cartData["itemsList"];
    }

    public function getCartItemsQty($userId) {
        $cartData = $this->getCartData($userId);
        return $cartData["itemsQty"];
    }

    public function addToCart($userId, $productId, $qty) {
        
    }

}
