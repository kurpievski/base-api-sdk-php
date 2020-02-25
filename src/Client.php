<?php

namespace BaseApi;

use BaseApi\Contracts\ClientInterface;
use BaseApi\Endpoint\AbstractEndpoint;
use BaseApi\Exception\ClientException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Client
 * @package BaseApi
 */
class Client extends GuzzleClient implements ClientInterface
{
    public const BASE_URI = '';

    /**
     * @var string
     */
    private $urn;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var RequestInterface
     */
    private $lastRequest;
    /**
     * @var ResponseInterface
     */
    private $lastResponse;
    /**
     * @var AbstractEndpoint[]
     */
    private $endpoints = [];
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @param string $baseUri
     * @param string $urn example: /api/v2
     * @param array $options extra parameters
     */
    public function __construct(array $options = [], string $urn = '', string $baseUri = '')
    {
        if (\method_exists($this, 'configureOptions')) {
            $this->configureOptions($options);
        }

        parent::__construct([
            'base_uri' => trim($baseUri ?: static::BASE_URI, '/'),
        ]);

        $this->urn = $urn;
        $this->options = $this->getOptionsResolver()->resolve($options);;
    }

    /**
     * @inheritdoc
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        try {
            /** @var ResponseInterface $response */
            $response = parent::send($request, $options);
        } catch (RequestException $e) {
            $this->lastRequest = $e->getRequest();
            $this->lastResponse = $e->getResponse();

            throw new ClientException($e, $e->getRequest(), $e->getResponse());
        }
        $this->lastRequest = $request;
        $this->lastResponse = $response;

        return $response;
    }

    /**
     * Get api urn (example: /api/v2)
     *
     * @return string
     */
    public function getUrn(): string
    {
        return $this->urn;
    }

    /**
     * @param string $name
     *
     * @return mixed null if option doesn't exists
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @return RequestInterface
     */
    public function getLastRequest(): RequestInterface
    {
        return $this->lastRequest;
    }

    /**
     * @return ResponseInterface
     */
    public function getLastResponse(): ResponseInterface
    {
        return $this->lastResponse;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $headers
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, string $uri, array $headers = []): RequestInterface
    {
        return new Request($method, $uri, $headers);
    }

    /**
     * @param string $class
     */
    protected function getEndpoint(string $class)
    {
        if (!isset($this->endpoints[$class])) {
            $this->endpoints[$class] = new $class($this, $this->options);
        }

        return $this->endpoints[$class];
    }

    /**
     * @return OptionsResolver
     */
    protected function getOptionsResolver()
    {
        if (!$this->optionsResolver) {
            $this->optionsResolver = new OptionsResolver();
        }

        return $this->optionsResolver;
    }
}
