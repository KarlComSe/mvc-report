<?php

namespace App\Form;

use App\Entity\Journal;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JournalCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstDay', DateType::class, [
                'label' => 'Räkenskapsår från',
                'attr' => ['placeholder' => 'Ange räkenskapsårets första dag'],
            ]);
        $builder
            ->add('lastDay', DateType::class, [
                'label' => 'Räkenskapsår till',
                'attr' => ['placeholder' => 'Ange räkenskapsårets sista dag'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Journal::class,
        ]);
    }
}
