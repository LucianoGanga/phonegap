
/* 
 * Page: productsList
 * Description: makes a product list according to the search value
 */
$(document).on("pagecreate", "#productsList", function () {

    /*
     * Search input action script
     */
    $("#searchProductByText").on("filterablebeforefilter", function (e, data) {
        var $ul = $(this),
                $input = $(data.input),
                value = $input.val(),
                html = "";
        $ul.html("");
        if (value && value.length > 2) {
            app.preloader("show");
            //$ul.html("<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>");
            $ul.html("Buscando...");
            $ul.listview("refresh");
            var productSearchByTextAjaxCall = app.ajaxCall("method=searchProductByText&text=" + $input.val());
            productSearchByTextAjaxCall.done(function (response) {
                var productsList = app.makeProductsList(response.data);
                html = productsList["html"];
                app.preloader("hide");
                if (productsList["count"] > 0)
                    $ul.html(html);
                else
                    $ul.html("Sin resultados.");
                $ul.listview("refresh");
                $ul.trigger("updatelayout");
            });
        }
    });

    /*
     * Product Item cart - Qty selection
     */
    $(".bp-productQty").on("change", function () {
        var qty = $(this).val();
        var price = $(this).data("price");
        var total = qty * price;
        if (qty > 0)
            $(this).parents("#addToCart").find(".bp-addButton").removeClass("ui-state-disabled");
        else
            $(this).parents("#addToCart").find(".bp-addButton").addClass("ui-state-disabled");
        $(this).parents("#addToCart").find(".bp-totalPrice").html(total);
    });

});

/* 
 * Page: cartList
 * Description: makes a product list according to the search value
 */
$(document).on("pagecreate", "#myCart", function () {
    app.mediator.subscribe("cart:itemsList", function (itemsList) {
        var itemsListResult = app.makeProductsList(itemsList);
        var ulList = $("#cartItemsList");
        if (itemsListResult["count"] > 0)
            ulList.html(itemsListResult["html"]);
        else
            ulList.html("Su lista está vacía");
        ulList.listview("refresh");
    });
});


/*
 * Cart tap action
 */
$(document).on("tap", ".bp-cartButton", function (e) {
    e.preventDefault();

    $.mobile.pageContainer.pagecontainer("change", "#myCart", {"showLoadMsg": true});
    app.mediator.publish("cart:itemsList", app.clientData["cart"]["itemsList"]);
    return;
});

/*
 * Product Item tap action
 */
$(document).on("tap", ".bp-productItemLink", function (e) {
    e.preventDefault();
    var productData = $(this).parents(".bp-productItem").data("product");
    app.showProductDetail(productData);
    return;
});

/*
 * Scan barcode button action
 */
$(document).on("tap", ".bp-scanCodeButton", function (e) {
    e.preventDefault();
    app.scan();
    return;
});




/* 
 * Page: productDetail
 * Description: shows a product's details
 */
$(document).on("pagecreate", "#productsList", function () {
    $(".bp-productQty").on("change", function () {
        var qty = $(this).val();
        var price = $(this).data("price");
        var total = qty * price;
        if (qty > 0)
            $(this).parents("#addToCart").find(".bp-addButton").removeClass("ui-state-disabled");
        else
            $(this).parents("#addToCart").find(".bp-addButton").addClass("ui-state-disabled");
        $(this).parents("#addToCart").find(".bp-totalPrice").html(total);
    });

});