<?php

namespace KJ\Payment\StripeBundle\Tests\Functional\app;

use JMS\Payment\CoreBundle\JMSPaymentCoreBundle;
use KJ\Payment\StripeBundle\KJPaymentStripeBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new FrameworkBundle(),
            new JMSPaymentCoreBundle(),
            new KJPaymentStripeBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.xml');
        $loader->load(__DIR__.'/config/api.yml');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/KJPaymentStripeBundle/cache';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/KJPaymentStripeBundle/logs';
    }

    public function getContainer()
    {
        if (null === $this->container) {
            $this->initializeContainer();
        }
        return parent::getContainer();
    }
}