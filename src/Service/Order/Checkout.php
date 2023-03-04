<?php

namespace Service\Order;

use Controller\Dto\Order\CheckoutProduct;
use Model;
use Service\Billing\Creator as BillingCreator;
use Service\Billing\Exception\BillingException;
use Service\Communication\Creator as CommunicationCreator;
use Service\Communication\Exception\CommunicationException;
use Service\Discount\Creator as DiscountCreator;
use Service\Discount\Exception\UnavailableDiscountException;
use Model\Entity;
use App\Db\DbProvider;

class Checkout
{
    public function __construct(
        private readonly DiscountCreator $discountCreator,
        private readonly CommunicationCreator $communicationCreator,
        private readonly BillingCreator $billingCreator,
        private readonly DbProvider $db,
    ) {
    }

    /**
     * Оплата корзины и информировании о размещённом заказе
     */
    public function checkoutProcess(int $userId, CheckoutProduct $checkoutProduct): bool
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

        $totalPrice = 0;
        foreach ($userBasket as $product) {
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
