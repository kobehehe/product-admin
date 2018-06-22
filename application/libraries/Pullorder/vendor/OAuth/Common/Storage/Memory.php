<?php
/*
 * Copyright 1999-2015 Alibaba Group.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace OAuth\Common\Storage;

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;

/*
 * Stores a token in-memory only (destroyed at end of script execution).
 */
class Memory implements TokenStorageInterface
{
    /**
     * @var object|TokenInterface
     */
    protected $tokens;

    /**
     * @var array
     */
    protected $states;

    public function __construct()
    {
        $this->tokens = array();
        $this->states = array();
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAccessToken($service)
    {
        if ($this->hasAccessToken($service)) {
            return $this->tokens[$service];
        }

        throw new TokenNotFoundException('Token not stored');
    }

    /**
     * {@inheritDoc}
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $this->tokens[$service] = $token;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAccessToken($service)
    {
        return isset($this->tokens[$service]) && $this->tokens[$service] instanceof TokenInterface;
    }

    /**
     * {@inheritDoc}
     */
    public function clearToken($service)
    {
        if (array_key_exists($service, $this->tokens)) {
            unset($this->tokens[$service]);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllTokens()
    {
        $this->tokens = array();

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAuthorizationState($service)
    {
        if ($this->hasAuthorizationState($service)) {
            return $this->states[$service];
        }

        throw new AuthorizationStateNotFoundException('State not stored');
    }

    /**
     * {@inheritDoc}
     */
    public function storeAuthorizationState($service, $state)
    {
        $this->states[$service] = $state;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAuthorizationState($service)
    {
        return isset($this->states[$service]) && null !== $this->states[$service];
    }

    /**
     * {@inheritDoc}
     */
    public function clearAuthorizationState($service)
    {
        if (array_key_exists($service, $this->states)) {
            unset($this->states[$service]);
        }

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllAuthorizationStates()
    {
        $this->states = array();

        // allow chaining
        return $this;
    }
}
