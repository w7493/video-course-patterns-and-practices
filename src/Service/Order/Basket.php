<?php

declare(strict_types=1);

namespace Service\Order;

use App\Db\DbProvider;
use App\Db\Exception\UniqueDbException;
use Controller\Dto\Order\AddProduct;
use Controller\Dto\Order\RemoveProduct;
use Model;
use Model\Entity;

class Basket
{
    public function __construct(
        private readonly Model\Repository\Basket $basket,
        private readonly Model\Repository\Product $product,
        private readonly DbProvider $db,
    ) {
    }

    /**
     * Корзина пользователя
     *
     * @return Model\Entity\Basket[]
     */
    public function getUserBasket(int $userId): array
    {
        $query = <<<EOT
            select b.id, p.name, p.price
            from basket b
            inner join product p on b.product_id = p.id
            where b.user_id = :user_id and p.is_hidden = 0
        EOT;

        $userBasket = [];
        foreach ($this->db->fetchAll($query, [':user_id' => $userId]) as $item) {
            $userBasket[] = new Entity\Basket($item['id'], $item['name'], $item['price']);
        }

        return $userBasket;
    }

    /**
     * Проверяем наличие товара в корзине пользователя
     */
    public function isProductInBasket(int $userId, int $productId): bool
    {
        $query = <<<EOT
            select b.id, p.name, p.price
            from basket b
            inner join product p on b.product_id = p.id
            where b.user_id = :user_id and p.is_hidden = 0
        EOT;

        $userBasket = [];
        foreach ($this->db->fetchAll($query, [':user_id' => $userId]) as $item) {
            $userBasket[] = new Entity\Basket($item['id'], $item['name'], $item['price']);
        }

        foreach ($userBasket as $item) {
            if ($productId === $item->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Добавляем товар в заказ
     *
     * @return array<string, mixed>
     */
    public function addProduct(AddProduct $dto): array
    {
        $isProductExists = $this->product->exists(
            $dto->getProductId(),
        );

        if (!$isProductExists) {
            return [
                'isSuccess' => false,
                'message' => 'Продукт не существует',
            ];
        }

        try {
            $orderId = $this->basket->addProduct(
                $dto->getUserId(),
                $dto->getProductId(),
                $dto->getQuantity(),
            );
        } catch (UniqueDbException) {
            return [
                'isSuccess' => false,
                'message' => 'Выбранный продукт уже добавлен в корзину',
            ];
        }

        return [
            'isSuccess' => true,
            'orderId' => $orderId,
        ];
    }

    /**
     * Удаляет товар из заказа
     */
    public function removeProduct(RemoveProduct $dto): int
    {
        return $this->basket->removeProduct(
            $dto->getId(),
        );
    }
}
