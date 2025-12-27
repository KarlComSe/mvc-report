<?php

// src/Form/Type/CurrencyType.php

namespace App\Form;

use App\Entity\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('alpabethicCode', TextType::class)
        ->add('decimalDigits', IntegerType::class)
        ->add('entity', TextType::class)
        ->add('numericCode', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Currency::class,
        ]);
    }
}
