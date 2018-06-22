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
namespace OAuth\OAuth1\Token;

use OAuth\Common\Token\TokenInterface as BaseTokenInterface;

/**
 * OAuth1 specific token interface
 */
interface TokenInterface extends BaseTokenInterface
{
    /**
     * @return string
     */
    public function getAccessTokenSecret();

    /**
     * @param string $accessTokenSecret
     */
    public function setAccessTokenSecret($accessTokenSecret);

    /**
     * @return string
     */
    public function getRequestTokenSecret();

    /**
     * @param string $requestTokenSecret
     */
    public function setRequestTokenSecret($requestTokenSecret);

    /**
     * @return string
     */
    public function getRequestToken();

    /**
     * @param string $requestToken
     */
    public function setRequestToken($requestToken);
}
