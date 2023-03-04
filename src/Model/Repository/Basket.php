<?php

declare(strict_types=1);

namespace Model\Repository;

use App\Db\DbProvider;
use Model\Entity;

class Basket
{
    public function __construct(
        private readonly DbProvider $db,
    ) {
    }

    /**
     * Добавляет продукт в корзину
     */
    public function addProduct(int $userId, int $productId, int $quantity): int
    {
        return $this->db->insert(
            'insert into basket (user_id, product_id, quantity) values (:user_id, :product_id, :quantity)',
            ['user_id' => $userId, 'product_id' => $productId, 'quantity' => $quantity],
        );
    }

    /**
     * Удаляет продукт из корзины
     */
    public function removeProduct(int $id): int
    {
        return $this->db->execute(
            'delete from basket where id = :id',
            ['id' => $id],
        );
    }
}
