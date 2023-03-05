<?php

declare(strict_types=1);

namespace Controller;

use Service\Product\Product;
use Symfony\Component\HttpFoundation\Request;
use View\Response;

class ProductController
{
    public function __construct(
        private readonly Product $product,
    ) {
    }

    /**
     * Список всех продуктов
     */
    public function listAction(Request $request): Response
    {
        $isShowHidden = $request->query->has('show')
            && $request->query->get('show') === 'hidden';

        $response = [];
        foreach ($this->product->getAll($isShowHidden) as $product) {
            $response[] = $product->toArray();
        }

        return new Response($response);
    }

    /**
     * Информация о продукте
     */
    public function infoAction(int $id): Response
    {
        $product = $this->product->getInfo($id);

        if ($product === null) {
            return new Response();
        }

        return new Response($product->toArray());
    }
}
