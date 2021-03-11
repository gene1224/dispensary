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
        For Example:<br/>
        $10.99 is the Dispensary Price<br/>
        $15.99 is your Retail Price<br/>
        When a customer orders an item and pays, the $10.99 will be deducted to $15.99 for the Enlisting Payment.<br/>
        It will now appear as $5 in your balance, and then it will deduct 10% as a transaction fee.<br/><br/>
        The revenue of the item to your account will be $4.5.</div>
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
//END ADDED

const objectToArray = (object) => {
  return Object.keys(object).map((i) => object[i]);
};
