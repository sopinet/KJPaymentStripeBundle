<?php

namespace KJ\Payment\StripeBundle\Client;

use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;

use JMS\Payment\CoreBundle\Plugin\Exception\InvalidDataException;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use JMS\Payment\CoreBundle\Plugin\Exception as JMSPluginException;

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
	 * @param \JMS\Payment\CoreBundle\Model\FinancialTransactionInterface $transaction
	 * @param boolean $capture  Whether or not to immediately capture the charge
	 * @return \Stripe_Charge
	 * @throws \Stripe_Error
	 */
	public function charge(FinancialTransactionInterface $transaction, $capture = true)
	{
		$data = $transaction->getExtendedData();
		
		try {
			
			$response = \Stripe_Charge::create(array(
				'capture' => $capture,
				'amount' => $transaction->getRequestedAmount()*100, // amount values are in cents
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
			
		} catch(\Stripe_CardError $e) {
			throw $e;
			
		} catch (Stripe_InvalidRequestError $e) {
			throw new InvalidDataException('The API request was not successful (Reason: Invalid parameters)');
			
		} catch (\Stripe_Error $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];	

			throw new CommunicationException('The API request was not successful (Reason: '.$err['message'].')');
			
		} catch (\Exception $e) {
			throw new JMSPluginException('The API request was not successful ('.$e->getCode().': '.$e->getMessage().')');
		}
		
		return $response;
	}
	
	/**
	 * Capture a charge
	 * 
	 * @param string $chargeId
	 * @return \Stripe_Charge
	 * @throws \Stripe_Error
	 */
	public function capture($chargeId) 
	{
		$charge = \Stripe_Charge::retrieve($chargeId);
		
		return $charge->capture();
	}

	

}
