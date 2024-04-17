<?php

namespace App\Controller;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;

use App\Model\Card;
use App\Model\CardHand;
use App\Model\GraphicCard;
use App\Model\FrenchSuitedDeck;
use App\Observer\SessionSavingObserver;
use App\Model\Game;

use Random\Randomizer;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(Request $request)
    {
        return $this->render('game/index.html.twig');
    }

    #[Route('/game/doc', name: 'app_game_doc')]
    public function doc()
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route('/game/reset', name: 'app_reset_game')]
    public function reset(Request $request)
    {
        $session = $request->getSession();
        $session->remove('game');
        return $this->redirectToRoute('app_play_game');
    }

    #[Route('/game/play/post', name: 'app_post_game')]
    public function processMove(Request $request)
    {   
        $session = $request->getSession();
        if ($session->has('game') === false){
            $flashes = $session->getFlashBag();
            $flashes->add(
                'notice',
                'Hmm. Not good. Dont cheat!!!'
            );
            return $this->redirectToRoute('app_game');
        }

        $gameState = $session->get('game');
        $game = Game::createFromSavedState($gameState);

        if (!$game->getGameStatus()){
            $flashes = $session->getFlashBag();
            $flashes->add(
                'notice',
                'Game has ended. You need to reset the session!'
            );
            return $this->redirectToRoute('app_play_game');
        }

        $game->attach(new SessionSavingObserver($request, 'game'));

        if ($game->getPlayerBet() === NULL){
            if ($request->request->get('bet') === NULL){
                $flashes = $session->getFlashBag();
                $flashes->add(
                    'notice',
                    'You must place a bet before you can play!'
                );
                return $this->redirectToRoute('app_play_game');
            }
            $game->setPlayerBet($request->request->get('bet'));
            $game->dealCard();
        }

        if ($request->request->get('action') === 'hit' && $game->getCurrentPlayer() === 'human'){
            $game->dealCard();
        }

        if ($request->request->get('action') === 'stand' && $game->getCurrentPlayer() === 'human'){
            $game->nextPlayer();
        }

        if ($game->getCurrentPlayer() === 'bank' && $request->request->get('action') === 'hit'){
            $game->dealCard();
            $bankScores = $game->hands['bank']->getScore();
            if (count($bankScores) > 0 && min($bankScores) >= 17){
                $game->endGame();
            } 
        }

        if ($game->isBusted()){
            $flashes = $session->getFlashBag();
            $game->endGame();
            $flashes->add(
                'notice',
                "You are busted and lose! {$game->getCurrentPlayer()}"
            );
            return $this->redirectToRoute('app_play_game');
        }

        return $this->redirectToRoute('app_play_game');
    }

    #[Route('/game/play', name: 'app_play_game', methods: ['GET'], defaults: ['game_needed' => true ])]
    public function play(Request $request)
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
