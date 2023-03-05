<?php

declare(strict_types=1);

namespace Model\Repository;

use App\Db\DbProvider;
use Model\Entity;

class Product
{
    /**
     * Возвращает коллекцию всех продуктов
     *
     * @return Entity\Product[]
     */
    public function fetchAll(bool $isShowHidden = false): array
    {
        $query = 'select * from product where is_hidden = :is_hidden';
        $params = ['is_hidden' => (int) $isShowHidden];

        $productList = [];
        foreach ((new DbProvider('data/database.sqlite'))->fetchAll($query, $params) as $item) {
            $productList[] = $this->createProductEntity($item);
        }

        return $productList;
    }

    /**
     * Поиск продуктов по массиву id
     *
     * @param int[] $ids
     * @return Entity\Product[]
     */
    public function search(array $ids = []): array
    {
        if (!count($ids)) {
            return [];
        }

        $query = 'select * from product where id in (' . substr(str_repeat('?, ', count($ids)), 0, -2) . ')';

        $productList = [];
        foreach ((new DbProvider('data/database.sqlite'))->fetchAll($query, $ids) as $item) {
            $productList[] = $this->createProductEntity($item);
        }

        return $productList;
    }

    /**
     * Проверяем существование продукта
     */
    public function exists(int $id): bool
    {
        $query = 'select count(*) as cnt from product where id = :id';

        return (new DbProvider('data/database.sqlite'))->fetchAll($query, [':id' => $id])[0]['cnt'] > 0;
    }

    /**
     * Добавляет новый продукт
     */
    public function add(string $name, int $price, bool $isHidden): int
    {
        return (new DbProvider('data/database.sqlite'))->insert(
            'insert into product (name, price, is_hidden) values (:name, :price, :is_hidden)',
            ['name' => $name, 'price' => $price, 'is_hidden' => $isHidden],
        );
    }

    /**
     * Редактирует существующий продукт
     */
    public function edit(int $id, string $name, int $price, bool $isHidden): int
    {
        return (new DbProvider('data/database.sqlite'))->execute(
            'update product set name = :name, price = :price, is_hidden = :is_hidden where id = :id',
            ['id' => $id, 'name' => $name, 'price' => $price, 'is_hidden' => (int) $isHidden],
        );
    }

    /**
     * @param array<string, mixed> $item
     */
    private function createProductEntity(array $item): Entity\Product
    {
        return new Entity\Product($item['id'], $item['name'], $item['price'], (bool) $item['is_hidden']);
    }
}
