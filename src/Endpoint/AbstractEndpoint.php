<?php

namespace BaseApi\Endpoint;

use BaseApi\Contracts\ClientInterface;
use Psr\Http\Message\ResponseInterface;


abstract class AbstractEndpoint
{
    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var string
     */
    protected $baseUrn = '/';
    /**
     * @var bool
     */
    protected $mapping = false;
    /**
     * Additional endpoint options
     *
     * @var array
     */
    protected $options = [];

    /**
     * @param ClientInterface $client
     * @param array $options
     */
    public function __construct(ClientInterface $client, array $options = [])
    {
        $this->client = $client;
        $this->options = (object)$options;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getUrn(array $params = []): string
    {
        $path = '';
        if ($params) {
            $params = array_map(
                function ($el) {
                    return trim($el, '/');
                },
                $params
            );
            $path = implode('/', $params);
        }

        return rtrim(
            sprintf(
                '%s%s/%s',
                rtrim($this->client->getUrn(), '/'),
                rtrim($this->baseUrn, '/'),
                $path
            ),
            '/'
        );
    }

    abstract protected function addRequestOptions(&$options);

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return array
     */
    protected function sendRequest(string $method, string $uri, array $options = []): array
    {
        $this->addRequestOptions($options);

        $options = array_filter($options, function ($var) {
            $var = array_filter($var);
            return !empty($var);
        });

        $request = $this->client->createRequest($method, $uri);
        $response = $this->client->send($request, $options);

        return $this->processResponse($response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws \RuntimeException
     * @return array
     */
    protected function processResponse(ResponseInterface $response): array
    {
        $bodyContents = $response->getBody()->getContents();
        return json_decode($bodyContents, true) ?: [$bodyContents];
    }
}
