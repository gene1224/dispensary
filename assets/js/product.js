jQuery(document).ready(function ($) {});

function removeFromStore(el, sku) {
  el.setAttribute("disabled", "disabled");
  Swal.fire({
    title: "Remove Product From Store?",
    showCancelButton: true,
    confirmButtonText: `Remove`,
  }).then((result) => {
    if (result.isConfirmed) {
      jQuery.ajax({
        type: "POST",
        url: `${wp_ajax.url}?action=remove_product_in_site`,
        data: {
          sku: sku,
        },
        success: function (response) {
          Swal.fire("Deleted", "", "success").then(function () {
            jQuery(`div[product-sku="${sku}"]`).remove();
            jQuery(".imported-count").text(
              Number(JSON.parse(response).imported_count)
            );
            jQuery("#added_products_count").text(
              Number(jQuery("#added_products_count").text()) - 1
            );
            jQuery("#remaining_products_count").text(
              Number(jQuery("#remaining_products_count").text()) + 1
            );
          });
        },
      });
    }
  });
  return;
}
