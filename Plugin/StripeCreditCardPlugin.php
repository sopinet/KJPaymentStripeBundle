<?php

namespace KJ\Payment\StripeBundle\Plugin;

use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Plugin\ErrorBuilder;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use KJ\Payment\StripeBundle\Client\Client;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator;


/**
 * Stripe payment plugin
 *
 * @author kjchew <kjchew@gmail.com>
 */
class StripeCreditCardPlugin extends AbstractPlugin
{
	/**
	 * @var \Symfony\Component\Validator\Validator 
	 */
	protected $validator;

	/**
     * @var \KJ\Payment\StripeBundle\Client\Client
     */
    protected $client;
	
	
	/**
	 * Constructor
	 * 
	 * @param \Symfony\Component\Validator\Validator $validator
	 * @param \KJ\Payment\StripeBundle\Client\Client $client
	 */
    public function __construct(Validator $validator, Client $client)
    {
		$this->validator = $validator;
        $this->client = $client;
    }	
	
    /**
     * @param string $paymentSystemName
     *
     * @return boolean
     */
    public function processes($paymentSystemName)
    {
        return 'stripe_credit_card' === $paymentSystemName;
    }
	
    /**
     * This method checks whether all required parameters exist in the given
     * PaymentInstruction, and whether they are syntactically correct.
     *
     * This method is meant to perform a fast parameter validation; no connection
     * to any payment back-end system should be made at this stage.
     *
     * In case, this method is not implemented. The PaymentInstruction will
     * be considered to be valid.
     *
     * @param PaymentInstructionInterface $instruction
     *
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException if the the PaymentInstruction is not valid
     */
	public function checkPaymentInstruction(PaymentInstructionInterface $instruction)
    {
		// define form validators
		$constraints = new Assert\Collection(array(
			'name' => array(
				new Assert\NotBlank(array('message' => 'Required')),
			),
			'number' => array(
				new Assert\NotBlank(array('message' => 'Required')),
				new Assert\Length(array('min' => 12, 'max' => 19, 'minMessage' => 'Invalid card number 1', 'maxMessage' => 'Invalid card number 2')),
				new Assert\Luhn(array('message' => 'Invalid card number')),
			),
			'exp_month' => array(
				new Assert\NotBlank(array('message' => 'Required')),
				new Assert\Range(array('min' => 1, 'max' => 12, 'minMessage' => 'Invalid code value', 'maxMessage' => 'Invalid code value')),
			),
			'exp_year' => array(
				new Assert\NotBlank(array('message' => 'Required')),
				new Assert\Range(array('min' => date('Y'), 'max' => date('Y', strtotime('+20 years')), 'minMessage' => 'Invalid date', 'maxMessage' => 'Invalid date')),
			),
			'cvc' => array(
				new Assert\NotBlank(array('message' => 'Required')),
				new Assert\Length(array('min' => 3, 'max' => 4, 'minMessage' => 'Invalid code value', 'maxMessage' => 'Invalid code value')),
			),
			'address_line1' => array(
				new Assert\NotBlank(array('message' => 'Required')),
			),
			'address_line2' => array(),
			'address_city' => array(
                new Assert\NotBlank(array('message' => 'Required')),
            ),
			'address_state' => array(
                new Assert\NotBlank(array('message' => 'Required')),
            ),
			'address_country' => array(
				new Assert\NotBlank(array('message' => 'Required')),
			),
			'address_zip' => array(
				new Assert\NotBlank(array('message' => 'Required')),
			),
		));
		
		// extract form values from extended data
		$dateToValidate = array();
		foreach ($constraints->fields as $name => $constraint) {
			$dateToValidate[$name] = $instruction->getExtendedData()->get($name);
		}
		
		// validate input data
		$errors = $this->validator->validateValue($dateToValidate, $constraints);
		
		// transform validator errors into payment exceptions
		$errorBuilder = new ErrorBuilder();
		foreach ($errors as $error) {
			// KLUDGE: remove [] around field name
			$field = substr($error->getPropertyPath(), 1, -1);
			
			$errorBuilder->addDataError('data_stripe_credit_card.'.$field, $error->getMessage());
		}

        if ($errorBuilder->hasErrors()) {
            throw $errorBuilder->getException();
        }
    }
	
    /**
     * This method executes an approve transaction.
     *
     * By an approval, funds are reserved but no actual money is transferred. A
     * subsequent deposit transaction must be performed to actually transfer the
     * money.
     *
     * A typical use case, would be Credit Card payments where funds are first
     * authorized.
     *
     * @param \JMS\Payment\CoreBundle\Model\FinancialTransactionInterface $transaction
     * @param boolean $retry Whether this is a retry transaction
     * @return void
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException      if there is a card error
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\InvalidDataException    if the request has invalid parameters
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException  if there is an API communiation error
     */	
    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $this->chargeCard($transaction, false);
    }

    /**
     * This method executes a deposit transaction without prior approval
     * (aka "sale", or "authorization with capture" transaction).
     *
     * A typical use case for this method is an electronic check payments
     * where authorization is not supported. It can also be used to deposit
     * money in only one transaction, and thus saving processing fees for
     * another transaction.
     *
     * @param \JMS\Payment\CoreBundle\Model\FinancialTransactionInterface $transaction
     * @param boolean $retry Whether this is a retry transaction
     * @return void
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException      if there is a card error
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\InvalidDataException    if the request has invalid parameters
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException  if there is an API communiation error
     */
    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        $this->chargeCard($transaction, true);
    }
	
	/**
	 * Charge a card
	 * 
	 * @param \JMS\Payment\CoreBundle\Model\FinancialTransactionInterface $transaction
	 * @param boolean $capture
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\FinancialException      if there is a card error
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\InvalidDataException    if the request has invalid parameters
	 * @throws \JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException  if there is an API communiation error
	 */
    protected function chargeCard(FinancialTransactionInterface $transaction, $capture = true)
    {
		// verify and charge card
		try {
			$charge = $this->client->charge($transaction, $capture);
			
		} catch(\Stripe_CardError $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];

			$ex = new FinancialException($err['code']);
			$transaction->setResponseCode($err['type']);
			$transaction->setReasonCode($err['code']);
			$ex->setFinancialTransaction($transaction);

			throw $ex;
		  
		} catch (Exception $e) {
			throw $e;
		}
		
		// complete the transaction
		$transaction->setReferenceNumber($charge->id);
        $transaction->setProcessedAmount($charge->amount/100);
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);		
	}
}