jQuery(document).ready(function ($) {
  $("#templateEditor").submit(function (e) {
    e.preventDefault();

    $.ajax({
      type: "POST",
      url: wp_ajax.url,
      data: `${$(this).serialize()}&nonce=${wp_ajax.nonce}`,

      success: function (response) {
        Swal.fire("Success", "Template Updated", "success");
      },
    });
  });
});
