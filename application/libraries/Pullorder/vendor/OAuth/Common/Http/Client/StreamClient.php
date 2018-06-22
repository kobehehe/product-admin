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

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Client implementation for streams/file_get_contents
 */
class StreamClient extends AbstractClient
{
    /**
     *
     * @param UriInterface $endpoint
     * @param mixed        $requestBody
     * @param array        $extraHeaders
     * @param string       $method
     *
     * @return string
     *
     * @throws TokenResponseException
     * @throws \InvalidArgumentException
     */
    public function retrieveResponse(
        UriInterface $endpoint,
        $requestBody,
        array $extraHeaders = array(),
        $method = 'POST'
    ) {
        // Normalize method name
        $method = strtoupper($method);

        $this->normalizeHeaders($extraHeaders);

        if ($method === 'GET' && !empty($requestBody)) {
            throw new \InvalidArgumentException('No body expected for "GET" request.');
        }

        if (!isset($extraHeaders['Content-Type']) && $method === 'POST' && is_array($requestBody)) {
            $extraHeaders['Content-Type'] = 'Content-Type: application/x-www-form-urlencoded';
        }

        $host = 'Host: '.$endpoint->getHost();
        // Append port to Host if it has been specified
        if ($endpoint->hasExplicitPortSpecified()) {
            $host .= ':'.$endpoint->getPort();
        }

        $extraHeaders['Host']       = $host;
        $extraHeaders['Connection'] = 'Connection: close';

        if (is_array($requestBody)) {
            $requestBody = http_build_query($requestBody, '', '&');
        }
        $extraHeaders['Content-length'] = 'Content-length: '.strlen($requestBody);

        $context = $this->generateStreamContext($requestBody, $extraHeaders, $method);

        $level = error_reporting(0);
        $response = file_get_contents($endpoint->getAbsoluteUri(), false, $context);

        if(defined("OAUTH_LOG") and OAUTH_LOG == true){
            $log_file = LOGS_DIR."/oauth_request_".date("Ymd").".log";
            $log_message = date("Y-m-d H:i:s") ."\r\n";
            $log_message .= "request : $method ". $endpoint->getAbsoluteUri()."\r\n". var_export($extraHeaders, true)."\r\n";
            if(!empty($requestBody)){
                $log_message .= "body: $requestBody\r\n";
            }
            $log_message .= "return: ". var_export($response, true)."\r\n\r\n";
            mkdirp(dirname($log_file));
            $fp = fopen($log_file, 'a');
            fwrite($fp, $log_message);
            fclose($fp);

        }

        error_reporting($level);
        if (false === $response) {
            $lastError = error_get_last();
            if (is_null($lastError)) {
                throw new TokenResponseException(
                    'Failed to request resource. HTTP Code: ' .
                    ((isset($http_response_header[0]))?$http_response_header[0]:'No response')
                );
            }
            throw new TokenResponseException($lastError['message']);
        }

        return $response;
    }

    private function generateStreamContext($body, $headers, $method)
    {

        $options =  array(
            'http' => array(
                'method'           => $method,
                'header'           => implode("\r\n", array_values($headers)),
                'content'          => $body,
                'protocol_version' => '1.1',
                'user_agent'       => $this->userAgent,
                'max_redirects'    => $this->maxRedirects,
                'timeout'          => $this->timeout
            ),
        );

        return stream_context_create(  $options );


    }
}
