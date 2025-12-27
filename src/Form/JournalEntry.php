<?php

namespace App\Form;

use App\Entity\JournalEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JournalEntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $chartOfAccounts = $options['chart_of_accounts'];

        $builder
            ->add('title', TextType::class, [
                'label' => 'Verifikattext',
                'attr' => ['placeholder' => 'Beskriv transaktionen'],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Datum',
                'html5' => true,
            ])
            ->add('journalLineItems', CollectionType::class, [
                'entry_type' => JournalLineItemType::class,
                'entry_options' => [
                    'label' => false,
                    'chart_of_accounts' => $chartOfAccounts,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false, //'Konteringar',
                'prototype' => true,
                'attr' => [
                    'class' => 'line-items-collection',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Spara verifikat',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JournalEntry::class,
            'chart_of_accounts' => null,
        ]);

        $resolver->setRequired('chart_of_accounts');
    }
}
