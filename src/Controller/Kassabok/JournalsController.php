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
use App\Form\JournalCreateFormType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class JournalsController extends AbstractController
{
    #[Route('/proj/journals/{organization}', name: 'kassabok_journals', requirements: ['organization' => '\d+'])]
    #[IsGranted('view', 'organization')]
    public function journals(Request $request, EntityManagerInterface $entityManager, ?Organization $organization): Response
    {
        $journal = new Journal();
        $form = $this->createForm(JournalCreateFormType::class);
        $form->add('save', SubmitType::class, [
            'label' => 'Lägg till',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $journal = $form->getData();
            $journal->setOrganization($organization);

            $entityManager->persist($journal);
            $entityManager->flush();
            return $this->redirectToRoute('kassabok_journals', ['organization' => $organization->getId()]);
        }

        return $this->render('kassabok/journals/index.html.twig', [
            'organization' => $organization,
            'journals' => $organization->getJournals(),
            'form' => $form,
        ]);
    }
}
