<?php

namespace Omnipay\IcepayPayments\Message;

use Omnipay\Common\Message\ResponseInterface;
use Omnipay\IcepayPayments\Exception\PostBackException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The request for getting the transaction status at Icepay.
 */
class TransactionStatusRequest extends AbstractRequest
{
    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $data = parent::getData();

        $data['ContractProfileId'] = $this->getContractProfileId();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data): ResponseInterface
    {
        try {
            $transactionStatusResponse = $this->getTransactionStatusFromPostBack();
        } catch (PostBackException $exception) {
            // Optional parameter to throw the error instead of fallback.
            if (isset($data['throwOnPostBackError'])) {
                throw $exception;
            }
            $transactionStatusResponse = false;
        }

        if ($transactionStatusResponse !== false) {
            return $transactionStatusResponse;
        }

        $this->sendRequest(
            Request::METHOD_POST,
            sprintf(
                '/transaction/%s',
                $this->getTransactionReference()
            ),
            $data
        );

        return new TransactionStatusResponse(
            $this,
            $this->getResponseBody(),
            $this->getResponse()->getStatusCode()
        );
    }

    /**
     * Use the data sent by Icepay in the post back to check the status.
     * This is necessary because Icepay has a delay in their backend if you request the status immediately after the signal.
     *
     * @see http://docs2.icepay.com/payment-process/handling-the-postback/postback-sample/
     *
     * @return TransactionStatusResponse|bool False when the data is is not sent or not correct.
     *
     * @throws PostBackException
     */
    private function getTransactionStatusFromPostBack(): ?TransactionStatusResponse
    {
        $request = Request::createFromGlobals();

        if (stripos($request->getContentType(), 'json') === false) {
            return false;
        }

        try {
            $contentAsArray = json_decode($request->getContent(), true);
        } catch(\LogicException $exception) {
            return false;
        }

        if (is_array($contentAsArray) === false || isset($contentAsArray['StatusCode']) === false) {
            return false;
        }

        $this->validateSecurityHashMatch($request, $contentAsArray);

        $camelCasedKeysContent = array_combine(
            array_map('lcfirst', array_keys($contentAsArray)),
            array_values($contentAsArray)
        );

        return new TransactionStatusResponse(
            $this,
            $camelCasedKeysContent,
            200
        );
    }

    /**
     * Get the security hash from the request and match it against a generated hash from the sent values.
     * Will throw an exception if it does not match.
     * Needs the POSTed Json as a php array.
     *
     * @param Request         $request
     * @param $contentAsArray
     *
     * @throws PostBackException
     */
    private function validateSecurityHashMatch(Request $request, $contentAsArray): void
    {
        $generatedSecurityHash = $this->getSecurityHash(
            Request::METHOD_POST,
            $request->getPathInfo(),
            $contentAsArray
        );

        $sentSecurityHash = $request->headers->get('checksum');

        if ($generatedSecurityHash !== $sentSecurityHash) {
            throw new PostBackException(
                sprintf(
                    'Sent security hash %s did not match generated hash %s',
                    $sentSecurityHash,
                    $generatedSecurityHash
                )
            );
        }
    }
}
