<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/report', name: 'app_report')]
    public function index(): Response
    {
        return $this->render('report.html.twig');
    }

    #[Route('/metrics', name: 'app_metrics')]
    public function report1(): Response
    {
        return $this->render('metrics/report.html.twig');
    }
}
