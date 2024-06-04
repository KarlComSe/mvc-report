<?php

namespace App\Controller\Kassabok;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Organization;
use Symfony\Bundle\SecurityBundle\Security;

class OrganizationController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/proj/organization', name: 'kassabok_organization')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // $organizations = $entityManager->getRepository(Organization::class)->findAll();

        // // Filter organizations based on user membership (if not admin)
        // if (!$this->security->isGranted('ROLE_ADMIN')) {
        //     $currentUser = $this->security->getUser();
        //     $organizations = array_filter($organizations, function (Organization $organization) use ($currentUser) {
        //         return $organization->getUsers()->contains($currentUser);
        //     });
        // }
        return $this->render('kassabok/organization/index.html.twig', [
            'controller_name' => 'OrganizationController',
            'organizations' => $entityManager->getRepository(Organization::class)->findByUser($this->getUser()),
        ]);
    }
}
