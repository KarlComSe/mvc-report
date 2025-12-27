<?php

namespace App\Controller\Kassabok;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class StartController extends AbstractController
{
    #[Route('/proj/', name: 'kassabok_start')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(\App\Entity\Organization::class);
        $organizations = $repository->findAll();
        // dump($organizations);

        return $this->render('kassabok/base.html.twig', ['organizations' => $organizations]);
    }
}
