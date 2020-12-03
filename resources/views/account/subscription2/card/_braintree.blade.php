<?php
  $class = '\\Acelle\\Cashier\\Services\\' . ucfirst($gateway['name']) . 'GatewayService';
  $gatewayService = new $class($gateway['fields']);
?>

<script src="https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js"></script>
<div id="dropin-container"></div>
  <div class="payment-method-not-selected">
    <button class="btn btn-primary bg-grey" id="submit-button">{{ trans('messages.payment.braintree.request_payment_method') }}</button>
  </div>
  <div class="payment-method-selected hide submit-button">
    <form action="{{ action('AccountSubscriptionController@subscribe', [
          'gateway' => $gateway['name'],
          'plan_uid' => $plan->uid,
          '_token' => csrf_token(),
      ]) }}"
      method="POST">
        <input type="hidden" name="nonce" value="" />
        <button type="submit" class="btn btn-primary bg-grey pay-button">{{ trans('messages.payment.braintree.pay') }}</a>
    </form>

  </div>
<script>
  var button = document.querySelector('#submit-button');
  var dropinInstance;
  
  braintree.dropin.create({
    authorization: '{{ $gatewayService->getClientToken(Auth::user()->customer->getRemoteOwnerId()) }}',
    container: '#dropin-container'
  }, function (createErr, instance) {
    button.addEventListener('click', function () {
      instance.requestPaymentMethod(function (err, payload) {
        // Submit payload.nonce to your server
        console.log(payload);
        console.log(err);
        
        if (err) {
          swalError(err);// Handle errors
        } else {
          $('[name="nonce"]').val(payload.nonce);
        }
      });
      
      if (instance.isPaymentMethodRequestable()) {
        // This will be true if you generated the client token
        // with a customer ID and there is a saved payment method
        // available to tokenize with that customer.
        $('.payment-method-not-selected').addClass('hide');
        $('.payment-method-selected').removeClass('hide');
      }
      
      instance.on('paymentMethodRequestable', function (event) {
        $('.payment-method-not-selected').addClass('hide');
        $('.payment-method-selected').removeClass('hide');
      });
      
      instance.on('noPaymentMethodRequestable', function () {
        $('.payment-method-not-selected').removeClass('hide');
        $('.payment-method-selected').addClass('hide');
      });
    });
  });
  

</script>