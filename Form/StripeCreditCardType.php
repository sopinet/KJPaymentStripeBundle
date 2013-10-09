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
				'required' => true,
				'error_type' => 'block',
			))
			->add('number', 'text', array(
				'label' => 'Card number',
				'required' => true,
				'error_type' => 'block',
				'attr' => array(
					'maxlength' => 19,
				),
			))
			->add('exp_month', 'choice', array(
				'label' => 'Card Expiry',
				'required' => true,
				'error_type' => 'block',
                'choices' => array('01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'),
                'empty_value' => 'MM',
				'attr' => array(
					'class' => 'input-mini',
				),
			))
			->add('exp_year', 'choice', array(
				'label' => ' ',
				'required' => true,
				'error_type' => 'block',
                'empty_value' => 'YYYY',
                'choices' => range(date('Y'), date('Y')+20),
				'attr' => array(
                    'class' => 'input-small',
				),
			))
			->add('cvc', 'text', array(
				'label' => 'CVC',
				'required' => true,
				'error_type' => 'block',
				'attr' => array(
                    'class' => 'input-mini',
					'maxlength' => 4,
				),
			))
			->add('address_line1', 'text', array(
				'label' => 'Billing Address',
				'required' => true,
				'error_type' => 'block',
			))
			->add('address_line2', 'text', array(
				'label' => false,
				'required' => false,
				'error_type' => 'block',
			))
			->add('address_city', 'text', array(
				'label' => 'City',
				'required' => true,
				'error_type' => 'block',
			))
			->add('address_state', 'text', array(
				'label' => 'State',
				'required' => true,
				'error_type' => 'block',
			))
			->add('address_country', 'text', array(
				'label' => 'Country',
				'required' => true,
				'error_type' => 'block',
			))
			->add('address_zip', 'text', array(
				'label' => 'Postcode / Zip code',
				'required' => true,
				'error_type' => 'block',
                'attr' => array(
                    'class' => 'input-small',
                ),
			));
    }
}