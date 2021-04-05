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
        <div style="text-align: justify;">For the retail price, you can change the price for each item that you will enlist. 
        The retail price will consist of the wholesale dispensary amount plus the minimum 10% mark-up price. 
        The maximum mark-up percentage is only up to 50%.
        <br/><br/>
        For a sample computation of your revenue on your Dispensary, let's take this as an example. 
        When a customer orders an item to your dispensary and pays that product, our system will automatically deduct the dispensary price under your retail price and will remove the 10% for the Transaction fee. 
        So the remaining will be your revenue for that product.
        <br/><br/>
        For example, the Dispensary Price is $10.99, and the Retail Price is $15.99. 
        <br/><br/>
        When a customer orders an item and pays, the Dispensary Price will be deducted from the Retail Price as Enlisting Payment. Now, your remaining balance will be $5, and there will be a 10% deduction as a transaction fee. The revenue of the item on your account will now be $4.5. 
       </div>
    `;
  Swal.fire({
    title: "Product Revenue Information",
    icon: "info",
    html: planHTML(),
    showCloseButton: true,
    focusConfirm: false,
    confirmButtonText: "Done",
  });
}

function addProductInfo() {
  const planHTML = () => `
        <div style="text-align: justify;">
        1. Browse the exciting AllStuff420 products. You can use the search filters if you are looking for specific products.<br/><br/>
        2. Once you found the products you want to add, press “Enlist Product”.<br/><br/>
        3. When you are done adding all the products you wish to enlist, go to “VIEW PRODUCTS ADDED”.<br/><br/>
        4. Add your preferred price for the product to ensure your revenue. <br/><br/>
        5. Once you are done with the pricing, press “Enlist products to dispensary”<br/><br/>
        </div>
    `;
  Swal.fire({
    title: "How To Add Products",
    icon: "info",
    html: planHTML(),
    showCloseButton: true,
    focusConfirm: false,
    confirmButtonText: "Done",
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
