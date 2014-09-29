
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
                var productData, productItem;
                var count = 0;
                for (var product in response.data) {
                    if (response.data.hasOwnProperty(product)) {
                        count++;
                        productData = response.data[product];
                        productItem = app.getTemplate("productItem")
                                .attr("data-product", JSON.stringify(productData))
                                .find(".bp-title").html(productData.categoria3).end()
                                .find(".bp-envase").html(productData.envase).end()
                                .find(".bp-categoria1").html(productData.categoria1).end()
                                .find(".bp-brand").html(productData.marca).end();
                        // Si el producto tiene imagen, la muestro
                        if (productData.imagen !== "sin imagen")
                            productItem.find(".bp-image").attr("src", app.defaultParams.imagesUrl + productData.imagen).end();

                        html += $('<div>').append(productItem).html();
                    }
                }
                app.preloader("hide");
                if (count > 0)
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

    /*
     * Product Item tap action script
     */
    $(document).on("tap", ".bp-productItemLink", function (e) {
        e.preventDefault();
        var productData = $(this).parents(".bp-productItem").data("product");
        $("#productDetail")
                .find(".bp-title").html(productData.categoria3).end()
                .find(".bp-envase").html(productData.envase).end()
                .find(".bp-categoria1").html(productData.categoria1).end()
                .find(".bp-brand").html(productData.marca).end();
        // Si el producto tiene imagen, la muestro
        if (productData.imagen !== "sin imagen")
            $("#productDetail").find(".bp-image").attr("src", app.defaultParams.imagesUrl + productData.imagen).end();

        $.mobile.pageContainer.pagecontainer("change", "#productDetail", {"showLoadMsg": true});
    });
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