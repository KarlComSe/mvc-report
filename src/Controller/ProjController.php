<?php

namespace App\Controller;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjController extends AbstractController
{
    #[Route('/proj', name: 'app_proj')]
    public function index(): Response
    {
        return $this->render('proj.html.twig');
    }
}
