<?php

namespace App\Controller\Kassabok;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use App\Form\OrganizationFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class OrganizationController extends AbstractController
{
    #[Route('/proj/organization', name: 'kassabok_organization')]
    public function index(Request $request, EntityManagerInterface $entityManager, OrganizationRepository $orgRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $organization = new Organization();
        $form = $this->createForm(OrganizationFormType::class);
        $form->add('save', SubmitType::class, [
            'label' => 'Lägg till',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organization = $form->getData();
            $organization->addUser($this->getUser());

            $entityManager->persist($organization);
            $entityManager->flush();
            return $this->redirectToRoute('kassabok_organization');
        }
        /** @var User $user */
        $user = $this->getUser();

        $organizations = $orgRepository->findByUser($user);

        return $this->render('kassabok/organizations/index.html.twig', [
            'controller_name' => 'OrganizationController',
            'organizations' => $organizations,
            'form' => $form,
        ]);
    }
}
