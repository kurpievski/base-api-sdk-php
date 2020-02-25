<?php

namespace BaseApi;

use BaseApi\Exception\InvalidArgumentException;

/**
 * Class OauthToken
 * @package kurpievski\BaseApi
 */
class OauthToken
{
    /**
     * @var string
     */
    private $accessToken;
    /**
     * @var int
     */
    private $expiresIn;
    /**
     * @var int
     */
    private $expires;
    /**
     * @var string
     */
    private $tokenType;
    /**
     * @var string
     */
    private $scope;
    /**
     * @var string
     */
    private $refreshToken;

    /**
     * Token constructor.
     * @param array $tokenData
     */
    public function __construct($data)
    {
        if (empty($data['access_token']) || empty($data['token_type'])) {
            throw new InvalidArgumentException();
        }

        $this->accessToken = $data['access_token'];
        $this->tokenType = $data['token_type'];
        $this->expiresIn = (int)($data['expires_in'] ?? 0);
        $this->expires = $data['expires'] ?? 0;
        $this->scope = $data['scope'] ?? '';
        $this->refreshToken = $data['refresh_token'] ?? '';

        if (isset($data['expires_in'])) {
            $this->expires = time() + $data['expires_in'];
        }
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires ? ($this->expires < time()) : false;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return int
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
