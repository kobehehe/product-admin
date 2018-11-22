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
namespace OAuth\Common\Http\Client;

/**
 * Abstract HTTP client
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var string The user agent string passed to services
     */
    protected $userAgent;

    /**
     * @var int The maximum number of redirects
     */
    protected $maxRedirects = 5;

    /**
     * @var int The maximum timeout
     */
    protected $timeout = 300;

    /**
     * Creates instance
     *
     * @param string $userAgent The UA string the client will use
     */
    public function __construct($userAgent = 'Chrome')
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @param int $redirects Maximum redirects for client
     *
     * @return ClientInterface
     */
    public function setMaxRedirects($redirects)
    {
        $this->maxRedirects = $redirects;

        return $this;
    }

    /**
     * @param int $timeout Request timeout time for client in seconds
     *
     * @return ClientInterface
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param array $headers
     */
    public function normalizeHeaders(&$headers)
    {
        // Normalize headers
        array_walk(
            $headers,
            function (&$val, &$key) {
                $key = ucfirst(strtolower($key));
                $val = ucfirst(strtolower($key)) . ': ' . $val;
            }
        );
    }
}
