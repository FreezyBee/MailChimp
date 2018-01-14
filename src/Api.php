<?php

namespace FreezyBee\MailChimp;

use FreezyBee\MailChimp\Http\Resource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class Api
 * @package FreezyBee\MailChimp
 */
class Api
{
    use SmartObject;

    /** @var \Closure */
    public $onResponse;

    /**
     * @var array config
     */
    private $config;

    /**
     * Api constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $parameters
     * @return mixed
     * @throws MailChimpException
     */
    public function call($method = 'GET', $endpoint = '', array $parameters = [])
    {
        $uri = $this->config['apiUrl'] . (strlen($endpoint) && $endpoint[0] == '/' ? substr($endpoint, 1) : $endpoint);
        $headers = ['Authorization' => 'apikey ' . $this->config['apiKey']];

        try {
            $body = $parameters ? Json::encode($parameters) : null;
        } catch (JsonException $e) {
            throw new MailChimpException('MailChimp request - invalid json', 667, $e);
        }

        $client = new Client;
        $request = new Request($method, $uri, $headers, $body);
        $resource = new Resource($request);

        try {
            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $client->send($request);
            $resource->setSuccessResponse($response);

        } catch (GuzzleException $e) {
            $resource->setErrorResponse($e);
        }

        $this->onResponse($resource);

        if ($resource->getException()) {
            throw new MailChimpException('MailChimp response - error', 666, $resource->getException());
        } else {
            return $resource->getResult();
        }
    }

    // TODO
    /**
     *
     */
    public function callService()
    {

    }
}
