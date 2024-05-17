<?php

namespace App\Controller;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Observer\SessionSavingObserver;
use App\Model\Game;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        return $this->render('game/index.html.twig');
    }

    #[Route('/game/doc', name: 'app_game_doc')]
    public function doc(): Response
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route('/game/play/post', name: 'app_post_game')]
    public function processMove(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $gameState = $session->get('game');
        $game = Game::createFromSavedState((array) $gameState);

        $game->attach(new SessionSavingObserver($request, 'game'));

        try {
            $game->playRound($request->request->all());
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_play_game');
    }

    #[Route('/game/play', name: 'app_play_game', methods: ['GET'], defaults: ['game_needed' => true ])]
    public function play(Request $request): Response
    {
        $session = $request->getSession();
        $gameState = $session->get('game');
        $game = Game::createFromSavedState((array) $gameState);

        return $this->render('game/game.html.twig', [
            'game' => $game
        ]);
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
}
