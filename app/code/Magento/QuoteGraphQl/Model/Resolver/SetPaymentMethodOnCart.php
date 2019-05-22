<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Mutation resolver for setting payment method for shopping cart
 */
class SetPaymentMethodOnCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart
     */
    private $setPaymentMethodOnCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $setPaymentMethodOnCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $setPaymentMethodOnCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->setPaymentMethodOnCart = $setPaymentMethodOnCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (!isset($args['input']['payment_method']['code']) || empty($args['input']['payment_method']['code'])) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }
        $paymentData = $args['input']['payment_method'];

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());
        $cart = $this->setPaymentMethodOnCart->execute($paymentData, $cart);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
