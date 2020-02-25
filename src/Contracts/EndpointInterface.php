<?php

namespace BaseApi\Contracts;

/**
 * Interface EndpointInterface
 * @package BaseApi\Contracts
 */
interface EndpointInterface
{
    /**
     * @param ClientInterface $client
     * @param array $options
     */
    public function __construct(ClientInterface $client, array $options = []);
}
