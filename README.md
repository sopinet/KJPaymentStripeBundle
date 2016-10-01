KJPaymentStripeBundle
=====================

Payment Bundle providing access to the Stripe API

# Installation

## composer

composer require sopinet/KJPaymentStripeBundle

## AppKernel

add to AppKernel: 
    new KJ\Payment\StripeBundle\KJPaymentStripeBundle(),

## config.yml

```yaml
// app/config/config.yml
kj_payment_stripe:
    api_key: sk_test_blablabla # ApiKey from Stripe
    api_version: "2016-07-06" # Last version from Stripe
```

# Configuration in JMSPaymentBundle

You need add:
"stripe_card_credit" method to your JMSPaymentBundle form

# Optional: configure form for credit card

You can use full form for credit card by default, with address options and others.

But you can configure another minimal form too, you can override this parameter so:
```
payment.form.stripe_credit_card_type.class: KJ\Payment\StripeBundle\Form\StripeCreditCardMinimumType
```

By last, you could to do your own form too