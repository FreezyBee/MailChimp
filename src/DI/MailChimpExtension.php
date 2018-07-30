<?php
declare(strict_types=1);

namespace FreezyBee\MailChimp\DI;

use FreezyBee\Httplug\DI\IClientProvider;
use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use FreezyBee\MailChimp\Api;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class MailChimpExtension extends CompilerExtension implements IClientProvider
{
    private $defaults = [
        'apiKey' => null,
        'apiUrl' => 'https://<dc>.api.mailchimp.com/',
        'httplugFactory' => '@httplug.factory.guzzle6',
        'debugger' => '%debugMode%'
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);

        // validate apiKey
        Validators::assert($config['apiKey'], 'string', 'MailChimp - missing apiKey');

        // validate apiUrl
        Validators::assert($config['apiUrl'], 'string', 'MailChimp - invalid apiUrl');

        if (!Strings::contains($config['apiUrl'], '<dc>')) {
            throw new AssertionException("MailChimp - missing <dc> apiUrl");
        }

        Validators::assert($config['apiUrl'], 'string', 'MailChimp - missing apiUrl');
        [, $datacentre] = explode('-', $config['apiKey']);
        $config['apiUrl'] = str_replace('<dc>', $datacentre, $config['apiUrl']);

        Validators::assert($config['apiUrl'], 'url', 'MailChimp - wrong apiUrl');

        $this->config = $config;

        $builder = $this->getContainerBuilder();

        $builder
            ->addDefinition($this->prefix('api'))
            ->setFactory(Api::class)
            ->setArguments(['@httplug.client.mailchimp']);
    }

    /**
     * Return array of client configs
     * clientName:
     *      factory: ...
     *      plugins:
     *          ...
     * @return array
     */
    /**
     * {@inheritdoc}
     */
    public function getClientConfigs(): array
    {
        bdump($this->config);
        return [
            'mailchimp' => [
                'factory' => $this->config['httplugFactory'],
                'plugins' => [
                    'authentication' => [
                        'type' => 'basic',
                        'username' => 'user',
                        'password' => $this->config['apiKey']
                    ],
                    'addHost' => [
                        'host' => $this->config['apiUrl']
                    ],
                    'headerDefaults' => [
                        'headers' => [
                            'Content-Type' => 'application/json; charset=utf-8'
                        ]
                    ]
                ]
            ]
        ];
    }
}
