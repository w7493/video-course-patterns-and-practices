<?php

declare(strict_types=1);

namespace Service\Product;

use App\Db\Exception\UniqueDbException;
use Controller\Dto\Admin\AddProduct;
use Controller\Dto\Admin\ChangeVisibilityProduct;
use Controller\Dto\Admin\EditProduct;
use Model;

class Product
{
    /**
     * Возвращает коллекцию всех продуктов
     *
     * @return Model\Entity\Product[]
     */
    public function getAll(bool $isShowHidden = false): array
    {
        return (new Model\Repository\Product())->fetchAll($isShowHidden);
    }

    /**
     * Возвращает сущность конкретного продукта
     */
    public function getInfo(int $id): ?Model\Entity\Product
    {
        $product = (new Model\Repository\Product())->search([$id]);
        return count($product) ? $product[0] : null;
    }

    /**
     * Добавляет продукт
     *
     * @return array<string, mixed>
     */
    public function add(AddProduct $dto): array
    {
        try {
            $productId = (new Model\Repository\Product())->add($dto->getName(), $dto->getPrice(), $dto->isHidden());

            return [
                'isSuccess' => true,
                'productId' => $productId,
            ];
        } catch (UniqueDbException) {
            return [
                'isSuccess' => false,
                'message' => 'Выбранный продукт уже добавлен в корзину',
            ];
        }
    }

    /**
     * Редактирует существующий продукт
     *
     * @return array<string, mixed>
     */
    public function edit(EditProduct $dto): array
    {
        $affectedRows = (new Model\Repository\Product())->edit($dto->getId(), $dto->getName(), $dto->getPrice(), $dto->isHidden());

        if ($affectedRows === 0) {
            return [
                'isSuccess' => false,
                'message' => 'Не удалось обновить данные',
            ];
        }

        return [
            'isSuccess' => true,
            'productId' => $dto->getId(),
        ];
    }

    /**
     * Изменяет видимость продукта
     *
     * @return array<string, mixed>
     */
    public function changeVisibility(ChangeVisibilityProduct $dto): array
    {
        $products = (new Model\Repository\Product())->search([$dto->getId()]);

        if (!count($products)) {
            return [
                'isSuccess' => false,
                'message' => 'Продукт не существует',
            ];
        }

        $product = $products[0];

        $affectedRows = (new Model\Repository\Product())->edit($product->getId(), $product->getName(), $product->getPrice(), $dto->isHidden());

        if ($affectedRows === 0) {
            return [
                'isSuccess' => false,
                'message' => 'Не удалось обновить данные',
            ];
        }

        return [
            'isSuccess' => true,
            'affectedRows' => $affectedRows,
        ];
    }
}
