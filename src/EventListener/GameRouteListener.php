<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Model\Game;
use App\Model\HumanPlayer;
use App\Model\BankPlayer;
use App\Model\FrenchSuitedDeck;
use App\Model\DeckShuffler;
use Random\Randomizer;

class GameRouteListener
{
    public function onKernelRequest(RequestEvent $event): void
    {

        $request = $event->getRequest();
        $session = $request->getSession();

        $attributes = $request->attributes->get('_route_params');

        if ($attributes && key_exists('game_needed', $attributes)) {
            if (!$session->has('game')) {
                $frenchDeck = FrenchSuitedDeck::create();
                $deckShuffler = new DeckShuffler(new Randomizer());
                $deckShuffler->shuffle($frenchDeck);
                $game = new Game(
                    [
                        new HumanPlayer(),
                        new BankPlayer(),
                    ],
                    $frenchDeck
                );

                $session->set('game', $game->getGameState());
            }
        }
    }
}
