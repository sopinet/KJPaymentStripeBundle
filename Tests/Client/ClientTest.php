<?php

namespace KJ\Payment\StripeBundle\Tests\Client;

use KJ\Payment\StripeBundle\Client\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const API_KEY     = 'sk_test_HQcikp1OHuHqcoPJWuScNIVu';
    const API_VERSION = '2013-08-13';


    public function testClientAuthentication()
    {
        $client = new Client(self::API_KEY, self::API_VERSION);

        $this->assertEquals(true, $client->testAuth());
    }

}
