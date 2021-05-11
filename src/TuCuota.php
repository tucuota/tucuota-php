<?php

namespace TuCuota;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use TuCuota\Exceptions\TucuotaRequestException;
use TuCuota\Exceptions\TuCuotaConnectionException;
use TuCuota\Exceptions\TucuotaPermissionsException;
use TuCuota\Exceptions\TucuotaException;
use TuCuota\Exceptions\TuCuotaNotFoundResource;

/**
 * Class TuCuota.
 */
class TuCuota
{
    const VERSION = '0.1';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    public $apiKey;
    public $environment;
    public $headers;
    public $base_uri;
    public $client;

    public function __construct(string $apiKey, string $environment = 'production')
    {
        // Check environment
        if (!in_array($environment, ['production', 'sandbox'])) {
            throw new TucuotaException("Invalid environment");
        }

        $this->environment = $environment;
        $this->apiKey = $apiKey;
        $this->headers = [
            "Authorization" => "Bearer " . $apiKey,
            "Content-Type" =>  "application/json",
            "accept" =>  "application/json",
            'User-Agent' => 'tucuota-php-client/1.0',
        ];
        $this->base_uri = $environment == 'production' ?
            'https://tucuota.com/api/' :
            'https://sandbox.tucuota.com/api/';

        $this->client = new Client([
                'base_uri' => $this->base_uri,
                'timeout'  => 15.0,
            ]);
    }

    public function request(string $method, string $uri, array $data = [])
    {
        try {
            $request = $this->client->request($method, $uri, [
                'headers' => $this->headers,
                $method == 'GET' ? 'query' : 'json' => $data
            ]);
        } catch (ConnectException $e) {
            throw new TuCuotaConnectionException($e->getMessage());
        } catch (ClientException $e) {

            $status = $e->getResponse()->getStatusCode();

            switch ($status) {
                case 401:
                    throw new TucuotaPermissionsException("Unauthorized. Verify API key and environment");

                case 403:
                    throw new TucuotaPermissionsException("Forbidden. Verify API key and environment");

                case 404:
                    throw new TuCuotaNotFoundResource("Resource not found");

                default:
                    $body = json_decode($e->getResponse()->getBody(), true);

                    $message = array_key_exists("message", $body) ? $body["message"] : Null;
                    $errors = array_key_exists("errors", $body) ? $body["errors"] : Null;

                    throw new TucuotaRequestException("$status: $message. $errors");
            }
        }

        try {
            $response = [];

            $body = json_decode($request->getBody(), true);
            $response['status'] = $request->getStatusCode();
            $response['message'] = array_key_exists("message", $body) ? $body["message"] : Null;
            $response['data'] = array_key_exists("data", $body) ? $body["data"] : Null;
            $response['meta'] = array_key_exists("meta", $body) ? $body["meta"] : Null;

            return $response;

        } catch (\Throwable $th) {
            throw new TucuotaException("Malformed response: " . $request->getBody());
        }
    }

    public function get(string $uri, array $data = [])
    {
        return $this->request(self::METHOD_GET, $uri, $data);
    }

    public function post(string $uri, array $data = [])
    {
        return $this->request(self::METHOD_POST, $uri, $data);
    }

    public function put(string $uri, array $data = [])
    {
        return $this->request(self::METHOD_PUT, $uri, $data);
    }

    public function delete(string $uri, array $data = [])
    {
        return $this->request(self::METHOD_DELETE, $uri, $data);
    }
}
