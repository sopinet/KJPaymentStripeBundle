<?php

namespace KJ\Payment\StripeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class StripeCreditCardMinimumType extends AbstractType
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
                'required' => false,
            ))
            ->add('number', 'text', array(
                'label' => 'Card number',
                'attr' => array(
                    'maxlength' => 19,
                ),
                'required' => false,
            ))
            ->add('exp_month', 'choice', array(
                'label' => 'Card expiry',
                'choices' => array('MM' => 'MM', '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'),
                'empty_data' => 'MM',
                'attr' => array(
                    'class' => 'input-mini',
                ),
                'required' => false,
            ))
            ->add('exp_year', 'choice', array(
                'label' => ' ',
                'empty_data' => 'YYYY',
                'choices' => array_merge(array('YYYY'), array_combine(range(date('Y'), date('Y') + 20), range(date('Y'), date('Y') + 20))),
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
            ));
    }
}
