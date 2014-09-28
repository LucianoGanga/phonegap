<?php

class bp {

    public function __construct() {
        
    }

    private function filter_by_value($array, $index, $value) {
        $newarray = array();
        $temp = array();
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];

                if (stripos($temp[$key], $value) !== false) {
                    $newarray[$key] = $array[$key];
                }
            }
        }
        return $newarray;
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
        global $config;

        $json = file_get_contents($config["productsJson"]);
        $products = json_decode($json, true);

        $searchs = array( 
            "0" => $this->filter_by_value($products, "categoria3", $text),
            "1" => $this->filter_by_value($products, "producto", $text)
            );

        $searchResult = call_user_func_array('array_merge', $searchs);
        
        $responseArray = array("status"=>"success", "data"=>array());
        foreach($searchResult as $key=>$product) {
            $responseArray["data"][$product["id"]] = $product;
        }
        
        return $responseArray;
    }

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

    public function getProductInfo($productUPC) {
        global $config;
        $ch = curl_init();

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
     * Agrega, edita o elimina un cliente del JSON
     */

    public function modifyClientsList($method, $clientName, $data = null) {
        $responseArray = array();
        $clientsList = $this->getClients();

        switch ($method) {
            case "add":
                if (isset($clientsList[$clientName])) {
                    $responseArray = array("status" => "error", "msg" => "clientAlreadyExists");
                } else {
                    $clientsList[$clientName] = $data;
                    // Guardo los datos
                    $this->setClients($clientsList, true);
                    $responseArray = array("status" => "success");
                }
                break;
            case "edit":
                $clientsList[$clientName] = (isset($clientsList[$clientName])) ? array_merge($clientsList[$clientName], $data) : $data;
                $this->setClients($clientsList, true);
                $responseArray = array("status" => "success");
                break;
            case "remove":
                if (isset($clientsList[$clientName])) {
                    unset($clientsList[$clientName]);
                    $this->setClients($clientsList, true);
                    $responseArray = array("status" => "success");
                } else {
                    $responseArray = array("status" => "error", "msg" => "inexistentClient");
                }
                break;
        }
        return $responseArray;
    }

    /*
     * Agrega, edita o elimina el archivo YML correspondiente a un cliente
     */

    public function processClientsYml($method, $clientName, $clientData = null) {
        global $config;
        // Ruta al archivo de conf del cliente
        $fichero = $config["clientsYmlFolder"] . $clientName . ".yml";
        switch ($method) {
            case "create":
            case "edit":
                // Datos pasados a YML
                $yaml = Spyc::YAMLDump($clientData, 2, 0, true);
                file_put_contents($fichero, $yaml);
                break;
            case "delete":
                unlink($fichero);
                break;
        }

        return;
    }

    /*
     * Combo de torneos. Segun los clientes que vengan
     */

    public function refreshTournamentsList($sport) {
        global $config;
        $array = Array();
        $page = file_get_contents("http://oficinabsas.datafactory.la:13722/servicios/listTournaments.php?sport=$sport");
        if ($page !== "") {

            $json = array(
                "type" => "string",
                "title" => "Torneo",
                "enum" => array(),
                "options" => array());
            $json["options"] = array("enum_titles" => array());

            foreach (json_decode($page) as $obj) {
                array_push($json["enum"], $obj->eventType . "." . $obj->eventCategory . "." . $obj->eventSubcategory);
                array_push($json["options"]["enum_titles"], $obj->eventName);
                $array[$obj->eventName] = $obj->eventType . "." . $obj->eventCategory . "." . $obj->eventSubcategory;
            }

            $jsonencoded = json_encode($json);
            $fh = fopen($config["tournamentsListJson"], 'w');
            fwrite($fh, $jsonencoded);
            fclose($fh);
        }

        return $array;
    }

    /*
     * Devuelve una lista de torneos extraida del JSON del select del formulario de torneos
     */

    private function getTournamentsList() {
        global $config;
        $activeTournamentsArray = json_decode(file_get_contents($config["tournamentsListJson"]), true);

        // Obtengo listado de canales del JSON de torneos
        $tournamentsChannelsList = $activeTournamentsArray["enum"];
        // Obtengo listado de nombres del JSON de torneos
        $tournamentsNamesList = $activeTournamentsArray["options"]["enum_titles"];

        // Asocio los canales a sus nombres
        $tournamentsList = array_combine($tournamentsChannelsList, $tournamentsNamesList);

        return $tournamentsList;
    }

    /*
     * Devuelve el nombre completo del torneo del canal que paso por la variable $channel
     */

    public function getTournamentName($channel) {
        $tournamentsList = $this->getTournamentsList();
        return $tournamentsList[$channel];
    }

    /*
     * Despliega un combo a partir de una consulta SQL y un conjunto de atributos
     */

    public static function getcomboFromQuery($query, $name, $id, $selectedOption = "", $attributes = array(), $customOptions = array()) {

        //Revisar los atributos
        $attributesText = "";

        foreach ($attributes as $label => $value) {
            $attributesText .= $label . "='" . addslashes($value) . " ' ";
        }

        $select = "<select name='" . $name . "' id='" . $id . "' " . $attributesText . ">";

        //Poner las opciones custom al inicio
        foreach ($customOptions as $value => $label) {
            $checked = ($value == $selectedOption) ? "selected='selected'" : "";
            $select .= "<option value='" . $value . "' " . $checked . ">" . $label . "</option>";
        }

        //Base de datos
        $db = Database::connect(USER_DS, PASS_DS, HOST_DS, NAME_DS, "5432", "postgres");
        $res = $db->query($query);

        //Poner las opciones recuperadas de la BD
        if ($db->numberOfRows($res) > 0) {

            while ($row = $db->RsFetchRow($res)) {

                $checked = ($selectedOption == $row[0]) ? "selected='selected'" : "";
                $label = isset($row[1]) ? $row[1] : $row[0];

                $select .= "<option value='" . $row[0] . "' " . $checked . ">" . $label . "</option>";
            }
        }

        $select .= "</select>";

        return $select;
    }

    /*
     * Implementa un cliente 
     */

    public function implementClient($loggedInUser, $clientName) {
        ob_implicit_flush();
        require (dirname(__DIR__) . '/config/hic.config.php');
        if (isset($loggedInUser) && $loggedInUser != "" && isset($clientName) && $clientName != "" && $this->checkLock($loggedInUser) == FALSE && $this->checkLock($loggedInUser, $clientName) == FALSE) {
            $this->setLock($loggedInUser, $clientName);

            $yamlglobal = Spyc::YAMLLoad($config["configGlobalFile"]); //se accesa al archivo de configuración global

            $yaml = Spyc::YAMLLoad($config["clientsYmlFolder"] . $clientName . '.yml'); //se accesa al archivo de configuración del cliente

            /*
             *  Variables hardcodeadas
             */
            $yamlglobal['createTorneoLangHtmlcenter'] == 'true'; // Hasta ahora no hay casos en que sea false, pero se deja, por si acaso

            if ($yaml['site']['customerDirPublic'] != $clientName) {
                die('El parametro pasado no corresponde con el archivo de configuracion.');
            }


            /*             * *****  Iniciailizacion de clases ****** */
            $logger_file = new FileLog();
            $logger_file->file(__DIR__ . $this->baseDir . "/logs/" . $clientName . ".log")->errorFile(__DIR__ . $this->baseDir . "/logs/" . $clientName . ".log"); // archivo para los mensajes de error

            $logger_screen = new WebLog();
            $logger = new MultipleLog();
            $logger->logger($logger_file)->logger($logger_screen);
            $logger->start();
            $configHtmlCenter = new ConfigHtmlCenter($clientName);
            $logger->log(($yaml['check']['productionRun'] == 'true') ? 'Run Type: Prod' : 'Run Type: Test');
            $logger->log('usuario: ' . $loggedInUser);
            $logger->log('customer: ' . $clientName);

            if (is_dir(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . '/deliver.datafactory.la/'))) {
                $logger->log('Eliminando ' . realpath(__DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . '/deliver.datafactory.la/') . " de una ejecucion anterior");
                exec("rm -rf " . realpath(__DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . '/deliver.datafactory.la/'));
            }

            if (is_dir(__DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/exported")) {
                $logger->log('Eliminando ' . __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/exported" . " de una ejecucion anterior");
                exec("rm -rf " . __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/exported");
            }

            if (is_dir(__DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/deliver_tmp")) {
                $logger->log('Eliminando ' . __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/deliver_tmp" . " de una ejecucion anterior");
                exec("rm -rf " . __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/deliver_tmp");
            }
            if (is_dir(__DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . '/exported_xsl')) {
                $logger->log('Eliminando ' . __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/exported_xsl" . " de una ejecucion anterior");
                exec("rm -rf " . __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/exported_xsl");
            }

            //variables que se obtienen del archivo de configuración
            $clientNameFullName = $yaml['site']['customerName'];
            $logger->log('customerName: ' . $clientNameFullName);
            $configHtmlCenter->sitiNombre = $clientNameFullName;
            if (!is_null($configHtmlCenter->sitiId) && $configHtmlCenter->sitiId != '') {
                $logger->log('siti_id: ' . $configHtmlCenter->sitiId);
            } else {
                $logger->log('Sitio Inexistente con ese nombre de cliente');
            }
            $clientNameDirHtml = $yaml['site']['customerDirHtml'];
            $logger->log('customerDirHtml: ' . $clientNameDirHtml);
            $sitiServer = $yaml['site']['sitiServer'];
            $logger->log('sitiServer: ' . $sitiServer);
            $configHtmlCenter->sitiServer = $sitiServer;
            $directory = $clientNameDirHtml . 'v3/';
            $logger->log('directory: ' . $directory);
            $configHtmlCenter->userDirectory = $directory;

            /*
             * Se deja siempre en true, por ahora, este es el comentario que habia antes en la configuracion
             *  # Contiene un valor booleano que indica si se requiere copiar los archivos en 
             *  # el directorio del cliente para luego commitear los cambios en el repositorio 
             *  # del deliver. 
             *  # Se copiaran los archivos xsls genericos para cada cliente y los archivos 
             *  # de /piezasGenericas/Cliente/v3/App.
             */

            if ($yaml['htmlCenter']['downloadLogos'] == 'true') {
                $logger->log('Update y Export de repositorios');
                $logger->log('Checkout y Export de clientes_html iniciando ...');
                $deploy_htmlcenter = new DeployFiles();
                $deploy_htmlcenter->checkoutDir(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . '/checkout');
                $deploy_htmlcenter->exportDir(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . '/exported');
                $deploy_htmlcenter->initialize($yaml['htmlCenter']['htmlcenterRevision']);
                $deploy_htmlcenter->reportToScreen(); // reporta a pantalla
                $deploy_htmlcenter->customer($clientName); // para crear los directorios
                $deploy_htmlcenter->configServersFTP($clientName);
                $deploy_htmlcenter->customersRoot(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . "/deliver_tmp");
                $logger->log('Checkout y Export de clientes_html terminado');
                $logger->log('Checkout y Export de xsl terminado iniciando ...');
                $deploy_xsl = new DeployFiles();
                $deploy_xsl->checkoutDir(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . '/checkout_xsl');
                $deploy_xsl->exportDir(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . '/exported_xsl');
                $deploy_xsl->initialize($yaml['htmlCenter']['xslhtmlcenterRevision']);
                $deploy_xsl->reportToScreen(); // reporta a pantalla
                $deploy_xsl->customer($clientName); // para crear los directorios
                $deploy_xsl->configServersFTP($clientName);
                $deploy_xsl->customersRoot(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . "/deliver_tmp");
                $logger->log('Checkout y Export de xsl terminado');
                $logger->log('Checkout de ServerSideAuthentication iniciando ...');
                $deploy_ssa = DFSVN::getNamedRepo('ServerSideAuthentication');
                $deploy_ssa->checkout("", realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . '/checkout_ssa', "HEAD");
                $logger->log('Checkout de ServerSideAuthentication terminado');
            }

            /*             * ************* Fin incializacion de clases ************** */
            if ($yaml['site']['deleteSite'] == 'true' && !is_null($configHtmlCenter->sitiId) && $configHtmlCenter->sitiId != '') {
                if ($yaml['check']['productionRun'] == 'true') {
                    $configHtmlCenter->deleteAll();
                }
                $logger->log('Borre sitio con exito, ID: ' . $configHtmlCenter->sitiId);
            }
            if ($yaml['check']['productionRun'] == 'true') {
                $configHtmlCenter->createSite();
                $logger->log('Cree sitio con exito, ID: ' . $configHtmlCenter->sitiId);
            }

            if ($yaml['htmlCenter']['deleteHtmlcenterTournaments']) {
                $logger->log('Borrar HTML Center para los torneos: ' . $yaml['htmlCenter']['deleteHtmlcenterTournaments']);
                if (($yaml['htmlCenter']['deleteHtmlcenterTournaments'] != '') && (!is_null($yaml['htmlCenter']['deleteHtmlcenterTournaments']))) {
                    foreach ($deleteHtmlcenterTournaments as $tournament) {
                        $channel = explode(".", $tourn["channel"]);
                        $id = $channel[count($channel) - 1];
                        $logger->log('Borrando HTMLCenter para: ' . $id);
                        if ($yaml['check']['productionRun'] == 'true') {
                            $configHtmlCenter->deletePageHTMLCenter($id);
                        }
                    }
                }
            }

            $tournamentsArray = $yaml['htmlCenter']['tournaments'];

            if ($yaml['htmlCenter']['createHtmlcenter'] == 'true') {
                $logger->log('Crear HTML Center para los torneos: ' . implode(',', $tournamentsArray));
                foreach ($tournamentsArray as $k => $tourn) {
                    $channel = explode(".", $tourn["channel"]);
                    $id = $channel[count($channel) - 1];
                    $name = $tourn["tournament"];
                    $logger->log('Creando HTMLCenter para: ' . $id);
                    if ($yaml['check']['productionRun'] == 'true') {
                        foreach ($yaml['htmlCenter']['languages'] as $langFull) {
                            $lang = explode("_", $langFull);
                            $language = $lang[0];
                            $location = $lang[1];
                            $configHtmlCenter->language = $language;
                            $configHtmlCenter->createHTMLCenter($id);
                        }
                    }
                    if ($yaml['htmlCenter']['downloadLogos'] == 'true') {
                        if ($yaml['check']['productionRun'] == 'true') {
                            $deploy_htmlcenter->tournament($id);
                        }
                        $logger->log('Seteo descarga de logos para: ' . $id);
                    }
                }
                if (($yaml['check']['productionRun'] == 'true') && ($yaml['htmlCenter']['downloadLogos'] == 'true')) {
                    $deploy_htmlcenter->downloadImages("escudos chicos", $directory . "/htmlCenter/escudos/");
                }
                $logger->log('Descargando logos para: ' . $tournament);
            }
            if ($yaml['htmlCenter']['renameMcMainMinTemplate'] == 'true') {
                copy($deploy_htmlcenter->checkoutDir() . "/v3/app/htmlCenter/assets/css/mc.main.min.template.css", $deploy_htmlcenter->exportDir() . '/v3/app/htmlCenter/assets/css/mc.main.min.css');
            }
            if ($yaml['check']['productionRun'] == 'true') {
                foreach ($tournamentsArray as $k => $tourn) {
                    $channel = explode(".", $tourn["channel"]);
                    $id = $channel[count($channel) - 1];
                    $name = $tourn["tournament"];

                    if (isset($tourn["coverage"]) && $tourn["coverage"] > 0) {
                        foreach ($yamlglobal["coverages"] as $value) {
                            if ($value["coverage"] <= $tourn["coverage"]) {
                                foreach ($value["packages"] as $package) {
                                    switch ($package) {

                                        // agendaMaM
                                        case 'agendaMaM':
                                            $logger->log('Creando Agenda HTML para: ' . $id);
                                            foreach ($yaml['htmlCenter']['languages'] as $langFull) {
                                                $lang = explode("_", $langFull);
                                                $language = $lang[0];
                                                $location = $lang[1];
                                                $configHtmlCenter->language = $language;
                                                $configHtmlCenter->createAgendaHTMLCenter($id);
                                            }
                                            $deploy_xsl->copyFrom("/xsl/agenda_v3.xsl")->to("/xsl/");
                                            break;

                                        // statsCenter es playerStats
                                        case 'statsCenter':
                                            $logger->log('Creando Player Stats para: ' . $id);
                                            foreach ($yaml['htmlCenter']['languages'] as $langFull) {
                                                $lang = explode("_", $langFull);
                                                $language = $lang[0];
                                                $location = $lang[1];
                                                $configHtmlCenter->language = $language;
                                                $configHtmlCenter->createPlayerStats($id);
                                            }
                                            break;

                                        // preview
                                        case 'preview':
                                            $logger->log('Creando Ficha Preview para: ' . $id);
                                            foreach ($yaml['htmlCenter']['languages'] as $langFull) {
                                                $lang = explode("_", $langFull);
                                                $language = $lang[0];
                                                $location = $lang[1];
                                                $configHtmlCenter->language = $language;
                                                $configHtmlCenter->createFichaPreview($id);
                                            }
                                            break;

                                        // //
                                        // case 'agendaMaM':
                                        //     break;
                                    }
                                }
                            }
                        }
                    }

                    // crear paginas de torneo lang
                    if ($yamlglobal['createTorneoLangHtmlcenter'] == 'true') {
                        $logger->log('Creando Torneo Lang para: ' . $id);
                        foreach ($yaml['htmlCenter']['languages'] as $langFull) {
                            $lang = explode("_", $langFull);
                            $language = $lang[0];
                            $location = $lang[1];
                            $configHtmlCenter->language = $language;
                            $configHtmlCenter->createTorneoLangHTMLCenter($id);
                        }
                    }
                }
            }

            //$configHtmlCenter->deleteXslHTMLCenter();
            //     ->toFTP();
            $deploy_htmlcenter->copyFrom("/v3/app/*")->to($directory);
            $logger->log('Copiando /v3/app/* to : ' . $directory);

            // Este no va al FTP
            $deploy_xsl->copyFrom("/xsl/futbol/v3/*")->to("/xsl/futbol/v3/");
            $logger->log('Copiando /xsl/futbol/v3/* to /xsl/futbol/v3');

            $deploy_xsl->copyFrom("/xsl/includes/configuracion_v3.template.xsl")->to("/xsl/includes/");
            $logger->log('Copiando /xsl/includes/configuracion_v3.template.xsl to /xsl/includes/');

            copy($deploy_xsl->checkoutDir() . "/xsl/agenda_v3.xsl", $deploy_htmlcenter->customersRoot() . '/' . $clientName . '/xsl/agenda_v3.xsl');
            $logger->log('Copiando ' . $deploy_xsl->checkoutDir() . "/xsl/agenda_v3.xsl" . " a " . $deploy_htmlcenter->customersRoot() . '/' . $clientName . '/xsl/');

            if (!file_exists($deploy_htmlcenter->customersRoot() . '/' . $clientName . '/xsl/includes/configuracion_v3.xsl')) {
                $logger->warn('El archivo ' . $deploy_htmlcenter->customersRoot() . '/' . $clientName . '/xsl/includes/configuracion_v3.xsl no existe, se usara la plantilla para crearlo');

                if (!file_exists($deploy_xsl->checkoutDir() . '/xsl/includes/configuracion_v3.template.xsl')) {
                    $logger->crit('Ni el archivo /xsl/includes/configuracion_v3.xsl, ni /xsl/includes/configuracion_v3.template.xsl no existen, no hay como crear el xsl.');
                }

                $xslfilename = $deploy_xsl->checkoutDir() . '/xsl/includes/configuracion_v3.template.xsl';
                $xsloutput = $deploy_htmlcenter->customersRoot() . '/' . $clientName . '/xsl/includes/configuracion_v3.xsl';
            } else {
                $logger->log('Modificando el archivo ' . $deploy_htmlcenter->customersRoot() . '/' . $clientName . '/xsl/includes/configuracion_v3.xsl');
                $xslfilename = $deploy_htmlcenter->customersRoot() . '/' . $clientName . '/xsl/includes/configuracion_v3.xsl';
                $xsloutput = $xslfilename;
            }

            $dom = new DomDocument;
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;
            $dom->loadXML(file_get_contents($xslfilename));
            $xmleditor = new EditXsl();
            $xpath = new DOMXpath($dom);

            $selector_torneos = '';
            $torneos_por_paquete = array();

            if ($yaml['htmlCenter']['xsl']['timezone'] != '') {
                $xmleditor->SetTimeZone($dom, $yaml['htmlCenter']['xsl']['timezone']);
            }
            foreach ($tournamentsArray as $k => $tourn) {
                $channel = explode(".", $tourn["channel"]);
                $channelName = $tourn["tournament"];

                if ($selector_torneos == '') {
                    $selector_torneos.= end($channel) . ":" . $channelName;
                } else {
                    $selector_torneos.= ',' . end($channel) . ":" . $channelName;
                }

                if (isset($tourn["coverage"]) && $tourn["coverage"] > 0) {
                    foreach ($yamlglobal["coverages"] as $value) {
                        if ($value["coverage"] <= $tourn["coverage"]) {
                            foreach ($value["packages"] as $package) {
                                if (!array_key_exists($package["packageName"], $torneos_por_paquete))
                                    $torneos_por_paquete[$package["packageName"]] = array();
                                array_push($torneos_por_paquete[$package["packageName"]], end($channel));
                            }
                        }
                    }
                }
            }

            $xpath = new DOMXpath($dom);
            $result = $xpath->evaluate('//xsl:template[@name="selector_torneos"]');
            foreach ($result as $node) {
                $node->nodeValue = $selector_torneos;
            }

            // torneos al xsl segun los paquetes y la covertura
            $torneos_xsl = array('historia' => '',
                'citiesStadiums' => '',
                'planteles' => '',
                'statsCenter' => '');
            foreach ($torneos_xsl as $key => $chann) {
                foreach ($torneos_por_paquete as $key2 => $channels) {
                    if ($key == $key2) {
                        foreach ($channels as $channel) {
                            if ($torneos_xsl[$key2] == '') {
                                $torneos_xsl[$key] .= '*' . $channel . '*';
                            } else {
                                $torneos_xsl[$key] .= ',' . '*' . $channel . '*';
                            }
                        }

                        $xpath = new DOMXpath($dom);
                        $result = $xpath->evaluate('//xsl:template[@name="torneos_' . $key . '"]');
                        foreach ($result as $node) {
                            $node->nodeValue = $torneos_xsl[$key];
                        }
                    }
                }
            }
            $xsl_size = $dom->save($xsloutput);
            $xmleditor->UpdateFile($changes, $xsloutput);
            $logger->log('Escribí archivo Xsl con: ' . $xsl_size . ' bytes');

            /* modificar auth.json */
            $jsonauthfilename = realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . '/checkout_ssa/ssa/auth.json';
            $logger->log('Actualizacion archivo auth.json');
            $authjson = json_decode(file_get_contents($jsonauthfilename), true);
            $jsoneditor = new jsonUpdate();
            $domains = array();
            foreach ($yaml['htmlCenter']["domains"] as $k => $domain) {
                array_push($domains, $domain["domain"]);
            }
            foreach ($yamlglobal["globalDomains"] as $k => $domain) {
                array_push($domains, $domain["domain"]);
            }

            // Buscar otros sitios donde tenga el mismo customerDirPublic, pero otro apiKey y eliminarlos
            foreach ($authjson as $key => $value) {
                if (($value["name"] == $yaml['site']['customerDirPublic']) && ($key != $yaml['htmlCenter']["apiKey"])) {
                    unset($authjson[$key]);
                }
            }
            $authjson[$yaml['htmlCenter']["apiKey"]]["domains"] = $domains;
            foreach ($tournamentsArray as $k => $tourn) {
                $channel = explode(".", $tourn["channel"]);
                $channel_str = "";
                foreach ($channel as $k => $channel_v) {
                    if ($channel_str == "") {
                        $channel_str.= $channel_v;
                    } else {
                        $channel_str.= '\\.' . $channel_v;
                    }
                }
                $authjson[$yaml['htmlCenter']["apiKey"]]["name"] = $yaml['site']['customerDirPublic'];
                $authjson[$yaml['htmlCenter']["apiKey"]]["sitename"] = $yaml['site']['customerName'];
                $name = $yaml['htmlCenter']["apiKey"] . '__coverageLevel__^' . $channel_str . '(\\..+$|$)__default';
                $jsoneditor->ModifyJsonByPath($authjson, $name, '==' . $tourn["coverage"]);

                $name = $yaml['htmlCenter']["apiKey"] . '__coverageLevel__^' . $channel_str . '(\\..+$|$)__packages';
                $jsoneditor->ModifyJsonByPath($authjson, $name, '--');

                /* soporte para especificar paquetes sin usar coverage */
                // if (isset($tourn["packages"])) {
                //     foreach ($tourn["packages"] as $k => $pack) {
                //         $jsoneditor->ModifyJsonByPath($authjson, $name, '++' . $pack);
                //     }
                // }

                if (isset($tourn["coverage"]) && isset($yamlglobal["coverages"])) {
                    foreach ($yamlglobal["coverages"] as $cov) {
                        if ($tourn["coverage"] >= $cov["coverage"]) {
                            foreach ($cov["packages"] as $pack) {
                                $jsoneditor->ModifyJsonByPath($authjson, $name, '++' . $pack["packageName"]);
                            }
                        }
                    }
                }
                if (isset($tourn["exceptions"])) {
                    foreach ($tourn["exceptions"] as $k => $excep) {
                        $line = "";
                        if ($excep["teamA"] && $excep["teamB"]) {
                            $name = $yaml['htmlCenter']["apiKey"] . '__coverageLevel__^' . $channel_str . '(\\..+$|$)__exceptions__' . $excep["teamA"] . "&" . $excep["teamB"];
                            $jsoneditor->ModifyJsonByPath($authjson, $name, '==' . $excep["coverage"]);
                        } else {
                            if ($excep["teamA"] || $excep["teamB"]) {
                                $name = $yaml['htmlCenter']["apiKey"] . '__coverageLevel__^' . $channel_str . '(\\..+$|$)__exceptions__' . $excep["teamA"] . $excep["teamB"];
                                $jsoneditor->ModifyJsonByPath($authjson, $name, '==' . $excep["coverage"]);
                            }
                        }
                    }
                }
            }

            // SISTEMA ANTIGUO, se deja para más flexibilidad, por si se necesita modificar algo extra
            if (isset($yaml['htmlCenter']["auth"]) && count($yaml['htmlCenter']["auth"]) > 0) {
                foreach ($yaml['htmlCenter']["auth"] as $name => $values) {
                    if ($name != "" && $values != "") {
                        $values_array = explode(',', $values);
                        foreach ($values_array as $value) {
                            $jsoneditor->ModifyJsonByPath($authjson, $name, $value);
                            $logger->log($name . ':' . $value);
                        }
                    }
                }
            }
            $logger->log('Fin actualizacion archivo auth.json');
            $fp = fopen($jsonauthfilename, 'w');
            $authjson_encoded = json_encode($authjson, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
            fwrite($fp, $jsoneditor->indent($authjson_encoded));
            fclose($fp);

            /* si se va a aplicar a produccion, realizar el commit */
            if ($yaml['check']['productionRun'] == 'true') {
                $logger->log('Commit archivo ' . $jsonauthfilename . ' con mensaje automatico.');
                $deploy_ssa->commit(realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/ " . $loggedInUser . '/checkout_ssa', "auth.json: " . $clientName . ": Commit Automatico por deployDeliver");
            } else {
                $logger->log('Commit de ' . $jsonauthfilename . ' no se realiza por no estar en productionRun = TRUE: ');
            }

            $path_add = realpath(__DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser) . '/deliver.datafactory.la/';
            $path_deliver_temp = realpath($deploy_htmlcenter->customersRoot() . '/' . $clientName . "/");
            $path_deliver_commit = realpath(__DIR__ . "/" . $this->baseDir . "/implementations/") . "/" . $loggedInUser . '/deliver.datafactory.la/' . $clientName;
            $message = $clientName . ": GenV3[Version: v3-. " . $yaml['htmlCenter']['htmlcenterRevision'] . "_xsl-" . $yaml['htmlCenter']['htmlcenterRevision'] . "- Commit Automatico ";
            $logger->log('Copio contenido de ' . $path_deliver_temp . ' a ' . $path_add);

            if ($yaml['check']['productionRun'] == 'true') {
                exec("mkdir -p " . $path_add);
                $deliver = DFSVN::getNamedRepo('deliver');
                $deliver->mkdir("/" . $clientName, "Adicionando directorio inicial para el usuario: " . $clientName);
                $deliver->checkout("/" . $clientName, $path_deliver_commit, "HEAD");
                $logger->log('Checkout del directorio del cliente ' . $clientName . ' realizado en: ' . $path_deliver_commit);
            } else {
                $logger->log("No con productionRun .. ejecutando: mkdir -p " . $path_deliver_commit);
                exec("mkdir -p " . $path_deliver_commit);
            }

            //1) v3/htmlCenter/customization
            if (!is_dir($path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/")) {
                $logger->log("Ejecutando: mkdir -p " . $path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/");
                exec("mkdir -p " . $path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/");
            }
            $logger->log("rsync -av --ignore-existing  " . $path_deliver_temp . '/' . $clientNameDirHtml . "/v3/htmlCenter/customization/" . " " . $path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/");
            exec("rsync -av --ignore-existing  " . $path_deliver_temp . '/' . $clientNameDirHtml . "/v3/htmlCenter/customization/" . " " . $path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/");
            $logger->log("rm -fr " . $path_deliver_temp . '/' . $clientNameDirHtml . "/v3/htmlCenter/customization/");
            exec("rm -fr " . $path_deliver_temp . '/' . $clientNameDirHtml . "/v3/htmlCenter/customization/");
            if ((file_exists($path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/mc.config.js")) && ($yaml['site']['sitiBaseURL'] != '')) {
                exec("sed -i -e 's|\(appUrl: \"\).*\(\".*\)|\\1" . $yaml['site']['sitiBaseURL'] . "/htmlCenter/\\2|' -e 's|\(iframesUrl: \"\).*\(\".*\)|\\1" . $yaml['site']['sitiBaseURL'] . "/\\2|'  " . $path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/mc.config.js");
                $logger->log("Actualizando appUrl e iframesUrl en : " . $path_add . '/' . $clientName . "/" . $clientNameDirHtml . "/v3/htmlCenter/customization/mc.config.js");
            }
            // ApiKey
            if ((file_exists($path_deliver_temp . '/' . $clientNameDirHtml . "/v3/htmlCenterApp.js")) && ($yaml['htmlCenter']['apiKey'] != '')) {
                exec("sed -i -e 's|\(apiKey: \"\)%%API_KEY%%\(\".*\)|\\1" . $yaml['htmlCenter']['apiKey'] . "\\2|' " . $path_deliver_temp . '/' . $clientNameDirHtml . "/v3/htmlCenterApp.js");
                $logger->log("Actualizando ApiKey en : " . $path_deliver_temp . '/' . $clientNameDirHtml . "/v3/htmlCenterApp.js");
            }

            //2) v3/pronosticador/
            if ($yaml['htmlCenter']['notCopyPronosticador'] == 'true') {
                $logger->log('Sacando del commit: "v3/pronosticador/"');
                $logger->log("rm -fr " . $path_deliver_temp . '/' . $clientNameDirHtml . "v3/pronosticador/");
                exec("rm -fr " . $path_deliver_temp . '/' . $clientNameDirHtml . "v3/pronosticador/");
            }

            //3) v3/htmlCenter/data/deportes/futbol/mundial*
            if ($yaml['htmlCenter']['notCopyMundialHistory'] == 'true') {
                $logger->log('Sacando del commit: "v3/htmlCenter/data/deportes/futbol/mundial{1,2}*"');
                $logger->log("rm -fr " . $path_deliver_temp . '/' . $clientNameDirHtml . "v3/htmlCenter/data/deportes/futbol/mundial{1,2}*");
                exec("rm -fr " . $path_deliver_temp . '/' . $clientNameDirHtml . "v3/htmlCenter/data/deportes/futbol/mundial{1,2}*");
            }

            exec("cp -R " . $path_deliver_temp . " " . $path_add);

            if ($yaml['check']['productionRun'] == 'true') {
                if ($deliver) {
                    $logger->log('Adding al repositorio: ' . $path_deliver_commit);
                    $paths = scandir($path_deliver_commit);
                    foreach ($paths as $p) {
                        if ($p === '.' or $p === '..' or $p === '.svn')
                            continue;
                        $result = $deliver->add($path_deliver_commit, $p);
                        if ($result["error"] > 0) {
                            $logger->warn('svn add ' . $p . ' fallo: ' . print_r($result["text"], TRUE));
                        }
                    }
                    $logger->log('Commiteando con mensaje: ' . $message);
                    $result = $deliver->commit($path_deliver_commit, $message);
                    if ($result["error"] > 0) {
                        $logger->error('svn commit de deliver fallo: ' . print_r($result["text"], TRUE));
                    }
                    $logger->log('Commit realizado para el cliente: ' . $clientName);
                } else {
                    $logger->crit('La API de svn para el repositorio de deliver no esta inicializada');
                    $logger->crit('NO SE REALIZO EL COMMIT DEL DIRECTORIO DEL USUARIO');
                }
            } else {
                $logger->log('Commit para el cliente ' . $clientName . ' no se realiza por no estar en productionRun = TRUE: ');
            }

            $logger->end();
            $r = $this->removeLock($loggedInUser, $clientName);
            if ($r !== TRUE)
                $logger->log($r);
        } elseif ($this->checkLock($loggedInUser)) {
            echo "Running";
        }
    }

    /*
     * Crea el archivo de bloqueo
     * 
     */

    public function setLock($loggedInUser, $clientName = NULL) {
        $lockFilePath = __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/";
        if (!is_dir($lockFilePath)) {
            exec("mkdir -p " . $lockFilePath);
        }
        exec("touch " . $lockFilePath . "/.running");
        if ($clientName != NULL) {
            file_put_contents(__DIR__ . "/" . $this->baseDir . "/implementations/running_" . $clientName, $loggedInUser);
        }
    }

    /*
     * Comprueba el estado del archivo de bloqueo
     * 
     * devuelve TRUE si existe el archivo (o no se pasaron bien los parametros)
     * 
     */

    public function checkLock($loggedInUser, $clientName = NULL) {
        $lockFilePath = __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/";
        if (isset($loggedInUser) && $loggedInUser != "") {
            if ($clientName != NULL) {
                return (is_file(__DIR__ . "/" . $this->baseDir . "/implementations/running_" . $clientName)) ? file_get_contents(__DIR__ . "/" . $this->baseDir . "/implementations/running_" . $clientName) : FALSE;
            } else {
                return is_file($lockFilePath . "/.running");
            }
        } else {
            return TRUE;
        }
    }

    /*
     * Elimina el archivo de bloqueo
     */

    public function removeLock($loggedInUser, $clientName = NULL, $force = FALSE) {
        $lockFilePath = __DIR__ . "/" . $this->baseDir . "/implementations/" . $loggedInUser . "/";
        $lockfile = $lockFilePath . "/.running";
        if (isset($loggedInUser) && $loggedInUser != "") {
            if (file_exists($lockfile)) {
                unlink($lockfile);
            }
        }
        if ($clientName != NULL) {
            if ($force = TRUE) {
                unlink(__DIR__ . "/" . $this->baseDir . "/implementations/running_" . $clientName);
            } else {
                if ($loggedInUser == file_get_contents(__DIR__ . "/" . $this->baseDir . "/implementations/running_" . $clientName)) {
                    unlink(__DIR__ . "/" . $this->baseDir . "/implementations/running_" . $clientName);
                } else {
                    return "Tu usuario y el que esta implementando el cliente, no coinciden: No se borra " . __DIR__ . "/" . $this->baseDir . "/implementations/running_" . $clientName;
                }
            }
        }
        return TRUE;
    }

}
