<?php

namespace KJ\Payment\StripeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class StripeCreditCardType extends AbstractType
{
    public function getName()
    {
        return 'stripe_credit_card';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Your name',
                'error_type' => 'block',
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('number', 'text', array(
                'label' => 'Card number',
                'error_type' => 'block',
                'attr' => array(
                    'maxlength' => 19,
                ),
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('exp_month', 'choice', array(
                'label' => 'Card expiry',
                'error_type' => 'block',
                'choices' => array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'),
                'empty_value' => 'MM',
                'attr' => array(
                    'class' => 'input-mini',
                ),
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('exp_year', 'choice', array(
                'label' => ' ',
                'error_type' => 'block',
                'empty_value' => 'YYYY',
                'choices' => array_combine(range(date('Y'), date('Y') + 20), range(date('Y'), date('Y') + 20)),
                'attr' => array(
                    'class' => 'input-small',
                ),
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('cvc', 'text', array(
                'label' => 'CVC',
                'error_type' => 'block',
                'attr' => array(
                    'class' => 'input-mini',
                    'maxlength' => 4,
                ),
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('address_line1', 'text', array(
                'label' => 'Billing address',
                'error_type' => 'block',
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('address_line2', 'text', array(
                'required' => false,
                'error_type' => 'block',
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('address_city', 'text', array(
                'label' => 'City',
                'error_type' => 'block',
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('address_state', 'text', array(
                'label' => 'State',
                'error_type' => 'block',
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('address_country', 'text', array(
                'label' => 'Country',
                'error_type' => 'block',
                'required' => false,
                'render_optional_text' => false,
            ))
            ->add('address_zip', 'text', array(
                'label' => 'Postcode / Zip code',
                'error_type' => 'block',
                'attr' => array(
                    'class' => 'input-small',
                ),
                'required' => false,
                'render_optional_text' => false,
            ));
    }
}