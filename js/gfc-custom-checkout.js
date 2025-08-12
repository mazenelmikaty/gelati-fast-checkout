jQuery(function($){
  var $wrapper = $('.gelati-fast-checkout'),
      $form    = $('#gelati-fast-checkout-form'),
      $submit  = $('#gfc-submit'),
      $error   = $('#gfc-error'),
      $success = $('#gfc-success');

  if ( !$wrapper.length || !$form.length ) {
    return;
  }

  // Hide old notices
  $error.hide();
  $success.hide();

    $form.on('submit', function(e){
      e.preventDefault();
      $submit.prop('disabled', true);
      $error.hide().empty();
      $success
        .show()
        .removeClass('woocommerce-error')
        .addClass('woocommerce-message')
        .text('⏳ Processing your order…');

      const payload = new URLSearchParams(new FormData($form[0]));
      payload.append('action', 'gfc_place_order');

      fetch(gfc_ajax.ajax_url, {
        method: 'POST',
        body: payload
      })
      .then(res => res.json())
      .then(function(json){
        $submit.prop('disabled', false);

        if ( json.success && json.data.order_number ) {
          // Show native success template
          $wrapper.replaceWith( json.data.success_html );
          $(document.body).trigger('wc_fragment_refresh');
        } else {
          $error
            .show()
            .addClass('woocommerce-error')
            .text('❌ ' + (json.data.message || 'Something went wrong.'));
          $success.hide().empty();
        }
      })
      .catch(function(){
        $submit.prop('disabled', false);
        $error
          .show()
          .addClass('woocommerce-error')
          .text('❌ Request failed. Please try again.');
        $success.hide().empty();
      });
    });
  });
  document.addEventListener('DOMContentLoaded', function () {
    const addressFields = document.querySelectorAll('input[name="billing_address_1"], input[name="billing_full_name"], input[name="billing_phone"], input[name="billing_email"]');

  addressFields.forEach(field => {
    field.addEventListener('blur', () => {
      fetchUpdatedCart();
    });
  });

  function fetchUpdatedCart() {
    fetch(gfc_ajax.ajax_url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'gfc_refresh_cart_totals',
      })
    })
    .then(res => res.json())
    .then(data => {
      document.querySelector('.order-subtotal').innerHTML = `<strong>Subtotal:</strong> ${data.subtotal}`;
      document.querySelector('.order-shipping').innerHTML = `<strong>Delivery:</strong> ${data.shipping}`;
      document.querySelector('.order-total').innerHTML = `<strong>Total:</strong> ${data.total}`;
    });
  }
});
