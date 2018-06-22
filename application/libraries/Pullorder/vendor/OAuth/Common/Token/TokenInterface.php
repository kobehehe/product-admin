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
namespace OAuth\Common\Token;

/**
 * Base token interface for any OAuth version.
 */
interface TokenInterface
{
    /**
     * Denotes an unknown end of life time.
     */
    const EOL_UNKNOWN = -9001;

    /**
     * Denotes a token which never expires, should only happen in OAuth1.
     */
    const EOL_NEVER_EXPIRES = -9002;

    /**
     * @return string
     */
    public function getAccessToken();

    /**
     * @return int
     */
    public function getEndOfLife();

    /**
     * @return array
     */
    public function getExtraParams();

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken);

    /**
     * @param int $endOfLife
     */
    public function setEndOfLife($endOfLife);

    /**
     * @param int $lifetime
     */
    public function setLifetime($lifetime);

    /**
     * @param array $extraParams
     */
    public function setExtraParams(array $extraParams);

    /**
     * @return string
     */
    public function getRefreshToken();

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken);
}
