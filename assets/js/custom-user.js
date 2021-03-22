$(document).ready(function ($) {
  const emitErrors = (errors) => {
    const usernameError = errors.username
      ? errors.username == "exist"
        ? `Username already exist,`
        : `Invalid username,`
      : "";

    const passwordError = errors.password
      ? errors.password == "not_matched"
        ? "Password does not match,"
        : "Password required,"
      : "";

    const emailError = errors.email
      ? errors.email == "required"
        ? "Email requried,"
        : "Email already exist,"
      : "";

    const lNameError = errors.last_name ? "Last Name required," : "";
    const fNameError = errors.first_name ? "First Name required," : "";

    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: [
        usernameError,
        passwordError,
        emailError,
        fNameError,
        lNameError,
      ].join(" "),
    });
  };
  $("#newUser").submit(function (e) {
    e.preventDefault();
    $.ajax({
      type: "POST",
      url: wp_ajax.url,
      data: $(this).serialize(),
      dataType: "json",
      success: function (response) {
        if (response.errors) {
          emitErrors(response.errors);
          return;
        }
        console.log("SUCCESS");
        Swal.fire(
          "Success",
          `Store Manager ${wp_ajax.edit ? "Updated" : "Created"}!`
        ).then(() => {
          if (!wp_ajax.edit) document.getElementById("newUser").reset();
        });
      },
    });
  });
});
