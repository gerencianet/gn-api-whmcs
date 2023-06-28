<?php

use Gerencianet\Gerencianet;
use Gerencianet\Exception\GerencianetException;


/**
 * Generates an random prefix to compose a unique ID for
 * open finance idempotency key.
 */
function uniqidReal($lenght = 30) {
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new Exception("no cryptographically secure random function available");
    }
    return substr(bin2hex($bytes), 0, $lenght);
}


/**
 * Retrieve Genrencianet API Instance
 * 
 * @param array $gatewayParams Payment Gateway Module Parameters
 * 
 * @return \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 */
function getGerencianetApiInstance($gatewayParams)
{
    // Pix Parameters
    $pixCert = $gatewayParams['pixCert'];

    // Boolean Parameters
    $mtls    = ($gatewayParams['mtls'] == 'on');
    $debug   = ($gatewayParams['debug'] == 'on');
    $sandbox = ($gatewayParams['sandbox'] == 'on');

    // Client Authentication Parameters
    $clientIdSandbox     = $gatewayParams['clientIdSandbox'];
    $clientIdProd        = $gatewayParams['clientIdProd'];
    $clientSecretSandbox = $gatewayParams['clientSecretSandbox'];
    $clientSecretProd    = $gatewayParams['clientSecretProd'];

    // Getting API Instance
    $api_instance = Gerencianet::getInstance(
        array(
            'client_id' => $sandbox ? $clientIdSandbox : $clientIdProd,
            'client_secret' => $sandbox ? $clientSecretSandbox : $clientSecretProd,
            'certificate' => $pixCert,
            'sandbox' => $sandbox,
            'debug' => $debug,
            'headers' => [
                'x-skip-mtls-checking' => $mtls ? 'false' : 'true', // Needs to be string
                'x-idempotency-key' => uniqid(uniqidReal(), true) // For open finance usage
            ]
        )
    );

    return $api_instance;
}

/**
 * Create Immediate Charge
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param array $requestBody Request Body
 * 
 * @return array Generated Charge
 */
function createImmediateCharge($api_instance, $requestBody)
{

    try {
        $responseData = $api_instance->pixCreateImmediateCharge([], $requestBody);

        return $responseData;

    } catch (GerencianetException $e) {
        showException('Gerencianet Exception', array($e));

    } catch (Exception $e) {
        showException('Exception', array($e));
    }
}

/**
 * Configure WebhookUrl
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param array $requestParams Request Params
 * @param array $requestBody Request Body
 */
function configWebhook($api_instance, $requestParams, $requestBody)
{
    try {
        $api_instance->pixConfigWebhook($requestParams, $requestBody);

    } catch (GerencianetException $e) {
        throw new \Exception("<strong>Falha ao cadastrar webhook:<br></strong>" . $e); 
    } catch (Exception $e) {
        throw new \Exception("<strong>Falha ao cadastrar webhook:<br></strong>" . $e);
    }
}

/**
 * Generate QR Code for a Pix Charge
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param array $requestParams Request Params
 * 
 * @return array QR Code Infos
 */
function generateQRCode($api_instance, $requestParams)
{
    try {
        $qrcode = $api_instance->pixGenerateQRCode($requestParams);

        return $qrcode;

    } catch (GerencianetException $e) {
        showException('Efí Exception', array($e));

    } catch (Exception $e) {
        showException('Exception', array($e));
    }
}

/**
 * Refund Pix Charge
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param array $requestParams Request Params
 * @param array $requestBody Request Body
 * 
 * @return array Charge Refund Infos
 */
function devolution($api_instance, $requestParams, $requestBody)
{
    try {
        $responseData = $api_instance->pixDevolution($requestParams, $requestBody);

        return $responseData;

    } catch (GerencianetException $e) {
        showException('Efí Exception', array($e));

    } catch (Exception $e) {
        showException('Exception', array($e));
    }
}