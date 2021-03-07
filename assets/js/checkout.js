jQuery(document).ready(function ($) {
  console.log(wp_ajax);

  const templateSelector = $(`select[name="_wc_memberships_profile_field_template_selected"]`);

  //LOCALIZE THIS SOON
  const templatesAvailable = [
    {
      id: 109,
      preview:
        "https://templates.qrxdispensary.com/wp-content/uploads/2021/01/1.-Marketplace-03-WCFM-Thumbnail.png",
    },
  ];
  templateSelector.change(function () {
    const selecteTemplate = templatesAvailable.find(
      (template) => template.id == templateSelector.val()
    );
    $("#templatePreview").attr(
      "src",
      selecteTemplate ||
        "https://dummyimage.com/300x300/ddd/000.png&text=Preview"
    );
  });

  const subdomainInput = $(
    `input[name="_wc_memberships_profile_field_subdomain_name"]`
  );
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

  const domainInput = $(
    `input[name="_wc_memberships_profile_field_domain_name"]`
  );
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
    submitButton.removeAttr("disabled");
  }
});
