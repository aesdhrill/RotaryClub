const $ = require('jquery');

function openCard($this) {
  $this.closest('.card').find('.account_edit_card_form').fadeIn(0);
  $this.closest('.card').find('.account_card_show').fadeOut(0);
}

$(function () {
  $('.select2').select2({
    selectOnClose: true,
    closeOnSelect: true,
  });

  $(document).on('select2:open', function (e) {
    document.querySelector('.select2-search__field').focus();
  });

  $('.account_edit_btn').on('click', function () {
    $(this).closest('.account_show').siblings('.account_edit_form').fadeIn(0);
    $(this).closest('.account_show').fadeOut(0);
  });

  $('.account_cancel_btn').on('click', function (e) {
    e.preventDefault();
    $(this).closest('.account_edit_form').siblings('.account_show').fadeIn(0);
    $(this).closest('.account_edit_form').fadeOut(0);
  });

  if ($('.account_edit_card_btn').closest('.card').find('.invalid-feedback').length) {
    openCard($('.account_edit_card_btn'));
  }

  $('.account_edit_card_btn').on('click', function () {
    openCard($(this));
  });

  $('.account_cancel_form_btn').on('click', function (e) {
    e.preventDefault();
    $(this).closest('.card').find('.account_card_show').fadeIn(0);
    $(this).closest('.card').find('.account_edit_card_form').fadeOut(0);
  });
});
