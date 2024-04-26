<?php

namespace App\Controller;

use App\Service\QuoteService;
use App\Service\DeckService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use App\Model\Game;

class ApiController extends AbstractController
{
    public function __construct(protected QuoteService $quoteService, protected DeckService $deckService)
    {
    }

    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {

        $data = [
            'api' => [
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

    #[Route('/api/deck', name: 'api_deck', defaults: ['deck_needed' => true ])]
    public function deck(Request $request): JsonResponse
    {
        $deck = $this->deckService->getDeck($request);
        return $deck;
    }

    #[Route('/api/deck/shuffle', name: 'api_deck_shuffle', defaults: ['deck_needed' => true ])]
    public function shuffle(Request $request): JsonResponse
    {
        $deck = $this->deckService->shuffleDeck($request);
        return $deck;
    }

    #[Route('/api/deck/draw', name: 'api_deck_draw', defaults: ['deck_needed' => true ])]
    public function draw(Request $request): JsonResponse
    {
        $deck = $this->deckService->drawCard($request);
        return $deck;
    }

    #[Route('/api/deck/draw/{number}', name: 'api_deck_draw_number', defaults: ['deck_needed' => true ])]
    public function drawNumber(int $number, Request $request): JsonResponse
    {
        $deck = $this->deckService->drawCards($number, $request);
        return $deck;
    }

    #[Route('/api/deck/deal/{players}/{cards}', name: 'api_deck_deal', defaults: ['deck_needed' => true ])]
    public function deal(int $players, int $cards, Request $request): JsonResponse
    {
        $deck = $this->deckService->dealCards($players, $cards, $request);
        return $deck;
    }

    #[Route('/api/deck/reset', name: 'api_deck_reset', defaults: ['deck_needed' => true ])]
    public function reset(Request $request): JsonResponse
    {
        $deck = $this->deckService->resetDeck($request);
        return $deck;
    }

    #[Route('/api/game', name: 'api_game')]
    public function game(Request $request): JsonResponse
    {

        $data = [];
        if ($request->getSession()->has('game')) {
            $game = Game::createFromSavedState((array) $request->getSession()->get('game'));
            $gameState = $game->getGameState();
            $data = [
                'players' => $gameState['players'],
                'currentPlayer' => $gameState['currentPlayer'],
                'pot' => $gameState['pot'],
                'gameStatus' => $gameState['gameStatus'],
                'bank_balance' => $game->getPlayers()['bank']->getBalance(),
                'player_balance' => $game->getPlayers()['human']->getBalance()
            ];
        }
        return new JsonResponse($data);
    }


    // Bygg vidare på din landningssida api/ som visar en webbsida med en sammanställning av alla JSON routes som din webbplats erbjuder. Varje route skall ha en förklaring vad den gör.
    // Skapa en kontroller i Symfony där du kan skapa routes för ett JSON API för denna delen av uppgiften.
    // Skapa en route GET api/deck som returnerar en JSON struktur med hela kortleken sorterad per färg och värde.
    // Skapa en route POST api/deck/shuffle som blandar kortleken och därefter returnerar en JSON struktur med kortleken. Den blandade kortleken sparas i sessionen.
    // Skapa route POST api/deck/draw och POST api/deck/draw/:number som drar 1 eller :number kort från kortleken och visar upp dem i en JSON struktur samt antalet kort som är kvar i kortleken. Kortleken sparas i sessionen så om man anropar dem flera gånger så minskas antalet kort i kortleken.
    // [OPTIONELLT] Skapa en route POST api/deck/deal/:players/:cards som delar ut ett antal :cards från kortleken till ett antal :players och visar upp de korten som respektive spelare har fått i en JSON struktur. Visa även antalet kort som är kvar i kortleken.
}
