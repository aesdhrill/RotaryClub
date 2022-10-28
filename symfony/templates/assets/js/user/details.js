$(function () {
  $('#user_roles_edit_btn').on('click', function () {
    $(this).closest('.user_roles_show').siblings('.user_roles_edit_form').fadeIn(0);
    $(this).closest('.user_roles_show').fadeOut(0);
  });

  $('#user_roles_edit_cancel_btn').on('click', function (e) {
    e.preventDefault();
    $(this).closest('.user_roles_edit_form').siblings('.user_roles_show').fadeIn(0);
    $(this).closest('.user_roles_edit_form').fadeOut(0);
  });

  $('#user_status_edit_btn').on('click', function () {
    $(this).closest('.user_status_show').siblings('.user_status_edit_form').fadeIn(0);
    $(this).closest('.user_status_show').fadeOut(0);
  });

  $('#user_status_edit_cancel_btn').on('click', function (e) {
    e.preventDefault();
    $(this).closest('.user_status_edit_form').siblings('.user_status_show').fadeIn(0);
    $(this).closest('.user_status_edit_form').fadeOut(0);
  });

  $('#user_expiry_date_edit_btn').on('click', function () {
    $(this).closest('.user_expiry_date_show').siblings('.user_expiry_date_edit_form').fadeIn(0);
    $(this).closest('.user_expiry_date_show').fadeOut(0);
  });

  $('#user_expiry_date_edit_cancel_btn').on('click', function (e) {
    e.preventDefault();
    $(this).closest('.user_expiry_date_edit_form').siblings('.user_expiry_date_show').fadeIn(0);
    $(this).closest('.user_expiry_date_edit_form').fadeOut(0);
  });
});
