<?php

namespace KJ\Payment\StripeBundle\Client;

use JMS\Payment\CoreBundle\Entity\Payment;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\Exception;
use JMS\Payment\CoreBundle\Plugin\Exception\InvalidDataException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;



class Client
{
    protected $apiKey;
    protected $apiVersion;

    public function __construct($apiKey, $apiVersion = null)
    {
        $this->apiKey = $apiKey;
        $this->apiVersion = $apiVersion;

        \Stripe::setApiKey($this->apiKey);

        if ($this->apiVersion) {
            \Stripe::setApiVersion($this->apiVersion);
        }
    }

    /**
     * Charge a card
     *
     * @param FinancialTransactionInterface $transaction
     * @param bool $capture
     * @return \Stripe_Charge
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\InvalidDataException
     * @throws \Exception|\Stripe_CardError
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException
     */
    public function charge(FinancialTransactionInterface $transaction, $capture = true)
    {
        $data = $transaction->getExtendedData();

        try {

            $response = \Stripe_Charge::create(array(
                'capture' => $capture,
                'amount' => $this->convertAmountToStripeFormat($transaction->getRequestedAmount()), // amount values are in cents
                'currency' => $transaction->getPayment()->getPaymentInstruction()->getCurrency(),
                'description' => $data->get('payment_description'),
                'card' => array(
                    'name' => $data->get('name'),
                    'number' => $data->get('number'),
                    'exp_month' => $data->get('exp_month'),
                    'exp_year' => $data->get('exp_year'),
                    'cvc' => $data->get('cvc'),
                    'address_line1' => $data->get('address_line1'),
                    'address_line2' => $data->get('address_line2'),
                    'address_city' => $data->get('address_city'),
                    'address_state' => $data->get('address_state'),
                    'address_country' => $data->get('address_country'),
                    'address_zip' => $data->get('address_zip'),
                ),
            ));

        } catch (\Stripe_AuthenticationError $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];

            $ex = new FinancialException($e->getMessage());
            $transaction->setResponseCode($err['type']);
            $transaction->setReasonCode('authentication_error');
            $ex->setFinancialTransaction($transaction);

            throw $ex;

        } catch (\Stripe_Error $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];

            if (array_key_exists('code', $err) && !empty($err['code'])) {
                $reasonCode = $err['code'];
            } else {
                $reasonCode = PluginInterface::REASON_CODE_INVALID;
            }

            $ex = new FinancialException($err['message']);
            $transaction->setResponseCode($err['type']);
            $transaction->setReasonCode($reasonCode);
            $ex->setFinancialTransaction($transaction);

            throw $ex;

        } catch (\Exception $e) {
            $ex = new FinancialException($e->getMessage());
            $transaction->setResponseCode($e->getCode());
            $transaction->setReasonCode(PluginInterface::REASON_CODE_INVALID);
            $ex->setFinancialTransaction($transaction);

            throw $ex;
        }

        return $response;
    }

    /**
     * Convert transaction amount to stripe amount format
     *
     * @param float $amount
     * @return integer
     */
    public function convertAmountToStripeFormat($amount)
    {
        return $amount * 100;
    }

    /**
     * Convert transaction amount reported from Stripe API
     *
     * @param $amount
     * @return float
     */
    public function convertAmountFromStripeFormat($amount)
    {
        return $amount / 100;
    }

    /**
     * Capture a charge
     *
     * @param string $chargeId
     * @return \Stripe_Charge
     */
    public function capture($chargeId)
    {
        $charge = \Stripe_Charge::retrieve($chargeId);

        return $charge->capture();
    }

    /**
     * Full or Part refund
     *
     * @param FinancialTransactionInterface $transaction
     * @return mixed
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException
     */
    public function refund(FinancialTransactionInterface $transaction)
    {
        $data = $transaction->getExtendedData();

        try {
            $charge = \Stripe_Charge::retrieve($data->get('charge_id'));

            $response = $charge->refund(array(
                'amount' => $this->convertAmountToStripeFormat($transaction->getRequestedAmount()), // amount values are in cents
                'refund_application_fee' => false
            ));

        } catch (\Stripe_AuthenticationError $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];

            $ex = new FinancialException($e->getMessage());
            $transaction->setResponseCode($err['type']);
            $transaction->setReasonCode('authentication_error');
            $ex->setFinancialTransaction($transaction);

            throw $ex;

        } catch (\Stripe_Error $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];

            if (array_key_exists('code', $err) && !empty($err['code'])) {
                $reasonCode = $err['code'];
            } else {
                $reasonCode = PluginInterface::REASON_CODE_INVALID;
            }

            $ex = new FinancialException($err['message']);
            $transaction->setResponseCode($err['type']);
            $transaction->setReasonCode($reasonCode);
            $ex->setFinancialTransaction($transaction);

            throw $ex;

        } catch (\Exception $e) {
            $ex = new FinancialException($e->getMessage());
            $transaction->setResponseCode($e->getCode());
            $transaction->setReasonCode(PluginInterface::REASON_CODE_INVALID);
            $ex->setFinancialTransaction($transaction);

            throw $ex;
        }

        return $response;
    }
}
