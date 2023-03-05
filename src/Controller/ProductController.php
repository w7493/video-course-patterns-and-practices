<?php

declare(strict_types=1);

namespace Controller;

use App;
use Model;
use Service\Product\Product;
use Symfony\Component\HttpFoundation\Request;
use View\Response;

class ProductController
{
    // Если в запросе есть эта опция, то показываем только скрытые продукты
    private const SHOW_HIDDEN = 'hidden';
    // Ключ параметра, по которому происходит фильтрация списка товаров
    private const LIST_SHOW_KEY = 'show';

    private Product $product;

    public function __construct()
    {
        $db = new App\Db\DbProvider('data/database.sqlite');
        $productRepository = new Model\Repository\Product($db);
        $this->product = new Product($productRepository);
    }

    /**
     * Список всех продуктов
     */
    public function listAction(Request $request): Response
    {
        $isShowHidden = $request->query->has(self::LIST_SHOW_KEY)
            && $request->query->get(self::LIST_SHOW_KEY) === self::SHOW_HIDDEN;

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
