<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class JournalYearEndClosureFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Nollställ resultatkonton',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ]);
    }
}
