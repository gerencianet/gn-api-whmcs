<?php

require_once __DIR__ . '/../../../../../../../init.php';

if (file_exists($autoload = realpath(__DIR__ . "/../../../../gerencianet-sdk/autoload.php"))) {
	require_once $autoload;
} else {
	echo json_encode(array("error" => "could not find autoload file for the SDK"));
    return;
}

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;

App::load_function('gateway');


// Fetch gateway configuration parameters
$gatewayParams = getGatewayVariables('efi');


if ($gatewayParams == null || $gatewayParams['activeOpenFinance'] != 'on') {
    echo json_encode(array("error", "Open Finance resources are off or unavailable"));
    return;
}

if (isset($_GET['participants'])) {
    $options = array();
    if ($gatewayParams['sandbox'] != 'on') {
        $options['client_id'] = $gatewayParams['clientIdProd'];
        $options['client_secret'] = $gatewayParams['clientSecretProd'];
        $options['sandbox'] = FALSE;
        $options['certificate'] = $gatewayParams['pixCert'];
    } 

    else {
        $options['client_id'] = $gatewayParams['clientIdSandbox'];
        $options['client_secret'] = $gatewayParams['clientSecretSandbox'];
        $options['sandbox'] = TRUE;
        $options['certificate'] = $gatewayParams['pixCert'];
    }

    try {

        $api = Gerencianet::getInstance($options);

        $response = $api->ofListParticipants($params);

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    } catch (GerencianetException $e) {
        echo json_encode(array(
            'code' => $e->code,
            'error' => $e->error,
            'description' => $e->errorDescription
        ));
    } catch (Exception $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }

} 