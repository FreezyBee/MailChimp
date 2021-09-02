<?php

declare(strict_types=1);

namespace FreezyBee\MailChimp\DI;

use FreezyBee\Httplug\DI\IClientProvider;
use FreezyBee\MailChimp\Api;
use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class MailChimpExtension extends CompilerExtension implements IClientProvider
{
    /** @var mixed */
    protected $config;

    /** @var string */
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        $this->config = new class {
            /** @var string */
            public $apiUrl = 'https://<dc>.api.mailchimp.com/';

            /** @var string */
            public $httplugFactory = '@httplug.factory.guzzle7';
        };
    }

    public function loadConfiguration()
    {
        $config = $this->config;

        if (!Strings::contains($config->apiUrl, '<dc>')) {
            throw new AssertionException("MailChimp - missing <dc> apiUrl");
        }

        Validators::assert($config->apiUrl, 'string', 'MailChimp - missing apiUrl');
        [, $datacentre] = explode('-', $this->apiKey);
        $config->apiUrl = str_replace('<dc>', $datacentre, $config->apiUrl);

        Validators::assert($config->apiUrl, 'url', 'MailChimp - wrong apiUrl');

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
     * @return mixed[]
     */
    public function getClientConfigs(): array
    {
        return [
            'mailchimp' => [
                'factory' => $this->config->httplugFactory,
                'plugins' => [
                    'authentication' => [
                        'type' => 'basic',
                        'username' => 'user',
                        'password' => $this->apiKey
                    ],
                    'addHost' => [
                        'host' => $this->config->apiUrl
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
