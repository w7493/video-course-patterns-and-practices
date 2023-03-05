<?php

declare(strict_types=1);

namespace Service\Order;

use App\Db\Exception\UniqueDbException;
use Controller\Dto\Order\AddProduct;
use Controller\Dto\Order\CheckoutProduct;
use Controller\Dto\Order\RemoveProduct;
use Model;
use Service\Billing\Creator as BillingCreator;
use Service\Billing\Exception\BillingException;
use Service\Communication\Creator as CommunicationCreator;
use Service\Communication\Exception\CommunicationException;
use Service\Discount\Creator as DiscountCreator;
use Service\Discount\Exception\UnavailableDiscountException;

class Order
{
    public function __construct(
        private readonly Model\Repository\Basket $basket,
        private readonly Model\Repository\Product $product,
        private readonly DiscountCreator $discountCreator,
        private readonly CommunicationCreator $communicationCreator,
        private readonly BillingCreator $billingCreator,
    ) {
    }

    /**
     * Корзина пользователя
     *
     * @return Model\Entity\Basket[]
     */
    public function getUserBasket(int $userId): array
    {
        return $this->basket->getUserBasket($userId);
    }

    /**
     * Проверяем наличие товара в корзине пользователя
     */
    public function isProductInBasket(int $userId, int $productId): bool
    {
        foreach ($this->basket->getUserBasket($userId) as $item) {
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

    /**
     * Оплата корзины и информировании о размещённом заказе
     */
    public function checkoutProcess(int $userId, CheckoutProduct $checkoutProduct): bool
    {
        $totalPrice = 0;
        foreach ($this->basket->getUserBasket($userId) as $product) {
            $totalPrice += $product->getPrice();
        }

        try {
            $discount = $this->discountCreator->getDiscount($userId, $checkoutProduct->getPromoCode());
        } catch (UnavailableDiscountException) {
            // логириуем ошибку
            // считаем что пользователю недоступна скидка
            $discount = 0;
        }

        $totalPrice = $totalPrice - $totalPrice / 100 * $discount;

        try {
            $this->billingCreator->pay($checkoutProduct->getBillingType(), $totalPrice);
        } catch (BillingException) {
            // логируем ошибку
            // без оплаты нельзя отправить заказ, поэтому отменяем его
            return false;
        }

        try {
            $this->communicationCreator
                ->sendMessage(CommunicationCreator::TYPE_SMS)
                ->prepare($userId, 'checkout_template');
        } catch (CommunicationException) {
            // логируем ошибку
            // создаём задание на повторную отправку уведомления
        }

        return true;
    }
}
