<?php

namespace App\Controller\Kassabok;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Organization;
use App\Form\OrganizationFormType;
use Symfony\Bundle\SecurityBundle\Security;

class OrganizationController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/proj/organization', name: 'kassabok_organization')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $organization = new Organization();
        $form = $this->createForm(OrganizationFormType::class);
        $form->add('save', SubmitType::class, [
            'label' => 'Create organization']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organization = $form->getData();
            $organization->addUser($this->getUser());

            $entityManager->persist($organization);
            $entityManager->flush();
            return $this->redirectToRoute('kassabok_organization');
        }

        return $this->render('kassabok/organization/index.html.twig', [
            'controller_name' => 'OrganizationController',
            'organizations' => $entityManager->getRepository(Organization::class)->findByUser($this->getUser()),
            'form' => $form,
        ]);
    }

    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $organization = new Organization();

        $form = $this->createForm(OrganizationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organization = $form->getData();

            return $this->redirectToRoute('kassabok_organization');
        }
    }
}
