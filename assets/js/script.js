const productEndpoint = "/wp-json/wc/v3/products";
const categoryEndpoint = "/wp-json/wc/v3/products/categories";
const tagEndpoint = "/wp-json/wc/v3/products/tags";
const gridLoaderHTML = `<div class="loader"></div>`;

const storageSave = (name, data, is_object = true) => {
  if (name == "listing_cart") {
    jQuery(".cart-count").html(data.length);
  }
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

const tagsHTML = (tags) => {
  if (tags.length === 0) {
    return "";
  }
  return `<div class="tags">${tags[0].name}</div>`;
};

const productItemButton = (product, disable = false, cart = false) => {
  const className = disable ? "remove" : "add";
  const buttonName = disable ? "Product Imported" : "Enlist Product";
  const disabled = disable ? "disabled" : "";
  if (!cart) {
    return `<button ${disabled} class="action ${className}" onclick="addToCart(this, ${product.id}, '${product.sku}')" data-product-id="${product.id}">${buttonName}</button>`;
  }
  return `<button class="action on-cart" data-product-id="${product.id}" onclick="addToCart(this, ${product.id}, '${product.sku}', true)">Added to Cart</button>`;
};

const productItemHTML = (product, importedProducts = []) => {
  const productExist = importedProducts.includes(product.id.toString());
  let cart_items = storageGet("listing_cart") || [];
  console.log(cart_items);
  const productOnCart = cart_items.find(
    (item) => item.source_product_id == product.id
  );

  return `<div class="product-item">
	<div class="product-image">
        <img src="${
          product.images[0]?.src || "https://dummyimage.com/160x160/fff/000000"
        }">
        ${tagsHTML(product.tags)}
	</div>
	<div class="product-descriptions">
		<div class="product-name">${product.name}</div>
		<div class="product-price">$ ${Number(product.price).toFixed(2)}</div>
		<div class="product-sku--rating">
			<div class="product-sku">
				<span>SKU:</span>
				<span>${product.sku}</span>
			</div>
			<div class="product-rating">
				${ratingStarsHTML(product.average_rating)}
			</div>
		</div>
	</div>
	<div class="product-actions">
        ${productItemButton(product, productExist, productOnCart)}
	</div>
	<div class="product-source">
		Source :
		<a href="https://allstuff420.com">All Stuff 420</a>
	</div>
</div>`;
};

const selectOptionsHTML = (categories) => {
  return categories
    .map(
      (category) => `<option value="${category.id}">${category.name}</option>`
    )
    .join("");
};

jQuery(document).ready(function ($) {
  console.log(wp_ajax);
  if (wp_ajax.listing_cart) {
    storageSave("listing_cart", wp_ajax.listing_cart);
  }
  getTaxonomies = (type = "tags", queryString = "per_page=100") => {
    const endpoint = type == "tags" ? tagEndpoint : categoryEndpoint;
    $.ajax({
      url: `${wp_ajax.default_site}${endpoint}?${queryString}`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader(
          "Authorization",
          `Basic ${wp_ajax.default_api_key}`
        );
      },
      type: "GET",
      contentType: "application/json",
      success: function (response) {
        const selectionHTML = selectOptionsHTML(response);
        if (type == "tags") {
          $("#tagSelect").html(
            `<option value="">Select Tag</option>${selectionHTML}`
          );
          $("#tagSelect").removeAttr("disabled");
        } else if (type == "categories") {
          $("#categorySelect").html(
            `<option value="">Select Category</option>${selectionHTML}`
          );
          $("#categorySelect").removeAttr("disabled");
        }
      },
    });
  };
  getTaxonomies();
  getTaxonomies("categories");

  const default_query_string = {
    per_page: 10,
    stock_status: "instock",
  };

  let productFilter = default_query_string;

  let current_page = 1;

  $("#gridNext").click(function () {
    $("#product-importer-grid").html(gridLoaderHTML);
    current_page += 1;
    loadProducts(serializeObject(productFilter));
  });
  $("#gridPrev").click(function () {
    if (current_page == 1) return;
    $("#product-importer-grid").html(gridLoaderHTML);
    current_page -= 1;
    loadProducts(serializeObject(productFilter));
  });

  const loadProducts = (
    queryString,
    fromFilter = false,
    site = "https://allstuff420.com"
  ) => {
    const selected_ids = wp_ajax.imported_products
      .filter((product) => {
        return product.source_site_url == "http://allstuff420.com";
      })
      .map((product) => product.source_product_id);

    $.ajax({
      url: `${site}${productEndpoint}?page=${current_page}&${queryString}`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader(
          "Authorization",
          `Basic ${wp_ajax.default_api_key}`
        );
      },
      type: "GET",
      contentType: "application/json",
      success: function (products) {
        if (products.length == 0) {
          $("#product-importer-grid").html("<h2>No Products Found</h2>");
        }

        $("#currentPage").text(current_page);
        const productHTMLs = products
          .map((product) => {
            return productItemHTML(product, selected_ids);
          })
          .join("");

        $("#product-importer-grid").html(productHTMLs);
        if (fromFilter) {
          $("#applyFilter").removeAttr("disabled");
          $("#applyFilter").find(".loader").fadeIn();
        }
      },
    });
  };

  loadProducts(serializeObject(default_query_string));

  $("#applyFilter").click(() => {
    $(this).attr("disabled", "true");
    const selected_category = $("#categorySelect").val();
    const selected_tag = $("#tagSelect").val();
    const min_price = $("#minPrice").val();
    const max_price = $("#maxPrice").val();
    const name_seach = $("#nameSearch").val();
    const order = $("#orderSelect").val();
    const perPage = $("#perPage").val();
    const query = {
      search: name_seach,
      order: order,
      category: selected_category,
      tag: selected_tag,
      min_price: min_price,
      max_price: max_price,
      per_page: perPage,
      stock_status: "instock",
    };
    if (min_price && max_price) {
      if (Number(min_price) > Number(max_price)) {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Minimum price should be lower than the maximum price.",
        });
        return 1;
      }
    }
    current_page = 1;
    productFilter = query;
    $(this).find(".loader").fadeIn();
    loadProducts(serializeObject(query));
  });

  function nextPage() {
    current_page += 1;
    loadProducts(serializeObject(productFilter));
  }

  function prevPage() {
    current_page -= 1;
    loadProducts(serializeObject(productFilter));
  }
});

