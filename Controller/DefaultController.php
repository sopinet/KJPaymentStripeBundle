<?php

namespace KJ\Payment\StripeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('KJPaymentStripeBundle:Default:index.html.twig', array('name' => $name));
    }
}
