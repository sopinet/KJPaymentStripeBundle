<?php

namespace KJ\Payment\StripeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StripeCreditCardMinimumType extends AbstractType
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
                'choices' => array_combine(
                    range(1, 12),
                    range(1, 12)
                ),
                'empty_data' => date('n'),
                'attr' => array(
                    'class' => 'input-mini',
                ),
                'required' => false,
            ))
            ->add('exp_year', ChoiceType::class, [
                'label' => ' ',
                'empty_data' => date('Y'),
                'choices' => array_combine(
                    range(date('Y'), (date('Y') + 20)),
                    range(date('Y'), (date('Y') + 20))
                ),
                'attr' => [
                    'class' => 'input-small',
                ],
                'required' => false,
            ])
            ->add('cvc', TextType::class, array(
                'label' => 'CVC',
                'attr' => array(
                    'class' => 'input-mini',
                    'maxlength' => 4,
                ),
                'required' => false,
            ));
    }
}
