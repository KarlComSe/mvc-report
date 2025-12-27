<?php

namespace App\Controller\Kassabok;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Organization;
use App\Entity\Journal;
use App\Entity\JournalEntry;
use App\Form\JournalEntryFormType;
use App\Form\JournalYearEndClosureFormType;
use App\Service\FinancialReportService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\YearEndService;

class JournalController extends AbstractController
{
    #[IsGranted('view', 'organization')]
    #[Route('/proj/journals/{organization}/{journal}', name: 'kassabok_journal', requirements: ['organization' => '\d+', 'journal' => '\d+'])]
    public function journal(Organization $organization, Journal $journal): Response
    {
        return $this->render(
            'kassabok/journal/index.html.twig',
            [
                'organization' => $organization,
                'journal' => $journal,
                'form' => $this->createForm(JournalEntryFormType::class)
            ]
        );
    }

    #[IsGranted('view', 'journal')]
    #[Route('/proj/journals/{journal}/add-entry', name: 'kassabok_add_entry', requirements: ['journal' => '\d+'])]
    public function addEntry(Request $request, EntityManagerInterface $entityManager, ?Journal $journal): Response
    {
        $journalEntry = new JournalEntry();
        $journalEntry->setJournal($journal);

        $form = $this->createForm(JournalEntryFormType::class, $journalEntry, [
            'chart_of_accounts' => $journal->getChartOfAccounts(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($journalEntry);
            $entityManager->flush();

            $this->addFlash('success', 'Verifikat sparat!');

            return $this->redirectToRoute('kassabok_journal', [
                'organization' => $journal->getOrganization()->getId(),
                'journal' => $journal->getId()
            ]);
        }

        return $this->render('kassabok/journal/add_entry.html.twig', [
            'form' => $form->createView(),
            'journal' => $journal,
        ]);
    }

    #[IsGranted('view', 'journal')]
    #[Route('/proj/journals/{journal}/add-year-end-closure', name: 'kassabok_add_year_end_closure', requirements: ['journal' => '\d+'])]
    public function addYearEndClosure(Request $request, EntityManagerInterface $entityManager, ?Journal $journal): Response
    {


        $form = $this->createForm(JournalYearEndClosureFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lastDay = $journal->getLastDay();

            $closingService = new YearEndService($entityManager, new FinancialReportService());
            $closingService->closeIncomeStatement(
                $journal,
                $lastDay
            );

            $this->addFlash('success', 'Verifikat sparat!');

            return $this->redirectToRoute('kassabok_journal', [
                'organization' => $journal->getOrganization()->getId(),
                'journal' => $journal->getId()
            ]);
        }

        return $this->render('kassabok/journal/add_year_end_closure.html.twig', [
            'form' => $form->createView(),
            'journal' => $journal,
        ]);
    }
}
