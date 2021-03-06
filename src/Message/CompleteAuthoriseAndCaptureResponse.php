<?php

namespace Omnipay\IcepayPayments\Message;

/**
 * The response after complete authorise and capture request.
 * For this response, we explicitly check what the status is of the payment transaction at Icepay.
 */
class CompleteAuthoriseAndCaptureResponse extends AbstractResponse
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful(): bool
    {
        return isset($this->data['status']) && in_array($this->data['status'], [
            self::RESPONSE_STATUS_COMPLETED,
            self::RESPONSE_STATUS_SETTLED,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * In case there is no status 'cancelled' available in the response (yet), check if there is statusCode in the
     * queryString. Icepay calls a postback to the completeUrl with data of the payment transaction as queryString.
     */
    public function isCancelled(): bool
    {
        return isset($this->data['status']) && $this->data['status'] === self::RESPONSE_STATUS_CANCELLED
            || $this->request->getHttpRequest()->get('statusCode') === self::RESPONSE_STATUS_CANCELLED;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionReference(): ?string
    {
        return $this->request->getTransactionReference();
    }
}
