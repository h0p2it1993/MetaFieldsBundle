<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle\DependencyInjection;

use KimaiPlugin\MetaFieldsBundle\EventSubscriber\ExpenseMetaFieldSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Parser;

final class MetaFieldsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        if ('test' === $container->getParameter('kernel.environment')) {
            return;
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        /** @var array<string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['ExpensesBundle'])) {
            $container->removeDefinition(ExpenseMetaFieldSubscriber::class);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        $yamlParser = new Parser();

        $config = $yamlParser->parseFile(__DIR__ . '/../Resources/config/packages/jms_serializer.yaml');
        $container->prependExtensionConfig('jms_serializer', $config['jms_serializer']);

        $config = $yamlParser->parseFile(__DIR__ . '/../Resources/config/packages/nelmio_api_doc.yaml');
        $container->prependExtensionConfig('nelmio_api_doc', $config['nelmio_api_doc']);

        $container->prependExtensionConfig('kimai', [
            'permissions' => [
                'roles' => [
                    'ROLE_SUPER_ADMIN' => [
                        'configure_meta_fields',
                    ],
                ],
            ],
        ]);
    }
}
