<?php
// API Key @ upcdatabase.org
$config["upcDatabaseApiKey"] = "aee35a3fd591aabd219bb1aa0a1746f8";

// API URL @ upcdatabase.org
$config["upcDatabaseUrl"] = "http://api.upcdatabase.org/json/" . $config["upcDatabaseApiKey"] . "/";

// Products JSON
$config["productsJson"] = dirname(dirname(__FILE__)) . "/data/productsList.json";

// Basket JSON
$config["clientsBasketJson"] = dirname(dirname(__FILE__)) . "/data/clientsBasket.json";

// Brands JSON
$config["brandsJson"] = dirname(dirname(__FILE__)) . "/data/brandsList.json";