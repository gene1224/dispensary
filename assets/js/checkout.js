jQuery(document).ready(function ($) {
  /*added*/
  $("input.thwcfe-input-field").on("click", function () {
    $("span.description").removeAttr("style");
  });
  $("input.thwcfe-input-field").on("focus", function () {
    $("span.description").removeAttr("style");
  });
  $("input.thwcfe-input-field").on("focusout", function () {
    $("span.description").removeAttr("style");
  });
  $("span.description").removeAttr("style");
  $("abbr.required").each(function (index) {
    $(this).hover(function () {
      $(this).attr("title", "This field is required");
    });
  });
  //$(".woocommerce-order").filter('p').first().remove();
  /*end added*/
  $("#_wc_memberships_profile_field_template_selected_field").prepend(`
  <div class="template-preivew-container">
      <p class="template-preivew-p"><strong>Template Preview</strong></p>
      <span>You may change your template later. However, we recommend you decide on the final template now as changes will put your website on-hold while processing. 
      See all templates <a href="/template-gallery/" target="_blank">here</a>.</span>
      <br/>
      <a id="templatePreviewLink" href="#"><img id="templatePreview" src="" style="display:none;"></a>
  </div>
`);

  jQuery(`#_wc_memberships_profile_field_template_selected`)
    .parent()
    .append(
      `<select name="template_selector" id="template_selector">
    <option selected="selected" disabled>Select Template</option>
    ${wp_ajax.templatesAvailable
      .map(
        (template) =>
          `<option value=${template.blog_id}>${template.name}</option>`
      )
      .join("")}</select>`
    );
  jQuery(`#_wc_memberships_profile_field_template_selected`).hide();
  //LOCALIZE THIS SOON
  const templatesAvailable = wp_ajax.templatesAvailable;
  $(`#template_selector`).change(function () {
    const selecteTemplate = templatesAvailable.find(
      (template) => template.blog_id == $(this).val()
    );
    $("#_wc_memberships_profile_field_template_selected").attr(
      "value",
      $(this).val()
    );
    $("#templatePreview").attr(
      "src",
      selecteTemplate.thumbnail ||
        "https://dummyimage.com/300x300/ddd/000.png&text=Preview"
    );
    $("#templatePreview").show();
    $("#templatePreviewLink").attr({
      href: "https://templates.qrxdispensary.com/" + selecteTemplate.post_name,
      target: "_blank",
      title: "Click To Preview The " + selecteTemplate.name,
    });
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
    }).always(() => {});
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
    }).always(() => {});
  });
});
