<?php

namespace App\Controller\Kassabok;

use App\Entity\Organization;
use App\Entity\Journal;
use App\Service\FinancialReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FinancialReportsController extends AbstractController
{
    public function __construct(
        private FinancialReportService $service
    ) {
    }

    #[IsGranted('view', 'organization')]
    #[Route('/proj/journals/{organization}/{journal}/resultatrakning', name: 'kassabok_resultatrakning', requirements: ['organization' => '\d+', 'journal' => '\d+'])]
    public function incomeStatement(Organization $organization, Journal $journal): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $report = $this->service->getIncomeStatement($journal);

        return $this->render('kassabok/journal/resultatrakning.html.twig', [
            'organization' => $organization,
            'journal' => $journal,
            'report' => $report
        ]);
    }

    #[IsGranted('view', 'organization')]
    #[Route('/proj/journals/{organization}/{journal}/balansrakning', name: 'kassabok_balansrakning', requirements: ['organization' => '\d+', 'journal' => '\d+'])]
    public function balanceSheet(Organization $organization, Journal $journal): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $report = $this->service->getBalanceSheet($journal);

        return $this->render('kassabok/journal/balansrakning.html.twig', [
            'organization' => $organization,
            'journal' => $journal,
            'report' => $report
        ]);
    }
}
