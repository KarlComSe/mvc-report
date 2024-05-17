<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Model\FrenchSuitedDeck;
class CardRouteListener
{
    public function onKernelRequest(RequestEvent $event): void
    {

        $request = $event->getRequest();
        $session = $request->getSession();

        $attributes = $request->attributes->get('_route_params');

        if ($attributes && key_exists('deck_needed', $attributes)) {
            if (!$session->has('deck_2')) {
                $frenchDeck = FrenchSuitedDeck::create();
                $session->set('deck_2', $frenchDeck->getDeck());
            }
        }
    }
}
