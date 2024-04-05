<?php

namespace App\Controller;

use App\Service\QuoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{ 

    protected QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    #[Route('/api', name: 'app_api')]
    public function index()
    {
        $data = [
            'api' => [
                '/api/quote' => 'Provides paraphrased quotes, written by chatGPT, from a fictional character named Assad. The quotes are based on a book series called Department Q by Jussi Adler-Olsen.',
                '/api/number' => 'Provides a random number between 0 and 100.',
            ],
        ];
        return $this->render('api/index.html.twig', $data);
    }

    #[Route('/api/quote', name: 'app_quotes')]
    public function assadsQuotes()
    {
        $quote = $this->quoteService->getAssadsQuote();
        return $quote;     
    }
}