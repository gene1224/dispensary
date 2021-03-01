const productEndpoint = "/wp-json/wc/v3/products";
const categoryEndpoint = "/wp-json/wc/v3/products/categories";
const tagEndpoint = "/wp-json/wc/v3/products/tags";

const storageSave = (name, data, is_object = true) => {
  const finalData = is_object ? JSON.stringify(data) : data;
  localStorage.setItem(name, finalData);
};

const storageGet = (name, is_object = true) => {
  const raw_data = localStorage.getItem(name);
  if (raw_data === null) {
    return false;
  }
  return is_object ? JSON.parse(raw_data) : raw_data;
};

const serializeObject = function (obj) {
  let str = [];
  for (let p in obj)
    if (obj.hasOwnProperty(p) && encodeURIComponent(obj[p])) {
      str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
    }
  return str.join("&");
};

const ratingStarsHTML = (average_rating) => {
  let ratingHtml = "";
  for (let x = 0; x < 5; x++) {
    const solid = x + 1 <= Math.round(average_rating) ? "solid" : "";
    ratingHtml = `${ratingHtml}<span class="star ${solid}"></span>`;
  }
  return ratingHtml;
};

const price_round = (num) => {
  cents = (num * 100) % 100;
  if (cents >= 25 && cents < 75) {
    //round x.25 to x.74 -> x.49
    return Math.floor(num) + 0.49;
  }
  if (cents < 25) {
    //round x.00 to x.24 -> [x - 1].99
    return Math.floor(num) - 0.01;
  }
  //round x.75 to x.99 -> x.99
  return Math.floor(num) + 0.99;
};
const productItemHTML = (product, index) => {
  const categories = product.categories.map((category) => category.name).join();
  const tags = product.tags.map((tag) => tag.name).join();
  const srp = price_round(Number(product.price) + Number(product.price) * 0.5);
  const estimatedProfit = (srp - Number(product.price)) * 0.9;
  const productImageSrc =
    product.images[0]?.src ||
    "https://dummyimage.com/180x180/ccc/000.png&text=Product";
  return `<div class="product-cart-item" product-sku="${product.sku}">
    <div class="product-cart-item-details">
        <div class="product-cart-item-image">
            <img src="${productImageSrc}">
        </div>
        <div class="product-cart-desc">
            <div class="name">${product.name}</div>
            <div class="price">Original Price : $ ${Number(
              product.price
            ).toFixed(2)}</div>
            <div class="categories">Categories: ${categories}</div>
            <div class="tags">Tags: ${tags}</div>
            <div class="sku--rating">
                <div class="sku">SKU: ${product.sku}</div>
                <div class="rating">
                    ${ratingStarsHTML(product.average_rating)}
                </div>
            </div>
            <div class="source-site">
                Source: <a href="https://allstuff420.com">All Stuff 420</a>
            </div>
        </div>
    </div>
    <div class="product-cart-item-action">
        <div class="new-listing-price">
        <label>New Listing Price</label>
        <p class="srp">SRP: $ ${srp.toFixed(2)}</p>
            <input type="number" value="${srp.toFixed(
              2
            )}" name="items[${index}][listing_price]">
            <input type="hidden" name="items[${index}][original_price]"  value="${
    product.price
  }">
            <input type="hidden" name="items[${index}][sku]"  value="${
    product.sku
  }">
            <input type="hidden" value="${
              product.id
            }" name="items[${index}][source_product_id]">
            <p class="estimated-profit">Estimated Profit $ <span class="estimated-profit-${
              product.id
            }">${estimatedProfit.toFixed(2)}</span></p>
        </div>
        <div class="listing-action">
            <button type="button" class="remove-product" onclick="removeItem('${
              product.sku
            }', ${product.id})">Remove from cart</button>
        </div>
    </div>
</div>`;
};

function removeItem(sku, product_id) {
  let data = {
    source_product_id: product_id,
    source_site_url: "https://allstuff420.com",
    sku: sku,
  };
  action = "remove_product_in_cart";
  jQuery.ajax({
    type: "POST",
    url: `${wp_ajax.url}?action=${action}`,
    data: data,

    success: function (response) {
      storageSave("listing_cart", response);
      jQuery(`div[product-sku="${sku}"]`).fadeOut(500, function () {
        jQuery(`div[product-sku="${sku}"]`).remove();
      });
      if (response.length) {
        jQuery("#cart-empty").show();
      }
    },
  });
}

jQuery(document).ready(function ($) {
  if (wp_ajax.listing_cart) {
    storageSave("listing_cart", wp_ajax.listing_cart);
  }
  const included_ids = wp_ajax.listing_cart
    .map((item) => item.source_product_id)
    .join();

  const default_query_string = {
    per_page: 100,
    stock_status: "instock",
    include: included_ids,
  };

  const loadProducts = (queryString, site = "https://allstuff420.com") => {
    const selected_ids = wp_ajax.imported_products
      .filter((product) => {
        return product.source_site_url == "http://allstuff420.com";
      })
      .map((product) => product.source_product_id);

    $.ajax({
      url: `${site}${productEndpoint}?${queryString}`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader(
          "Authorization",
          `Basic ${wp_ajax.default_api_key}`
        );
      },
      type: "GET",
      contentType: "application/json",
      success: function (products) {
        const productHTMLs = products
          .map((product, index) => {
            return productItemHTML(product, index);
          })
          .join("");
        $("#importCartItems").html(productHTMLs);
      },
    });
  };
  if (wp_ajax.listing_cart.length != 0) {
    loadProducts(serializeObject(default_query_string));
  } else {
    jQuery("#importCartItems").fadeOut();
    jQuery("#cart-empty").show();
  }
  var pulser;
  var perBatch = 2;

  $("#productImportForm").submit(function (e) {
    e.preventDefault();
    $("#importButton").addClass("loading");
    $("#importButton").html("Importing Products");

    $.ajax({
      url: `${wp_ajax.url}?action=start_import`,
      data: new FormData(this),
      processData: false,
      contentType: false,
      type: "post",
      success: function (response) {
        get_status();
        pulser = setInterval(get_status, 5000);
      },
    });
  });

  function get_status() {
    $.ajax({
      url: `${wp_ajax.url}?action=import_pulse`,
      data: {},
      type: "post",
      success: function (response) {
        const res = JSON.parse(response);

        $("#importButton").html(
          `Importing Products  ${res.skus_done.length} of ${res.skus.length}`
        );
        if (res.remaining_skus.length == 0) {
          clearInterval(pulser);

          Swal.fire(
            "Import Complete!",
            "Products successfully added to your dispensary. Click Okay to finish",
            "success"
          ).then((result) => {
            window.location = "https://wpms.net/import-test/?view=imported";
          });
        }
      },
    });
  }
});
