<?php

namespace BaseApi\Contracts;

use BaseApi\OauthToken;

/**
 * Interface TokenManagerInterface
 * @package BaseApi\Contracts
 */
interface TokenManagerInterface
{
    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param OauthToken $token
     */
    public function setData(OauthToken $token);
}
