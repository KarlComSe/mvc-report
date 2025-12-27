<?php

namespace App\Controller\Kassabok;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class AboutController extends AbstractController
{
    #[Route('/proj/about', name: 'kassabok_about')]
    public function index(): Response
    {
        return $this->render('kassabok/about/about.html.twig');
    }

    #[Route('/proj/about/database', name: 'kassabok_database')]
    public function databasePage(): Response
    {
        return $this->render('kassabok/about/database.html.twig');
    }
}
