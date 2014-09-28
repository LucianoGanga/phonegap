
/* 
 * Page: productsList
 * Description: makes a product list according to the search value
 */
$(document).on("pagecreate", "#productsList", function () {
    $("#searchProductByText-input").on("keyup", function (e) {
        var $ul = $($(this).data("list"));
        var $input = $(this);
        var value = $input.val();
        var html = "";
        $ul.html("");
        if (value && value.length > 2) {
            app.preloader("show");
            //$ul.html("<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>");
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
});