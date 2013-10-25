<?php

namespace KJ\Payment\StripeBundle\Tests\Controller;

use JMS\Payment\CoreBundle\PluginController\Result;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class PaymentControllerTest extends WebTestCase
{
    /**
     * @var Symfony\Component\HttpKernel\AppKernel
     */
    protected static $client;


    public function testValidVisaTransaction()
    {
        return $this->executeValidCardTransaction('Visa', 4242424242424242);
    }

    public function testValidMasterCardTransaction()
    {
        return $this->executeValidCardTransaction('MasterCard', 5555555555554444);
    }

    public function testValidAmericanExpressTransaction()
    {
        return $this->executeValidCardTransaction('American Express', 378282246310005);
    }

    public function testValidDiscoverTransaction()
    {
        // NOTE: non US account will fail
        return $this->executeValidCardTransaction('Discover', 6011111111111117);
    }

    public function testValidDinersClubTransaction()
    {
        // NOTE: non US account will fail
        return $this->executeValidCardTransaction('Diners Club', 30569309025904);
    }

    public function testValidJCBTransaction()
    {
        // NOTE: non US account will fail
        return $this->executeValidCardTransaction('JCB', 3530111333300000);
    }

    public function testLuhnCheckFailTransaction()
    {
        $form = $this->getCardForm(73.57, 'USD', array('number' => 4242424242424241));

        $this->assertFalse($form->isValid(), 'Form valid, should be invalid.');
    }

    public function testCardDeclineTransaction()
    {
        $form = $this->getCardForm(73.57, 'USD', array('number' => 4000000000000002));

        $this->assertTrue($form->isValid(), 'Invalid form data, should be valid.');

        $ppc = $this->get('payment.plugin_controller');
        $ppc->createPaymentInstruction($instruction = $form->getData());

        $this->assertNull($instruction->getPendingTransaction(), 'Pending transactions found, none expected.');

        $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());

        $this->assertNotNull($payment, 'Payment not created.');

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());


        $this->assertNotEquals(Result::STATUS_SUCCESS, $result->getStatus(), 'Transaction successful, should be unsuccessful');
        $this->assertEquals('card_declined', $result->getReasonCode(), 'Unexpected reason code '.$result->getReasonCode().', should be card_declined');
    }

    public function testInvalidCvcTransaction()
    {
        $form = $this->getCardForm(73.57, 'USD', array('cvc' => 99));

        $this->assertFalse($form->isValid(), 'Form valid, should be invalid.');
    }

    public function testInvalidCvcResponseTransaction()
    {
        $form = $this->getCardForm(73.57, 'USD', array('number' => 4000000000000127));

        $this->assertTrue($form->isValid(), 'Invalid form data, should be valid.');

        $ppc = $this->get('payment.plugin_controller');
        $ppc->createPaymentInstruction($instruction = $form->getData());

        $this->assertNull($instruction->getPendingTransaction(), 'Pending transactions found, none expected.');

        $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());

        $this->assertNotNull($payment, 'Payment not created.');

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());


        $this->assertNotEquals(Result::STATUS_SUCCESS, $result->getStatus(), 'Transaction successful, should be unsuccessful');
        $this->assertEquals('incorrect_cvc', $result->getReasonCode(), 'Unexpected reason code '.$result->getReasonCode().', should be incorrect_cvc');
    }

    public function testExpireCardTransaction()
    {
        $form = $this->getCardForm(73.57, 'USD', array('number' => 4000000000000069));

        $this->assertTrue($form->isValid(), 'Invalid form data, should be valid.');

        $ppc = $this->get('payment.plugin_controller');
        $ppc->createPaymentInstruction($instruction = $form->getData());

        $this->assertNull($instruction->getPendingTransaction(), 'Pending transactions found, none expected.');

        $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());

        $this->assertNotNull($payment, 'Payment not created.');

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());


        $this->assertNotEquals(Result::STATUS_SUCCESS, $result->getStatus(), 'Transaction successful, should be unsuccessful');
        $this->assertEquals('expired_card', $result->getReasonCode(), 'Unexpected reason code '.$result->getReasonCode().', should be expired_card');
    }

    public function testProcessingErrorTransaction()
    {
        $form = $this->getCardForm(73.57, 'USD', array('number' => 4000000000000119));

        $this->assertTrue($form->isValid(), 'Invalid form data, should be valid.');

        $ppc = $this->get('payment.plugin_controller');
        $ppc->createPaymentInstruction($instruction = $form->getData());

        $this->assertNull($instruction->getPendingTransaction(), 'Pending transactions found, none expected.');

        $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());

        $this->assertNotNull($payment, 'Payment not created.');

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());


        $this->assertNotEquals(Result::STATUS_SUCCESS, $result->getStatus(), 'Transaction successful, should be unsuccessful');
        $this->assertEquals('processing_error', $result->getReasonCode(), 'Unexpected reason code '.$result->getReasonCode().', should be processing_error');
    }


    protected function getCardForm($amount, $currency, $cardData = array())
    {
        $default = array(
            'name' => 'Test User',
            'number' => '4242424242424242',
            'exp_month' => '12',
            'exp_year' => date('Y'),
            'cvc' => '123',
            'address_line1' => '123 Test Street',
            'address_line2' => '',
            'address_city' => 'City',
            'address_state' => 'State',
            'address_country' => 'GB',
            'address_zip' => 'W1A 1AA',
        );
        $cardData = array_merge($default, $cardData);

        $form = $this->get('form.factory')->create('jms_choose_payment_method', null, array(
            'amount'   => $amount,
            'currency' => $currency,
            'default_method' => 'stripe_credit_card',
            'predefined_data' => array(
                'stripe_credit_card' => array(
                    'payment_description' => 'Test payment',
                ),
            ),
        ));

        $form->bind(array(
            'method' => 'stripe_credit_card',
            'data_stripe_credit_card' => $cardData,
        ));

        return $form;
    }

    protected function executeValidCardTransaction($cardType, $cardNumber)
    {
        $form = $this->getCardForm(73.57, 'USD', array('number' => $cardNumber));

        $this->assertTrue($form->isValid(), $cardType.': Invalid form data, should be valid.');

        $ppc = $this->get('payment.plugin_controller');
        $ppc->createPaymentInstruction($instruction = $form->getData());

        $this->assertNull($instruction->getPendingTransaction(), $cardType.': Pending transactions found, none expected.');

        $payment = $ppc->createPayment($instruction->getId(), $instruction->getAmount() - $instruction->getDepositedAmount());

        $this->assertNotNull($payment, $cardType.': Payment not created.');

        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());

        $this->assertEquals(Result::STATUS_SUCCESS, $result->getStatus(), $cardType.': Transaction not successful, reason '.$result->getReasonCode());
    }

    /**
     * @return null
     */
    public function setUp()
    {
        self::$client = self::createClient();

        parent::setUp();
    }

    /**
     * @return null
     */
    public function tearDown()
    {
        static::$kernel->shutdown();

        parent::tearDown();
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    public function get($service)
    {
        return static::$kernel->getContainer()->get($service);
    }
}
