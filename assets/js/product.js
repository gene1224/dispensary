jQuery(document).ready(function ($) {
  $(".update-price").click(function () {
    const product_name = $(this).attr("product-name");
    const original_price = Number($(this).attr("original-price"));
    const price = Number($(this).attr("price"));
    const sku = $(this).attr("sku");
    const srp = (original_price + original_price * 0.5).toFixed(2);

    const revenueCalc = (o, p) => {
      return ((p - o) * 0.9).toFixed(2);
    };

    Swal.fire({
      title: `Update ${product_name} Price`,
      html: `
        <p><small>Product Price: ${original_price} | SRP: $${srp} | Revenue : <span id="new_revenue">${revenueCalc(
        original_price,
        price
      )}</span></small></p>`,
      inputPlaceholder: "Enter New Price",
      input: "number",
      onOpen: () => {
        const input = Swal.getInput();
        input.oninput = () => {
          const inputVal = Number(input.value);
          if (inputVal > srp) {
            Swal.showValidationMessage(
              `Price should not be higher than the SRP: $${srp}`
            );
          } else if (inputVal < original_price) {
            Swal.showValidationMessage(
              `Price should not be loewr than the original price: $${original_price}`
            );
          } else {
            $("#new_revenue").text(revenueCalc(original_price, inputVal));
            input.value = inputVal.toFixed(2);
          }
        };
      },
      inputValue: price,
      showCancelButton: true,
      confirmButtonText: "Update Price",
      showLoaderOnConfirm: true,
      preConfirm: (price) => {
        console.log(price);
        if (price > (original_price + original_price * 0.5).toFixed(2)) {
          return Swal.showValidationMessage(
            `Price should not be higher than the SRP: $${srp}`
          );
        } else if (price < original_price) {
          return Swal.showValidationMessage(
            `Price should not be loewr than the original price: $${original_price}`
          );
        }

        const requestUrl = `${wp_ajax.url}?action=update_product_price&sku=${sku}&price=${price}`;
        return fetch(requestUrl, {
          method: "POST",
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
      if (result.isConfirmed) {
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
