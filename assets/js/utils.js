const storageSave = (name, data, is_object = true) => {
  if (name == "listing_cart") {
    jQuery(".cart-count").html(data.length);
    jQuery("#added_products_count").text(
      wp_ajax.imported_products.length + data.length
    );
    jQuery("#remaining_products_count").text(
      wp_ajax.max_products - wp_ajax.imported_products.length - data.length
    );
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

const price_round = (num) => {
  cents = (num * 100) % 100;
  if (cents >= 25 && cents < 75) {
    return Math.floor(num) + 0.49;
  }
  if (cents < 25) {
    return Math.floor(num) - 0.01;
  }
  return Math.floor(num) + 0.99;
};

function changePlan() {
  const planHTML = () => `
            <a id="membership_pro" href="https://qrxdispensary.com/checkout/?add-to-cart=2850">QRX Pro Plan</a><span class="or_span"> or </span>
            <a id="membership_premium" href="https://qrxdispensary.com/checkout/?add-to-cart=2851">QRX Premium Plan</a>
        `;
  Swal.fire({
    title: "Change Plan?",
    icon: "info",
    html: planHTML(),
    showCloseButton: true,

    focusConfirm: false,
    confirmButtonText: "No thanks",
  });
}

//ADDED
function viewRevenueInfo() {
  const planHTML = () => `
        <div style="text-align: justify;">
        When you enlist products from AllStuff420, you can decide on a mark-up price between 10-50% to ensure your profit. To improve your revenue, you don't have to pre-pay for the products you enlist - we simply deduct the product price + transaction fee once one of your customers order!
        <br><br>
        The product price is the price you will pay for the product + 10% in transaction fee, which is why the minimum mark-up is 10%. Any mark-up above 10% will be your profit.
        <br><br>
        EXAMPLE:
        The Product Price is $10.99, and your SRP is $15.99. Once a customer orders the product, we will deduct $10.99 + $1 (10% transaction fee). The remaining $4.5 will be pure profit for you.
       </div>
    `;
  Swal.fire({
    title: "Product Revenue Information",
    icon: "info",
    html: planHTML(),
    showCloseButton: true,
    focusConfirm: false,
    confirmButtonText: "OK",
  });
}

function addProductInfo() {
  const planHTML = () => `
        <div style="text-align: justify;">
        1. Browse the exciting AllStuff420 products. You can use the search filters if you are looking for specific products.<br/><br/>
        2. Once you have found the products you want to add, press “Enlist Product”.<br/><br/>
        3. When you are done adding all the products you wish to enlist, go to “VIEW PRODUCTS ADDED”.<br/><br/>
        4. Add your preferred price for the product. We have suggested an SRP, but you may change this.<br/><br/>
        5. Once you are done with the pricing, press “Enlist products to dispensary”<br/><br/>
        </div>
    `;
  Swal.fire({
    title: "How To Add Products",
    icon: "info",
    html: planHTML(),
    showCloseButton: true,
    focusConfirm: false,
    confirmButtonText: "OK",
  });
}

function addOwnProductInfo() {
  const planHTML = () => `
        <div style="text-align: justify;">
        Once you’re online dispensary is fully set-up, you can directly import your own products. 
        <br/><br/>
        In the meantime, should you wish to opt out from importing products from AllStuff420 you can skip the process and proceed to <a href="/my-account/template-editor/">editing your website account</a>.  
        </div>
    `;
  Swal.fire({
    title: "Add My Own Product",
    icon: "info",
    html: planHTML(),
    showCloseButton: true,
    focusConfirm: false,
    confirmButtonText: "Done",
  });
}
//END ADDED

const objectToArray = (object) => {
  return Object.keys(object).map((i) => object[i]);
};
