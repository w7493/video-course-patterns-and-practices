<?php

declare(strict_types=1);

namespace Controller;

use Service\Product\Product;
use Symfony\Component\HttpFoundation\Request;
use View\Response;

class ProductController
{
    // Если в запросе есть эта опция, то показываем только скрытые продукты
    private const SHOW_HIDDEN = 'hidden';
    // Ключ параметра, по которому происходит фильтрация списка товаров
    private const LIST_SHOW_KEY = 'show';

    /**
     * Список всех продуктов
     */
    public function listAction(Request $request): Response
    {
        $isShowHidden = $request->query->has(self::LIST_SHOW_KEY)
            && $request->query->get(self::LIST_SHOW_KEY) === self::SHOW_HIDDEN;

        $response = [];
        foreach ((new Product())->getAll($isShowHidden) as $product) {
            $response[] = $product->toArray();
        }

        return new Response($response);
    }

    /**
     * Информация о продукте
     */
    public function infoAction(int $id): Response
    {
        $product = (new Product())->getInfo($id);

        if ($product === null) {
            return new Response();
        }

        return new Response($product->toArray());
    }
}
