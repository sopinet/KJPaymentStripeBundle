<?php

namespace KJ\Payment\StripeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StripeCreditCardType extends AbstractType
{
    public function getName()
    {
        return 'stripe_credit_card';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'Your name',
                'required' => false,
            ))
            ->add('number', TextType::class, array(
                'label' => 'Card number',
                'attr' => array(
                    'maxlength' => 19,
                ),
                'required' => false,
            ))
            ->add('exp_month', ChoiceType::class, array(
                'label' => 'Card expiry',
                'choices' => array('MM' => 'MM', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'),
                'empty_data' => 'MM',
                'attr' => array(
                    'class' => 'input-mini',
                ),
                'required' => false,
            ))
            ->add('exp_year', ChoiceType::class, array(
                'label' => ' ',
                'empty_data' => 'YYYY',
                'choices' => array_merge(array('YYYY'), array_combine(range(date('Y'), date('Y') + 40), range(date('Y'), date('Y') + 40))),
                'attr' => array(
                    'class' => 'input-small',
                ),
                'required' => false,
            ))
            ->add('cvc', 'text', array(
                'label' => 'CVC',
                'attr' => array(
                    'class' => 'input-mini',
                    'maxlength' => 4,
                ),
                'required' => false,
            ))
            ->add('address_line1', TextType::class, array(
                'label' => 'Billing address',
                'required' => false,
            ))
            ->add('address_line2', TextType::class, array(
                'required' => false,
            ))
            ->add('address_city', TextType::class, array(
                'label' => 'City',
                'required' => false,
            ))
            ->add('address_state', TextType::class, array(
                'label' => 'State',
                'required' => false,
            ))
            ->add('address_country', TextType::class, array(
                'label' => 'Country',
                'required' => false,
            ))
            ->add('address_zip', TextType::class, array(
                'label' => 'Postcode / Zip code',
                'attr' => array(
                    'class' => 'input-small',
                ),
                'required' => false,
            ));
    }
}
