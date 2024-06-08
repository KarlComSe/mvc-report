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

class JournalsController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/proj/journals/{id}', name: 'kassabok_journals')]
    public function journals(Request $request, EntityManagerInterface $entityManager, Organization $organization): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $jorunal = new Journal();
        $form = $this->createForm(JournalCreateFormType::class);
        $form->add('save', SubmitType::class, [
            'label' => 'Lägg till',]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $journal = $form->getData();
            $journal->addOrganization($organization);

            $entityManager->persist($journal);
            $entityManager->flush();
            return $this->redirectToRoute('kassabok_journals', ['id' => $organization->getId()]);
        }

        return $this->render('kassabok/journal/index.html.twig', [
            'organization' => $organization,
            'journals' => $organization->getJournals(),
            'form' => $form,
        ]);
    }

    // public function new(Request $request): Response
    // {
    //     $this->denyAccessUnlessGranted('ROLE_USER');

    //     $journal = new Journal();

    //     $form = $this->createForm(JournalCreateFormType::class);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $journal = $form->getData();

    //         return $this->redirectToRoute('kassabok_journals');
    //     }
    // }
}
