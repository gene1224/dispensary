const productEndpoint = "/wp-json/wc/v3/products";
const categoryEndpoint = "/wp-json/wc/v3/products/categories";
const tagEndpoint = "/wp-json/wc/v3/products/tags";
const gridLoaderHTML = `<div class="custom-spin-loader"></div>`;

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
  if(productExist) {
      return '';
  }
  let cart_items = storageGet("listing_cart") || [];

  cart_items =
    typeof cart_items === "object" ? objectToArray(cart_items) : cart_items;

  const productOnCart = cart_items.find(
    (item) => item.source_product_id == product.id
  );

  return `<div class="product-item">
	<div class="product-image">
      <a 
        href="${
          product.images[0]?.src || "https://dummyimage.com/160x160/fff/000000"
        }"
        data-lightbox="Product-${product.sku}"
        data-title="${product.name}"
      >
        <img src="${
          product.images[0]?.src || "https://dummyimage.com/160x160/fff/000000"
        }">
      </a>
        ${tagsHTML(product.tags)}
	</div>
	<div class="product-descriptions">
		<div class="product-sku--rating">
			<div class="product-sku">
				<span>SKU:</span>
				<span>${product.sku}</span>
			</div>
			<div class="product-rating">
				${ratingStarsHTML(product.average_rating)}
			</div>
		</div>
		<div class="product-name">${product.name}</div>
		<div class="product-price">$ ${Number(product.price).toFixed(2)}</div>
	</div>
	<div class="product-actions">
        ${productItemButton(product, productExist, productOnCart)}
	</div>
	<!-- <div class="product-source">
		Source :
		<a href="https://allstuff420.com">All Stuff 420</a>
	</div> -->
</div>`;
};

jQuery(document).ready(function ($) {
  createCharts();

  if (wp_ajax.max_products == 50) {
    $("#membership_pro").hide();
    $("span.or_span").hide();
  }
  $("#popmake-6637 .go-free a").on("click", function () {
    $("#popmake-6637").popmake("close");
  });
  $("#popmake-6627 .go-free a").on("click", function () {
    $("#popmake-6627").popmake("close");
  });
  if (wp_ajax.listing_cart) {
    storageSave("listing_cart", wp_ajax.listing_cart);
  }
  
  const default_query_string = {
    orderby: "menu_order",
    order: "asc",
    per_page: 20,
    stock_status: "instock",
  };

  let productFilter = default_query_string;

  let current_page = 1;

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
      success: function (products, textStatus, request) {
        const total_products = request.getResponseHeader("x-wp-total");
        const total_page = request.getResponseHeader("x-wp-totalpages");

        if (products.length == 0) {
          $("#product-importer-grid").html("<h2>No Products Found</h2>");
        }

        $("#currentPage").text(current_page);
        const productHTMLs = products
          .map((product) => {
            return productItemHTML(product, selected_ids);
          }).filter(el => el !== '').slice(0, 4);
          
          
        if (productHTMLs.length == 0) {
          $("#product-importer-grid").html("<h2>All Best Sellers is already imported</h2>");
        } else {
          $("#custom-spin-loader").hide();
          $("#product-importer-grid").html(productHTMLs.join(""));
          var cards = $("#product-importer-grid .product-item");
          for (var i = 0; i < cards.length; i++) {
            var target = Math.floor(Math.random() * cards.length - 1) + 1;
            var target2 = Math.floor(Math.random() * cards.length - 1) + 1;
            cards.eq(target).before(cards.eq(target2));
          }
          // Added
          if (total_page > 1) {
            $(".grid-pagination").show();
          } else {
            $(".grid-pagination").hide();
          }
          // End Added
        }
        // End Added
        if (fromFilter) {
          $("#applyFilter").removeAttr("disabled");
          $("#applyFilter").find(".custom-spin-loader").fadeOut();
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
      search: "",
      order: order,
      category: selected_category,
      tag: selected_tag,
      min_price: "",
      max_price: "",
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
    $(this).find(".custom-spin-loader").fadeIn();
    loadProducts(serializeObject(query), true);
  });
});

