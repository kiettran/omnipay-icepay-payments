<?php

namespace Omnipay\IcepayPayments\Message;

/**
 * The response after creating a transaction at Icepay.
 */
class CreateTransactionResponse extends AbstractResponse
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful(): bool
    {
        return parent::isSuccessful()
            && (isset($this->data['transactionStatusCode']) && $this->data['transactionStatusCode'] === self::RESPONSE_STATUS_STARTED);
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirect(): bool
    {
        return $this->data['acquirerRequestUri'] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl(): string
    {
        return $this->data['acquirerRequestUri'] ?? '';
    }
}
