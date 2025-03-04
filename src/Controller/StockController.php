<?php

namespace App\Controller;

use App\Provider\AlphaVantageProvider;
use App\Provider\StockProviderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class StockController extends AbstractController
{
    public function __construct(private readonly StockProviderFactory $factory)
    {

    }

    #[Route('/stock', name: 'app_stock')]
    public function index(Request $request, #[MapQueryParameter] string $q, #[MapQueryParameter] string $provider = AlphaVantageProvider::IDENTIFIER): JsonResponse
    {
        $stockProvider = $this->factory->getProvider($provider);

        $json = $stockProvider->get($request->query->get('q'));
        $model = $stockProvider->process(json: $json, user: $this->getUser());

        return $this->json(data: $model);
    }
}
