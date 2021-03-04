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
        <a href="https://qrxdispensary.com/checkout/?add-to-cart=2850">QRX Pro Plan</a> or 
        <a href="https://qrxdispensary.com/checkout/?add-to-cart=2851">QRX Premium Plan</a>
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

const objectToArray = (object) => {
  return Object.keys(object).map((i) => object[i]);
};
