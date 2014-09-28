/*
 */
var app = {
    // Application Constructor
    initialize: function () {
        this.bindEvents();
    },
    // App default parameters
    defaultParams: {
        backendUrl: "//190.195.105.224:5080/phonegap/backend/index.php",
        imagesUrl: "//beta.bepots.com/images/",
        serverTimeout: 5,
        deviceIsPc: true
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function () {
        if (this.getParameter("pc", app.defaultParams.deviceIsPc) === true)
            this.onDeviceReady();
        else
            document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    // deviceready Event Handler
    //
    // The scope of 'this' is the event. In order to call the 'receivedEvent'
    // function, we must explicitly call 'app.receivedEvent(...);'
    onDeviceReady: function () {
        app.receivedEvent('productsList');
    },
    // Update DOM on a Received Event
    receivedEvent: function (method) {

        switch (method) {
            case "productDetail":
                
                break;
            case "productsList":
                var productsListAjaxCall = this.ajaxCall("method=getProductsList");
                productsListAjaxCall.done(function (response) {
                    app.preloader("hide");
                    if (response.status === "success") {
                        var productData, productItem;
                        var count = 0;
                        var productsListSelector = $("#productsList").find(".bp-productsList");
                        for (var product in response.data) {
                            if (response.data.hasOwnProperty(product)) {
                                productData = response.data[product];
                                productItem = app.getTemplate("productItem")
                                        .find(".bp-title").html(productData.categoria3).end()
                                        .find(".bp-envase").html(productData.envase).end()
                                        .find(".bp-categoria1").html(productData.categoria1).end()
                                        .find(".bp-marca").html(productData.marca).end()
                                        .find(".bp-image").attr("src", "http://beta.bepots.com/images/" + productData.imagen).end();

                                productsListSelector.append(productItem);
                            }
                            if (count++ === 10)
                                break;
                        }
                    } else {
                        $("#app").html("Ocurrió un problema al intentar cargar el listado de productos");
                    }
                });
                break;
        }
    },
    preloader: function (action, text) {
        if (action === "show")
            $("#preloader").show().html(text);
        else if (action === "hide")
            $("#preloader").hide().html("");
        return;
    },
    getTemplate: function (tpl) {
        var template = $(".templatesCtn").find(".tpl-" + tpl);
        return template.clone().removeClass("tpl-" + tpl);
    },
    showAlertMessage: function (message, type) {
        var msg = $(makeAlertMessage(message, type));
        $("#msgContainer").append(msg).delay(7000).fadeOut('fast');
    },
    getParameter: function (name, defaultVar) {
        /* getParameter - Función para obtener parámetros de la URL. Similar a $_GET["{NOMBRE_VARIABLE}"] en PHP. 
         *  Parámetros: 
         *  name (string): Nombre del parámetro que deseo obtener.
         *  defaultVar (string | boolean | int): Valor por defecto que se quiere que devuelva la función en caso de que el parámetro no exista.
         */
        name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
        var regexS = "[\\?&]" + name + "=([^&#]*)";
        var regex = new RegExp(regexS);
        var results = regex.exec(window.location.href);
        if (results === null && typeof defaultVar !== "undefined") {
            return defaultVar;
        } else if (results !== null) {
            if (results[1] === "true") {
                return true;
            } else if (results[1] === "false") {
                return false;
            } else if (!isNaN(results[1])) {
                return parseInt(results[1], 10);
            } else {
                return results[1];
            }
        } else {
            return;
        }
    },
    ajaxCall: function (dataString, options) {

        return $.ajax({
            type: 'GET',
            url: (typeof options !== "undefined" && typeof options.url !== "undefined") ? options.url : app.defaultParams["backendUrl"],
            data: dataString,
            timeout: app.defaultParams["serverTimeout"] * 1000,
            dataType: 'jsonp',
            crossDomain: true,
            cache: false,
            async: (typeof options !== "undefined" && typeof options.async !== "undefined") ? options.async : true,
            contentType: 'application/json; charset=UTF-8'
        });
    },
    ajaxSend: function (dataString, options) {
        return $.ajax({
            type: "GET",
            url: (typeof options !== "undefined" && typeof options.url !== "undefined") ? options.url : app.defaultParams["backendUrl"],
            data: dataString,
            dataType: (typeof options !== "undefined" && typeof options.dataType !== "undefined") ? options.dataType : "json",
            async: (typeof options !== "undefined" && typeof options.async !== "undefined") ? options.async : true
        });
    },
};
