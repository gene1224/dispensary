jQuery(document).ready(function ($) {
  $(".report-view-loading").hide();
  $(".product-content").removeAttr("style");
  $(".order-content").removeAttr("style");
  $(".targetdiv").css("justify-content", "left");
  $(".stocks-availability").each(function (index) {
    if ($(this).text() > 0) {
      $(this)
        .parents(".product-cart-item")
        .find(".product-cart-item-action")
        .hide();
      $(this)
        .parents(".product-cart-item")
        .find(".stocks-availability")
        .css("color", "green");
    } else {
      $(this)
        .parents(".product-cart-item")
        .find(".product-cart-item-action")
        .show();
      $(this)
        .parents(".product-cart-item")
        .find(".stocks-availability")
        .css("color", "red");
    }
  });
  $(".ioc-os-status-notif").each(function (index) {
    if ($(this).text() == "Pending" || $(this).text() == "pending") {
      $(this).css("color", "red");
    } else {
      $(this).css("color", "green");
    }
  });
  $.each(wp_ajax.ordered_products, function (index, value) {
    //console.log(index);
    //console.log(value);
  });
  //console.log(wp_ajax.ordered_products);
  $(".tabsingle").click(function () {
    $(".targetdiv").hide();
    $("#div-" + $(this).attr("target")).show();
    $(".tabsingle").removeClass("tab-active");
    $(this).addClass("tab-active");
  });
  $("button#stock-email-notif").each(function (index, value) {
    $(this).on("click", function () {
      const product_sku = $(this).data("sku");
      console.log(product_sku);
    });
  });
});
