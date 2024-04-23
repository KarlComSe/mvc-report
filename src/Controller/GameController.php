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

    #[Route('/game/reset', name: 'app_reset_game')]
    public function reset(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $session->remove('game');
        return $this->redirectToRoute('app_play_game');
    }

    #[Route('/game/play/post', name: 'app_post_game')]
    public function processMove(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        if ($session->has('game') === false) {
            $this->addFlash(
                'notice',
                'Hmm. Not good. Dont cheat!!!'
            );
            return $this->redirectToRoute('app_game');
        }

        $gameState = $session->get('game');
        $game = Game::createFromSavedState($gameState);

        if (!$game->getGameStatus()) {
            $this->addFlash(
                'notice',
                'Game has ended. You need to reset the session!'
            );
            return $this->redirectToRoute('app_play_game');
        }

        $game->attach(new SessionSavingObserver($request, 'game'));

        if (!$game->isBetPlaced()) {

            if ($request->request->get('bet') === null) {
                $this->addFlash(
                    'notice',
                    'You must place a bet before you can play!'
                );
                return $this->redirectToRoute('app_play_game');
            }
            $game->setPlayerBet($request->request->get('bet'));
            $game->dealCard();
        }

        if ($request->request->get('action') === 'hit' && $game->getCurrentPlayer() === 'human') {
            $game->dealCard();
        }

        if ($request->request->get('action') === 'stand' && $game->getCurrentPlayer() === 'human') {
            $game->nextPlayer();
        }

        if ($game->getCurrentPlayer() === 'bank' && $request->request->get('action') === 'hit') {
            $game->dealCard();
            $bankScores = $game->hands['bank']->getScore();
            if (count($bankScores) > 0 && min($bankScores) >= 17) {
                $game->endGame();
            }
        }

        if ($game->isBusted()) {
            $game->endGame();
            $this->addFlash(
                'notice',
                "You are busted and lose! {$game->getCurrentPlayer()}"
            );
        }

        return $this->redirectToRoute('app_play_game');
    }

    #[Route('/game/play', name: 'app_play_game', methods: ['GET'], defaults: ['game_needed' => true ])]
    public function play(Request $request): Response
    {
        $session = $request->getSession();
        $gameState = $session->get('game');
        $game = Game::createFromSavedState($gameState);
        $game->attach(new SessionSavingObserver($request, 'game'));

        return $this->render('game/game.html.twig', [
            'game' => $game
        ]);
    }
}
