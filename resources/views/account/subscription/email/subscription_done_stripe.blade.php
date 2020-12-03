<p>{!! trans('messages.subscription_done.stripe.email', [
    'customer' => $customerName,
    'plan' => $planName,
    'link' => $link,
]) !!}</p>

--<br>
{{ \Acelle\Model\Setting::get('site_name') }}
