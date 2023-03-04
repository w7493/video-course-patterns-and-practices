<?php

declare(strict_types=1);

namespace Controller;

use Controller\Dto\Admin\AddOrEditProduct;
use Controller\Dto\Admin\ChangeVisibilityProduct;
use Service\Product\Product;
use Symfony\Component\HttpFoundation\Request;
use View\Response;

class AdminController
{
    public function __construct(
        private readonly Product $product,
    ) {
    }

    /**
     * Добавляет новый или редактирует существующий продукт
     */
    public function addOrEditProductAction(Request $request): Response
    {
        $requestData = $request->request->all();

        if (!$this->validateAddOrEditProductData($requestData)) {
            return new Response(
                [
                    'message' => 'Отправлен невалидный набор данных',
                ],
                false,
            );
        }

        $operation = $this->product->addOrEdit(
            $this->transformToAddOrEditProductDto($requestData)
        );

        if ($operation['isSuccess'] === false) {
            return new Response(
                [
                    'message' => $operation['message'],
                ],
                false,
            );
        }

        return new Response(
            [
                'product' => [
                    'id' => $operation['productId'],
                ],
            ],
        );
    }

    /**
     * Изменяет видимость продукта
     */
    public function changeVisibilityProductAction(Request $request): Response
    {
        $requestData = $request->request->all();

        if (!$this->validateChangeVisibilityProductData($requestData)) {
            return new Response(
                [
                    'message' => 'Отправлен невалидный набор данных',
                ],
                false,
            );
        }

        $operation = $this->product->changeVisibility(
            $this->transformToChangeVisibilityProductDto($requestData)
        );

        if ($operation['isSuccess'] === false) {
            return new Response(
                [
                    'message' => $operation['message'],
                ],
                false,
            );
        }

        return new Response(
            [
                'affectedRows' => $operation['affectedRows'],
            ],
        );
    }

    /**
     * Упрощённая валидация, дабы не усложнять проект
     * Рекомендуется использовать symfony/validator
     *
     * @param array<string, mixed> $data
     */
    private function validateAddOrEditProductData(array $data): bool
    {
        return array_key_exists('name', $data)
            && is_string($data['name'])
            && array_key_exists('price', $data)
            && is_numeric($data['price']);
    }

    private function transformToAddOrEditProductDto(array $data): AddOrEditProduct
    {
        return new AddOrEditProduct(
            $data['name'],
            (int) $data['price'],
            isset($data['isHidden']) && $data['isHidden'],
            isset($data['id']) ? (int) $data['id'] : null,
        );
    }

    /**
     * Упрощённая валидация, дабы не усложнять проект
     * Рекомендуется использовать symfony/validator
     *
     * @param array<string, mixed> $data
     */
    private function validateChangeVisibilityProductData(array $data): bool
    {
        return array_key_exists('id', $data)
            && is_string($data['id'])
            && array_key_exists('isHidden', $data)
            && ($data['isHidden'] === 'true' || $data['isHidden'] === 'false');
    }

    private function transformToChangeVisibilityProductDto(array $data): ChangeVisibilityProduct
    {
        return new ChangeVisibilityProduct(
            (int) $data['id'],
            $data['isHidden'] === 'true',
        );
    }
}
