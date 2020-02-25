<?php

namespace BaseApi\Traits;

use BaseApi\Contracts\EndpointInterface;
use BaseApi\Contracts\TokenManagerInterface;
use BaseApi\OauthToken;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\Exception\AccessException;

/**
 * Trait OauthAwareTrait
 * @package BaseApi\Traits
 */
trait OauthAwareTrait
{
    /**
     * @var OauthToken
     */
    private $token;
    /**
     * @var TokenManagerInterface
     */
    private $oauthTokenManager;
    /**
     * @var EndpointInterface
     */
    private $oauthEndpoint;

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOauthOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['clientId', 'redirectUri', 'oauthEndpointClass']);
        $resolver->setDefined(['scope']);
        $resolver->setAllowedTypes('clientId', 'int');
        $resolver->setAllowedTypes('redirectUri', 'string');
        $resolver->setAllowedTypes('scope', 'string');
    }

    /**
     * @throws AccessException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     */
    private function oauth()
    {
        return $this->getEndpoint($this->options['oauthEndpointClass']);
    }

    /**
     * @param string $code
     */
    private function generateOauthTokenFromCode(string $code)
    {
        $data = $this->oauth()->token($code);
        $this->token = new OauthToken($data);
    }

    /**
     * @param array $data
     */
    private function generateOauthTokenFromData(array $data)
    {
        $token = new OauthToken($data);

        if (!$token->isExpired()) {
            $data = $this->oauth()->refreshToken($token->getRefreshToken());
            $token = new OauthToken($data);
        }

        $this->options['token'] = $token->getAccessToken();

        $this->token = $token;
    }

    /**
     * @param $options
     */
    private function addState(& $options)
    {
        $options['state'] = sha1(implode('+',$options));
    }

    /**
     * @param TokenManagerInterface $tokenManager
     * @param string $code
     * @param string $state
     * @return bool
     */
    public function isTokenValid(TokenManagerInterface $tokenManager, $code = '', $state = '')
    {
        $data = $tokenManager->getData();

        if ($data) {
            $this->generateOauthTokenFromData($data);
            $tokenManager->setData($this->token);
            return true;
        } elseif ($code && $state == $this->getState()) {
            $this->generateOauthTokenFromCode($code);
            $tokenManager->setData($this->token);
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getState(): string
    {
        return sha1(implode('+',$this->options));
    }


    /**
     * @return string
     */
    public function getOAuthUrl()
    {
        $options = $this->options;

        $query = \http_build_query(\array_filter([
            'client_id' => $options['clientId'] ?? '',
            'redirect_uri' => $options['redirectUri'] ?? '',
            'scope' => $options['scope'] ?? '',
            'state' => $this->getState(),
            'response_type' => 'code',
        ]));

        return static::OAUTH_URL.'?'.$query;
    }

    /**
     * @return OauthToken
     */
    public function getToken(): OauthToken
    {
        return $this->token;
    }
}
