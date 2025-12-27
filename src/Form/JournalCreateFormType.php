<?php

namespace App\Form;

use App\Entity\ChartOfAccounts;
use App\Entity\Journal;
use App\Repository\ChartOfAccountsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JournalCreateFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('chartOfAccounts', EntityType::class, [
                'class' => ChartOfAccounts::class,
                'choice_label' => 'name',
                'query_builder' => function (ChartOfAccountsRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.isStandard = true')
                        ->orderBy('c.basVersion', 'DESC');
                },
                'label' => 'Kontoplan',
                'placeholder' => 'Välj kontoplan',
            ])
            ->add('firstDay', DateType::class, [
                'label' => 'Räkenskapsår från',
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'Ange räkenskapsårets första dag'],
            ])
            ->add('lastDay', DateType::class, [
                'label' => 'Räkenskapsår till',
                'widget' => 'single_text',
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
