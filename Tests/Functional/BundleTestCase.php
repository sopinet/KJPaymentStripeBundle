<?php

namespace KJ\Payment\StripeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernel;

class BundleTestCase extends WebTestCase
{
    protected static function getKernelClass()
    {
        require_once __DIR__.'/app/AppKernel.php';
        return 'KJ\Payment\StripeBundle\Tests\Functional\app\AppKernel';
    }
}