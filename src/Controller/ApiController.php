<?php

namespace App\Controller;

use App\Service\QuoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    public function __construct(protected QuoteService $quoteService)
    {
    }

    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {

        $data = [
            'api' => [
                'api_currency' => [
                    'path' => '/api/currency',
                    'description' => 'Provides a list of currencies.'
                ],
                'api_game' => [
                'path' => '/api/game',
                'description' => 'Provides the current state of the game.'
                ],
                'api_quotes' => [
                'path' => '/api/quote',
                'description' => 'Provides paraphrased quotes, written by chatGPT, from a fictional character named Assad. The quotes are based on a book series called Department Q by Jussi Adler-Olsen.'
                ],
                'api_deck' => [
                'path' => '/api/deck',
                'description' => 'Provides a French-suited deck of cards, sorted by suit and value.'
                ],
                'api_deck_shuffle' => [
                'path' => '/api/deck/shuffle',
                'description' => 'Shuffles the French-suited deck of cards.'
                ],
                'api_deck_draw' => [
                'path' => '/api/deck/draw',
                'description' => 'Draws one card from the French-suited deck of cards.'
                ],
                'api_deck_draw_number/4' => [
                'path' => '/api/deck/draw/{number}',
                'description' => 'Draws a specified number of cards from the French-suited deck of cards.'
                ],
                'api_deck_deal' => [
                'path' => '/api/deck/deal/{players}/{cards}',
                'description' => 'Deals a specified number of cards to a specified number of players from the French-suited deck of cards.'
                ],
                'api_deck_reset' => [
                'path' => '/api/deck/reset',
                'description' => 'Resets the French-suited deck of cards.'
                ]
            ]
        ];
        return $this->render('api/index.html.twig', $data);
    }

    #[Route('/api/quote', name: 'app_quotes')]
    #[Route('/api/quote', name: 'api_quotes')]
    public function assadsQuotes(): JsonResponse
    {
        $quote = $this->quoteService->getAssadsQuote();
        return $quote;
    }
}
