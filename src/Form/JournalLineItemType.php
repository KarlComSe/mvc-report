<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\JournalLineItem;
use App\Repository\AccountRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JournalLineItemType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $chartOfAccounts = $options['chart_of_accounts'];

        $builder
            ->add('account', EntityType::class, [
                'class' => Account::class,
                'choice_label' => function (Account $account) {
                    return $account->getAccountNumber() . ' - ' . $account->getName();
                },
                'query_builder' => function (AccountRepository $repo) use ($chartOfAccounts) {
                    return $repo->createQueryBuilder('a')
                        ->where('a.chartOfAccounts = :chart')
                        ->andWhere('a.isDetailAccount = true')
                        ->orderBy('a.accountNumber', 'ASC')
                        ->setParameter('chart', $chartOfAccounts);
                },
                'label' => 'Konto',
                'placeholder' => 'Välj konto',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('debitAmount', NumberType::class, [
                'label' => 'Debet',
                'required' => false,
                'attr' => [
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'class' => 'form-control',
                ],
            ])
            ->add('creditAmount', NumberType::class, [
                'label' => 'Kredit',
                'required' => false,
                'attr' => [
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JournalLineItem::class,
            'chart_of_accounts' => null,
        ]);

        $resolver->setRequired('chart_of_accounts');
    }
}
