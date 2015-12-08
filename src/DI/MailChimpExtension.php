<?php

namespace FreezyBee\MailChimp\DI;

use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * Class MailChimpExtension
 * @package FreezyBee\MailChimp\DI
 */
class MailChimpExtension extends CompilerExtension
{
    private $defaults = [
        'apiKey' => null,
        'apiUrl' => 'https://<dc>.api.mailchimp.com/3.0/',
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
        list(, $datacentre) = explode('-', $config['apiKey']);
        $config['apiUrl'] = str_replace('<dc>', $datacentre, $config['apiUrl']);

        Validators::assert($config['apiUrl'], 'url', 'MailChimp - wrong apiUrl');

        $builder = $this->getContainerBuilder();

        $api = $builder->addDefinition($this->prefix('api'))
            ->setClass('FreezyBee\MailChimp\Api')
            ->setArguments([$config]);

        if ($config['debugger']) {
            $builder->addDefinition($this->prefix('panel'))
                ->setClass('FreezyBee\MailChimp\Diagnostics\Panel')
                ->setInject(false);
            $api->addSetup($this->prefix('@panel') . '::register', ['@self']);
        }
    }
}
