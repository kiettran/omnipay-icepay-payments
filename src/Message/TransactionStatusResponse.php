<?php

namespace Omnipay\IcepayPayments\Message;

/**
 * The response after getting the transaction status at Icepay.
 */
class TransactionStatusResponse extends AbstractResponse
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful(): bool
    {
        return (isset($this->data['status']) && in_array($this->data['status'], [
            self::RESPONSE_STATUS_COMPLETED,
            self::RESPONSE_STATUS_SETTLED,
        ])) && (isset($this->data['statusCode']) && $this->data['statusCode'] === 200);
    }

    /**
     * {@inheritdoc}
     */
    public function isCancelled(): bool
    {
        return parent::isCancelled() || $this->data['statusCode'] === self::RESPONSE_STATUS_CANCELLED;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionReference(): ?string
    {
        return $this->request->getTransactionReference();
    }
}
