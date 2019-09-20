<?php

declare(strict_types=1);

namespace PTS\SyliusReferralPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class PTSSyliusReferralExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');

        $paths = $config['channel_paths'];
        $namedPaths = [];
        foreach ($paths as $path) {
            $pathArr = [
                'path' => $path['path'],
                'domain' => $path['domain'],
                'default' => $path['default']
            ];
            $namedPaths[$path['name']] = $pathArr;
        }
        if (isset($config['customers']['enroller_edit']['enabled'])) {
            $enabledEnrollerEdit = $config['customers']['enroller_edit']['enabled'];
        }
        $container->setParameter('app_edit_enroller_enabled', $enabledEnrollerEdit);
        $container->setParameter('app_channel_paths', $namedPaths);
    }
}
