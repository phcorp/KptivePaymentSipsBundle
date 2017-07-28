<?php

namespace Kptive\PaymentSipsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        return $treeBuilder
            ->root('kptive_payment_sips', 'array')
                ->children()
                    ->arrayNode('config')
                        ->children()
                            ->scalarNode('merchant_id')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('merchant_country')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('pathfile')
                                ->cannotBeEmpty()
                                ->defaultValue('%kernel.root_dir%/config/sips/param/pathfile')
                            ->end()
                            ->scalarNode('templatefile')->defaultValue(null)->end()
                            ->scalarNode('currency_code')->cannotBeEmpty()->defaultValue(978)->end()
                            ->scalarNode('normal_return_url')->cannotBeEmpty()->defaultValue('%base_url%/payment/sips/back')->end()
                            ->scalarNode('cancel_return_url')->cannotBeEmpty()->defaultValue('%base_url%/payment/sips/back')->end()
                            ->scalarNode('automatic_response_url')->cannotBeEmpty()->defaultValue('%base_url%/payment/sips/notification')->end()
                        ->end()
                    ->end()
                    ->arrayNode('bin')
                        ->children()
                            ->scalarNode('request_bin')
                                ->cannotBeEmpty()
                                ->defaultValue('%kernel.root_dir%/config/sips/bin/static/request')
                            ->end()
                            ->scalarNode('response_bin')
                                ->cannotBeEmpty()
                                ->defaultValue('%kernel.root_dir%/config/sips/bin/static/response')
                            ->end()
                        ->end()
                    ->end()
                    ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->end()
            ->end();
    }
}
