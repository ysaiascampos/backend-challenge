<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use stdClass;
use Illuminate\Support\Facades\Log;

class ConectarApiController extends Controller
{
    /**
     * Consume un servicio $uriRest
     *
     * @param string $verboRest con verbo o método
     * @param string $uriRest
     * @param array $dataBodyJson para json en la petición
     * @param bool $download true si se debe descargar la respuesta
     * @return stdClass resultado del consumo con Guzzle
     */
    public static function consumoConGuzzle($verboRest, $uriRest, $dataBodyArray = null, $dataHeadersArray = null, $download = false, $timeout = 0, $localhost = false)
    {
        $apiRestHost = $localhost ? "http://127.0.0.1" : env('API_REST_HOST');
        $uriRestBaseService = env('API_REST_BASE_URL');
        $uriCompletaRest = $uriRestBaseService . $uriRest;
        $handler = new CurlHandler([
            'handle_factory' => new CurlFactory(2)
        ]);

        $client = new Client(['handler' => HandlerStack::create($handler),
            'base_uri' => $apiRestHost]);

        $requestOptions = [
            'headers' => [
            ],
            'http_errors' => false,
            'timeout' => $timeout
        ];

        if (!is_null($dataBodyArray)) {
            if (array_key_exists('multipart', $dataBodyArray)) {
                $requestOptions['multipart'] = $dataBodyArray['multipart'];
            } else {
                $requestOptions['headers']['Content-type'] = 'application/json; charset=utf-8';
                $requestOptions['headers']['Accept'] = 'application/json';
                $requestOptions['json'] = $dataBodyArray;
            }
        }
        if (!is_null($dataHeadersArray)) {
            $requestOptions['headers'] = array_merge($requestOptions['headers'], $dataHeadersArray);
        }

        $responseConsumoRest = $client->request($verboRest, $uriCompletaRest, $requestOptions);
        $dataRest = new stdClass();
        $body = $responseConsumoRest->getBody();
        if (json_decode($body, true) === null) {
            Log::error("Response no parseable: " . $verboRest . " " . $uriCompletaRest .
                PHP_EOL . "[REQUEST]:" . json_encode($dataBodyArray) . PHP_EOL . "[RESPONSE]:" . $body);
            $dataRest->statusCode = Constante::STATUS_500_ERROR_INTERNAL_ERROR;
            $dataRest->data = ['errors' => ['Error inesperado, debe reiniciar su sesión']];
        } else if (isset(json_decode($body, true)['errors'])) {
            Log::error("Response error controlado por el servicio: " . $verboRest . " " . $uriCompletaRest . PHP_EOL . "[REQUEST]:" . json_encode($dataBodyArray) . PHP_EOL . "[RESPONSE]:" . $body);
            $dataRest->statusCode = $responseConsumoRest->getStatusCode();
            $dataRest->data = json_decode($body, true);
        } else {
            $dataRest->statusCode = $responseConsumoRest->getStatusCode();
            $dataRest->data = json_decode($body, true);
        }
        $responseConsumoRest->getBody()->close();
        return $dataRest;
    }

}
