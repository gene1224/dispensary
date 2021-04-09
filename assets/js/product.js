jQuery(document).ready(function ($) {
  $(".update-price").click(function () {
    const product_name = $(this).attr("product-name");
    const original_price = Number($(this).attr("original-price"));
    const price = Number($(this).attr("price"));
    const sku = $(this).attr("sku");
    Swal.fire({
      title: `Update ${product_name} Price`,
      html: `
        <p><small>SRP: $${(original_price + original_price * 0.5).toFixed(2)}</small></p>`,
      inputPlaceholder: "Enter New Price",
      input: "number",
      onOpen: () => {
        const input = Swal.getInput()
        input.oninput = (x) => {
          console.log(input)
        }
      },
      inputValue: price,
      showCancelButton: true,
      confirmButtonText: "Update Price",
      showLoaderOnConfirm: true,
      preConfirm: (price) => {
        console.log(price);
        if (price > (original_price + original_price * 0.5).toFixed(2)) {
          return Swal.showValidationMessage(
            `Price should not be higher than the SRP: $${(
              original_price +
              original_price * 0.5
            ).toFixed(2)}`
          );
        }
        return fetch(`${wp_ajax.url}?action=update_product_price`, {
          method: "POST",
          data: { sku: sku, price: price },
        })
          .then((response) => {
            console.log(response);
            if (!response.ok) {
              throw new Error(response.statusText);
            }
            return response.json();
          })
          .catch((error) => {
            Swal.showValidationMessage(`Update failed try again`);
          });
      },
      allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
      console.log(result);
      if (result.updated) {
        Swal.fire({
          title: `Price successfuly updated`,
        });
      }
    });
  });
});

function removeFromStore(el, sku) {
  Swal.fire({
    title: "Remove Product From Store?",
    showCancelButton: true,
    confirmButtonText: `Remove`,
  }).then((result) => {
    if (result.isConfirmed) {
      el.setAttribute("disabled", "disabled");
      el.innerHTML = `Deleting Product.. <div class="custom-spin-loader mini"></div>`;
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
}
