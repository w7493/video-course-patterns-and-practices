<?php

declare(strict_types=1);

namespace Service\Product;

use App\Db\Exception\UniqueDbException;
use Controller\Dto\Admin\AddOrEditProduct;
use Controller\Dto\Admin\ChangeVisibilityProduct;
use Model;

class Product
{
    public function __construct(
        private readonly Model\Repository\Product $product,
    ) {
    }

    /**
     * Возвращает коллекцию всех продуктов
     *
     * @return Model\Entity\Product[]
     */
    public function getAll(bool $isShowHidden = false): array
    {
        return $this->product->fetchAll($isShowHidden);
    }

    /**
     * Возвращает сущность конкретного продукта
     */
    public function getInfo(int $id): ?Model\Entity\Product
    {
        $product = $this->product->search([$id]);
        return count($product) ? $product[0] : null;
    }

    /**
     * Добавляет или редактирует существующий продукт
     *
     * @return array<string, mixed>
     */
    public function addOrEdit(AddOrEditProduct $dto): array
    {
        if ($dto->getId() === null) {
            try {
                $productId = $this->product->add($dto->getName(), $dto->getPrice(), $dto->isHidden());

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

        $affectedRows = $this->product->edit($dto->getId(), $dto->getName(), $dto->getPrice(), $dto->isHidden());

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
        $products = $this->product->search([$dto->getId()]);

        if (!count($products)) {
            return [
                'isSuccess' => false,
                'message' => 'Продукт не существует',
            ];
        }

        $product = $products[0];

        $affectedRows = $this->product->edit($product->getId(), $product->getName(), $product->getPrice(), $dto->isHidden());

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
