<?php
declare(strict_types=1);

namespace FreezyBee\MailChimp;

use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class Api
{
    use SmartObject;

    /** @var HttpClient */
    private $client;

    /** @var RequestFactory */
    private $requestFactory;

    /**
     * @param HttpClient $client
     * @param RequestFactory $requestFactory
     */
    public function __construct(HttpClient $client, RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
        $this->client = $client;
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
        try {
            $body = $parameters ? Json::encode($parameters) : null;
        } catch (JsonException $e) {
            throw new MailChimpException($e->getMessage(), 667, $e);
        }

        $request = $this->requestFactory->createRequest($method, "3.0/$endpoint", [], $body);

        try {
            $response = $this->client->sendRequest($request);
        } catch (\Exception $e) {
            throw new MailChimpException($e->getMessage(), 666, $e);
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new MailChimpException($response->getBody()->getContents(), $response->getStatusCode());
        }

        try {
            return Json::decode($response->getBody()->getContents());
        } catch (JsonException $e) {
            throw new MailChimpException($e->getMessage(), 667, $e);
        }
    }
}
