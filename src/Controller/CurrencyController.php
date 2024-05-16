<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Form\CurrencyType;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CurrencyController extends AbstractController
{
    #[Route('/currency', name: 'app_currency')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $currencies = $entityManager->getRepository(Currency::class)->findAll();

        return $this->render('currency/index.html.twig', [
            'controller_name' => 'CurrencyController',
            'currencies' => $currencies,
        ]);
    }

    #[Route('/currency/create', name: 'app_currency_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $currency = new Currency();
        $form = $this->createForm(CurrencyType::class, $currency);
        $form->add('save', SubmitType::class, [
            'label' => 'Create Currency',
            'attr' => ['class' =>
                'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-2 border border-blue-700 rounded']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currency = $form->getData();

            $entityManager->persist($currency);
            $entityManager->flush();

            return $this->redirectToRoute('app_currency');
        }

        return $this->render('currency/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/api/currency', name: 'api_currency')]
    public function api(CurrencyRepository $currencyRepository): Response
    {
        $currencies = $currencyRepository
            ->findAll();

        return $this->json($currencies, 200);
    }

    #[Route('/api/currency/{id}', name: 'api_currency_show')]
    public function apiShow(Currency $currency): Response
    {
        return $this->json($currency, 200);
    }

    #[Route('/currency/edit/{id}', name: 'app_currency_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, Currency $currency): Response
    {

        $form = $this->createForm(CurrencyType::class, $currency);
        $form->add('save', SubmitType::class, [
            'label' => 'Save edit',
            'attr' => ['class' =>
                'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-2 border border-blue-700 rounded']
        ]);
        $form->add('cancel', SubmitType::class, [
            'label' => 'Cancel edit',
            'attr' => ['class' =>
                'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 my-2 border border-blue-700 rounded']
        ]);


        $form->handleRequest($request);

        /** @var ClickableInterface $button  */
        $button = $form->get("cancel");
        if ($button->isClicked()) {
            return $this->redirectToRoute('app_currency');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $currency = $form->getData();

            $entityManager->flush();

            return $this->redirectToRoute('app_currency');
        }

        return $this->render('currency/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/currency/delete/{id}', name: 'app_currency_delete')]
    public function delete(EntityManagerInterface $entityManager, Currency $currency): Response
    {
        $entityManager->remove($currency);
        $entityManager->flush();

        return $this->redirectToRoute('app_currency');
    }

    #[Route('/currency/show/{id}', name: 'app_currency_show')]
    public function show(Currency $currency): Response
    {
        return $this->render('currency/show.html.twig', [
            'currency' => $currency,
        ]);
    }
}
