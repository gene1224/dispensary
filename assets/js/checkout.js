jQuery(document).ready(function ($) {
  console.log(wp_ajax);
  const submitButton = $("#place_order");
  $("#business_information").change(function (e) {
    e.preventDefault();

    const fileEl = document.getElementById("business_information");
    let formData = new FormData();
    formData.append("business_information", fileEl.files[0]);

    
    submitButton.attr("disabled", "disabled");
    $.ajax({
      url: `${wp_ajax.url}?nonce=${wp_ajax.nonce}&action=checkout_document_upload`,
      type: "POST",
      processData: false, // important
      contentType: false, // important
      dataType: "json",
      data: formData,
      success: (response) => {
        if (response.url) {
          $(`input[name="file_attachment_url"]`).val(response.url);
        }
      },
    }).always(() => {
      check_addtional_fields();
    });
  });

  const subdomainInput = $("#subdomain");
  subdomainInput.change(function (e) {
    const subdomainNAme = subdomainInput
      .val()
      .toLowerCase()
      .replace("/[^A-Za-z0-9.-]/", "")
      .replace(`.${wp_ajax.base_domain}`, "");
    const subdomain = `${subdomainNAme}.${wp_ajax.base_domain}`;

    subdomainInput.val(subdomain);

    $.ajax({
      url: `${wp_ajax.url}?nonce=${wp_ajax.nonce}&action=check_subdomain&subdomain=${subdomain}`,
      type: "GET",
      success: (response) => {
        try {
          const res = JSON.parse(response);
          if (res.exist) {
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: `The subdomain ${subdomain} is already taken`,
            }).then(function () {
              subdomainInput.val("");
            });
            return;
          } else {
            subdomainInput.css("border", "2px solid green");
          }
        } catch (error) {}
      },
    }).always(() => {
      check_addtional_fields();
    });
  });

  const domainInput = $("#domain");
  domainInput.change(function (e) {
    const domain = domainInput.val();
    $.ajax({
      url: `${wp_ajax.url}?nonce=${wp_ajax.nonce}&action=check_input_domain&domain=${domain}`,
      type: "GET",
      success: (response) => {
        try {
          const res = JSON.parse(response);
          if (res.domains.length > 0) {
            if (!res.domains[0].available) {
              Swal.fire({
                icon: "error",
                title: "Oops...",
                text: `The domain ${domain} is already taken`,
              }).then(function () {
                domainInput.val("");
              });
              return;
            }
            domainInput.css("border", "2px solid green");
          }
        } catch (error) {}
      },
    }).always(() => {
      check_addtional_fields();
    });
  });

  function check_addtional_fields() {
    if (domainInput.length) {
      if (!domainInput.val()) {
        return;
      }
    } else if (subdomainInput.length) {
      if (!subdomainInput.val()) {
        return;
      }
    }
    if (!$(`input[name="file_attachment_url"]`).val()) {
      return;
    }

    submitButton.removeAttr("disabled");
  }
});
