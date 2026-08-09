<?php
namespace Opencart\System\Library\Ohbono;

/**
 * Wallet transaction constants.
 *
 * Transaction rows are ledger records and should never be edited after
 * creation.
 */
class WalletTransaction
{
    public const DIRECTION_CREDIT = 'credit';
    public const DIRECTION_DEBIT = 'debit';

    public const TYPE_ADMIN_CREDIT = 'admin_credit';
    public const TYPE_ADMIN_DEBIT = 'admin_debit';
    public const TYPE_ORDER_PAYMENT = 'order_payment';
    public const TYPE_ORDER_REFUND = 'order_refund';
    public const TYPE_CASHBACK = 'cashback';
    public const TYPE_RECHARGE = 'wallet_recharge';
    public const TYPE_SIGNUP_BONUS = 'signup_bonus';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public static function isCreditType(string $type): bool
    {
        return in_array($type, [
            self::TYPE_ADMIN_CREDIT,
            self::TYPE_ORDER_REFUND,
            self::TYPE_CASHBACK,
            self::TYPE_RECHARGE,
            self::TYPE_SIGNUP_BONUS,
            self::TYPE_REFERRAL,
            self::TYPE_PROMOTION
        ], true);
    }

    public static function isDebitType(string $type): bool
    {
        return in_array($type, [
            self::TYPE_ADMIN_DEBIT,
            self::TYPE_ORDER_PAYMENT,
            self::TYPE_ADJUSTMENT
        ], true);
    }
}
