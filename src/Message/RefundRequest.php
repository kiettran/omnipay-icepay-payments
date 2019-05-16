<?php

namespace Omnipay\IcepayPayments\Message;

use Omnipay\Common\Message\ResponseInterface;

/**
 * The request for refunding at Icepay.
 */
class RefundRequest extends AbstractRequest
{
    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $data = parent::getData();

        $data['ContractProfileId'] = $this->getContractProfileId();
        $data['AmountInCents'] = $this->getAmountInteger();
        $data['CurrencyCode'] = $this->getCurrencyCode();
        $data['Reference'] = $this->getReference(); // This isn't the payment reference but needs to be unique among refunds.
        $data['Timestamp'] = $this->getTimestamp()->format(self::TIMESTAMP_FORMAT);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data): ResponseInterface
    {
        $this->sendRequest(
            self::METHOD_POST,
            sprintf(
                '/transaction/%s/refund',
                $this->getTransactionReference()
            ),
            $data
        );

        return new CreateTransactionResponse(
            $this,
            $this->getResponseBody()
        );
    }
}