function addToCart(btn, id, sku, remove = false) {
  let cart_items = storageGet("listing_cart") || [];

  cart_items =
    typeof cart_items === "object" ? objectToArray(cart_items) : cart_items;

  const item_exist = cart_items.find((item) => item.source_product_id == id);

  if (item_exist && !remove) {
    return;
  }

  const remaining_products =
    wp_ajax.max_products - wp_ajax.imported_products.length - cart_items.length;

  if (remaining_products <= 0 && !remove) {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      html: `<p>
          You've reach maximum product on your Dispensary. You can upgrade your plan to add more products.
          <a href="https://qrxdispensary.com/checkout/?add-to-cart=2850">Pro Plan</a> or 
          <a href="https://qrxdispensary.com/checkout/?add-to-cart=2851">Premium Plan</a> 
        </p>`,
    });
    return;
  }
  btn.setAttribute(
    "onclick",
    `addToCart(this, ${id}, '${sku}', ${remove ? "false" : "true"})`
  );

  btn.innerHTML = `${
    remove ? "Removing" : "Adding"
  } to cart.. <div class="custom-spin-loader mini"></div>`;

  btn.disabled = true;

  let data = {
    source_product_id: id,
    source_site_url: "https://allstuff420.com",
    sku: sku,
    remove: remove,
  };

  action = remove ? "remove_product_in_cart" : "add_to_cart_list";

  jQuery.ajax({
    type: "POST",
    url: `${wp_ajax.url}?action=${action}`,
    data: data,
    success: function (response) {
      var added_products_count = document.getElementById(
        "added_products_count"
      );
      added_products_count.innerHTML = remove
        ? `${wp_ajax.cart_count - wp_ajax.imported_products_count}`
        : `${wp_ajax.cart_count + wp_ajax.imported_products_count}`;
      btn.innerHTML = remove ? "Enlist Product" : "Added to Cart";
      btn.classList.remove(remove ? "on-cart" : "add");
      btn.classList.add(remove ? "add" : "on-cart");
      btn.disabled = false;
      try {
        storageSave("listing_cart", JSON.parse(response));
      } catch (error) {}
    },
  });
}

const weeks_dates = () => {
  var result = [];
  for (var i = 0; i < 7; i++) {
    var d = new Date();
    d.setDate(d.getDate() - i);
    result.push(d.toISOString().slice(0, 10));
  }
  return result.reverse();
};

function createCharts() {
  

  const pageViewData = {
    label: "Page Views",
    data: weeks_dates().map((date) => {
      const visit = wp_ajax.pageview_data.find(
        (data) => date == data.date_visited
      );
      return visit ? visit.count : 0;
    }),
    fill: false,
    borderColor: "rgb(75, 2, 192)",
    tension: 0.5,
  };

  const visitorData = {
    label: "Visitor Count",
    data: weeks_dates().map((date) => {
      const visit = wp_ajax.visitor_data.find(
        (data) => date == data.date_visited
      );
      return visit ? visit.count : 0;
    }),
    fill: false,
    borderColor: "rgb(75, 192, 192)",
    tension: 0.5,
  };
  
  const salesLastWeekData = {
    label: "Sales",
    data: weeks_dates().map((date) => {
      const sales = wp_ajax.last_weeks_orders.find(
        (data) => date == data.date
      );
      return sales ? sales.total : 0;
    }),
    fill: false,
    borderColor: "rgb(34, 139, 34)",
    tension: 0.5,
  };
  
  
    
    const visitChart = document.getElementById("visitChart");
  const pageViewChart = document.getElementById("pageViewChart");
  const salesChart = document.getElementById("salesChart");
  lineGraph(salesChart, salesLastWeekData, "This weeks sales");
  lineGraph(visitChart, visitorData, "This weeks visitors data");
  lineGraph(pageViewChart, pageViewData, "This weeks page view data");
}

function lineGraph(el, data, title = "") {
  let visitorLineChart = new Chart(el, {
    type: "line",
    data: {
      labels: weeks_dates().map(date => date.split("-").splice(-2).join("/")),
      datasets: [data],
    },
    options: {
      responsive: true,
      maintainAspectRatio:false,
      plugins: {
        title: {
          display: true,
          text: title,
        },
      },
      scales: {
        yAxes: [{
            
        ticks: {
          beginAtZero: true,
          maxTicksLimit: 5,
          stepSize: 1,
        }
      }]
      },
    },
  });
}