function addToCart(btn, id, sku, remove = false) {
  let cart_items = storageGet("listing_cart") || [];

  const item_exist = cart_items.find((item) => item.source_product_id == id);

  if (item_exist && !remove) {
    debugger;
    return;
  } else if (item_exist && remove) {
    const filter_cart_items = cart_items.filter(
      (item) => item.source_product_id == id
    );
    cart_items = filter_cart_items;
  }

  btn.setAttribute(
    "onclick",
    `addToCart(this, ${id}, '${sku}', ${remove ? "false" : "true"})`
  );
  btn.innerHTML = `${
    remove ? "Removing" : "Adding"
  } to cart.. <div class="loader mini"></div>`;

  btn.disabled = true;

  let data = {
    source_product_id: id,
    source_site_url: "https://allstuff420.com",
    sku: sku,
    remove: remove,
  };
  if (!remove) {
    cart_items.push(data);
  }

  action = "add_to_cart_list";
  if (remove) {
    action = "remove_product_in_cart";
  }
  jQuery.ajax({
    type: "POST",
    url: `${wp_ajax.url}?action=${action}`,
    data: data,

    success: function (response) {
      btn.innerHTML = remove ? "Enlist Product" : "Added to Cart";
      btn.classList.remove(remove ? "on-cart" : "add");
      btn.classList.add(remove ? "add" : "on-cart");
      btn.disabled = false;
      storageSave("listing_cart", cart_items);
    },
  });
}
