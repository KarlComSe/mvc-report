<?php

namespace App\Controller\Kassabok;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StartController extends AbstractController
{
    #[Route('/proj/', name: 'kassabok_start')]
    public function index(): Response
    {
        return $this->render('kassabok/base.html.twig');
    }
}
