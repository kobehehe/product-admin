<?php


namespace OAuth\OAuth1\Service;
// 需注入到 OAuth\OAuth1 命名空间


use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Client\ClientInterface;


/**
 * Class Etsy 实现 oauth 1 Service

 */
class Etsy extends AbstractService
{

    protected $scopes = array();

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        SignatureInterface $signature,
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $signature, $baseApiUri);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://openapi.etsy.com/v2/');
        }
    }


    public function getRequestTokenEndpoint()
    {
        $uri = new Uri($this->baseApiUri . 'oauth/request_token');
        $scopes = $this->getScopes();

        if (count($scopes)) {
            $uri->setQuery('scope=' . implode('%20', $scopes));
        }

        return $uri;
    }


    public function getAuthorizationEndpoint()
    {
        return new Uri($this->baseApiUri);
    }


    public function getAccessTokenEndpoint()
    {
        return new Uri($this->baseApiUri . 'oauth/access_token');
    }


    protected function parseRequestTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('无法解析response.');
        } elseif (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true') {
            throw new TokenResponseException('retrieving token出错');
        }

        return $this->parseAccessTokenResponse($responseBody);
    }


    protected function parseAccessTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('无法解析response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('retrieving token出错: "' . $data['error'] . '"');
        }

        $token = new StdOAuth1Token();

        $token->setRequestToken($data['oauth_token']);
        $token->setRequestTokenSecret($data['oauth_token_secret']);
        $token->setAccessToken($data['oauth_token']);
        $token->setAccessTokenSecret($data['oauth_token_secret']);

        $token->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
        unset($data['oauth_token'], $data['oauth_token_secret']);
        $token->setExtraParams($data);

        return $token;
    }


    public function setScopes(array $scopes)
    {
        if (!is_array($scopes)) {
            $scopes = array();
        }

        $this->scopes = $scopes;
        return $this;
    }


    public function getScopes()
    {
        return $this->scopes;
    }
}
