<?php

namespace App\Controller\Kassabok;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrganizationController extends AbstractController
{
    #[Route('/proj/organization', name: 'kassabok_organization')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('kassabok/organization/index.html.twig', [
            'controller_name' => 'OrganizationController',
        ]);
    }
}
