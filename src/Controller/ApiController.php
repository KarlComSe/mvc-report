<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class ApiController extends AbstractController
{
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
        $number = random_int(0, 100);
        /*
        * Below is not actual quotes, they are provided by chatGPT and due to copyright issues, it needs to paraphrase.
        * The quotes are provided upon asking for quotes by Assad, which is a fictional character in a book series called Department Q.
        * Assad often makes strange quotes, based on his origin, which supposedly is Syrian. The book is written by Jussi Adler-Olsen.
        */
        $assadsQuotes = [
            "In the desert, even a small snake casts a long shadow." => "Assad's way of saying that even seemingly insignificant details can have significant consequences.",
            "Sometimes the path to truth is like a winding river; it may take unexpected turns, but it always finds its way." => "Assad's metaphor for the unpredictable nature of solving mysteries and uncovering the truth.",
            "A man without curiosity is like a ship without a compass; lost in a sea of ignorance." => "Assad's belief in the importance of curiosity and knowledge.",
            "The eagle may soar high, but the fox knows every hole in the ground." => "Assad's acknowledgment of the value of both intelligence and cunning in solving cases.",
            "In life, as in the forest, it is not always the tallest trees that offer the most shade." => "Assad's reflection on the importance of humility and empathy.",
            "Just as the dromedary carries its burdens across the vast desert, so too must we carry the weight of our responsibilities." => "Assad's comparison of life's challenges to the endurance of dromedaries in harsh environments, emphasizing the importance of resilience.",
            "A man who trusts in the loyalty of a dromedary may find himself stranded in the desert with an empty water skin." => "Assad's warning about misplaced trust, using the loyalty of dromedaries as a metaphor for human relationships.",
            "In the garden of life, even the most delicate flowers must weather storms to bloom." => "Assad's reflection on resilience and growth in the face of adversity.",
            "Like the stars in the night sky, each person carries their own light, guiding them through the darkness." => "Assad's metaphor for individual strength and purpose.",
            "The river of time flows ceaselessly, carrying with it both the debris of the past and the promise of the future." => "Assad's contemplation on the passage of time and the inevitability of change.",
            "A candle may flicker in the wind, but its flame remains steadfast against the darkness." => "Assad's analogy for unwavering determination and resolve.",
            "Just as the wolf prowls the forest in search of prey, so too must we navigate the shadows of the unknown." => "Assad's comparison of investigative work to the hunt for truth and justice.",
        ];

        $randomQuote = array_rand($assadsQuotes);
        $data = [
            'quote' => $randomQuote,
            'explanation' => $assadsQuotes[$randomQuote],
        ];
        return new JsonResponse($data);
    }
}