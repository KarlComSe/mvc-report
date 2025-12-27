<?php

namespace App\Controller\Kassabok;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Organization;
use App\Entity\Journal;
use App\Service\AccountLedgerService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class JournalAccountLedgerController extends AbstractController
{
    public function __construct(private AccountLedgerService $ledgerService)
    {
    }

    #[IsGranted('view', 'organization')]
    #[Route('/proj/journals/{organization}/{journal}/grundbok', name: 'kassabok_accountledger', requirements: ['organization' => '\d+', 'journal' => '\d+'])]
    public function journalAccountLedger(Organization $organization, Journal $journal): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $ledgerData = $this->ledgerService->getCompleteAccountLedger($journal);

        return $this->render('kassabok/journal/account_ledger.html.twig', [
            'organization' => $organization,
            'journal' => $journal,
            'ledgerData' => $ledgerData,
        ]);
    }
}
