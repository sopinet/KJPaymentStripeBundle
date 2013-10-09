<?php

namespace KJ\Payment\StripeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kj_payment_stripe');
		$rootNode
			->children()
				->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
				->scalarNode('api_version')->isRequired()->cannotBeEmpty()->end();
		
        return $treeBuilder;
    }
}
