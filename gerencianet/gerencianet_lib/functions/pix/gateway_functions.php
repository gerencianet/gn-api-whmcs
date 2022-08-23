<?php

use Gerencianet\Gerencianet;

/**
 * Validate if required parameters are empty
 * 
 * @param array $gatewayParams Payment Gateway Module Parameters
 */
function validateRequiredParams($gatewayParams)
{
    

    $requiredParams = array(
        'clientIdProd'        => $gatewayParams['clientIdProd'],
        'clientSecretProd'    => $gatewayParams['clientSecretProd'],
        'clientIdSandbox'     => $gatewayParams['clientIdSandbox'],
        'clientSecretSandbox' => $gatewayParams['clientSecretSandbox'],
    );

    $errors = array();

    foreach ($requiredParams as $key => $value) {
        if (empty($value)) {
            $errors[] = "$key é um campo obrigatório. Verificar as configurações do Portal de Pagamento.";
        };
    };

    if (!empty($errors)) {
        showException('Exception', $errors);
    }
}

/**
 * Get existing Pix Charge for a Invoice ID
 * 
 * @param int $invoiceId
 * 
 * @return array Pix Charge Info
 */
function getPixCharge($invoiceId) {
    return find('tblgerencianetpix', 'invoiceid', $invoiceId);
}

/**
 * Create a Pix Charge
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param array $gatewayParams Payment Gateway Module Parameters
 * 
 * @return array Generated Pix Charge
 */
function createPixCharge($api_instance, $gatewayParams)
{
    // Pix Parameters
    $pixKey         = $gatewayParams['pixKey'];
    $pixDays        = $gatewayParams['pixDays'];
    $pixDescription = $gatewayParams['description'];
    $pixDiscount = str_replace('%', '', $gatewayParams['pixDiscount']);

    if (empty($pixKey)) {
        showException('Exception', array('Chave Pix não informada. Verificar as configurações do Portal de Pagamento.'));

    } else {
        // Calculating pix amount with discount
        $pixAmount = $gatewayParams['amount'];
        $total = (double)$pixAmount - ((($pixAmount) * $pixDiscount) /100);
        $total = number_format($total, 2, '.', '');
        if (strpos($gatewayParams['paramsPix']['clientDocumentPix'],'/')) {
            $document = str_replace('/','',str_replace('-','',str_replace('.','',$gatewayParams['paramsPix']['clientDocumentPix']))); 
           
        }else{
            $document = str_replace('-','',str_replace('.','',$gatewayParams['paramsPix']['clientDocumentPix']));
        }
    
        $requestBody = [
            'calendario' => [
                'expiracao' => $pixDays * 86400 // Multiplying by 86400 (1 day seconds) because the API expects to receive a value in seconds
            ],
            'devedor'=>[
                'cpf'=> $document,
                'nome'=> $gatewayParams['paramsPix']['clientNamePix']
            ],
            'valor' => [
                'original' => strval($total) // String value from amount
            ],
            'chave' => $pixKey,
            "infoAdicionais" => [
                [
                    "nome" => "Pagamento em",
                    "valor" => $gatewayParams['companyname']
                ],
                [
                    "nome" => "Número do Pedido",
                    "valor" => "#".$gatewayParams['invoiceid']
                ]
            ]
        ];
    
        return createImmediateCharge($api_instance, $requestBody);
    }
}

/**
 * Store Pix Info
 * 
 * @param array $pix Pix Charge
 * @param array $gatewayParams Payment Gateway Module Parameters
 */
function storePixChargeInfo($pix, $gatewayParams)
{
    $txId      = $pix['txid'];
    $locId     = $pix['loc']['id'];
    $invoiceId = $gatewayParams['invoiceid'];

    $info = [
        'invoiceid' => $invoiceId,
        'txid' => $txId,
        'locid' => $locId,
    ];

    insert('tblgerencianetpix', $info);
}

/**
 * Create table for store Pix Charge Infos
 */
function createGerencianetPixTable()
{
    $tableName = 'tblgerencianetpix';

    if(!hasTable($tableName)) {
        $callback = function ($table) {
            $table->increments('id');
            $table->integer('invoiceid')->unique();
            $table->string('txid')->unique();
            $table->integer('locid');
            $table->string('e2eid');
        };

        createTable($tableName, $callback);
    }
}

/**
 * Create WebhookUrl for a Pix Charge
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param array $gatewayParams Payment Gateway Module Parameters
 */
function createWebhook($api_instance, $gatewayParams)
{
    // System Parameters
    $moduleName  = $gatewayParams['paymentmethod'];
    $systemUrl   = $gatewayParams['systemurl'];
    $callbackUrl = $systemUrl . 'modules/gateways/callback/' . $moduleName . '.php';

    $requestParams = [
        'chave' => $gatewayParams['pixKey']
    ];

    $requestBody = [
        'webhookUrl' => $callbackUrl
    ];

    configWebhook($api_instance, $requestParams, $requestBody);
}

/**
 * Generate QR Code for a Pix Charge
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param int $locId Location ID to generate QR Code
 * 
 * @return string QR Code Template
 */
function createQrCode($api_instance, $locId) {
    $qrcode = generateQRCode($api_instance, ['id' => $locId]);

    return generateQRCodeTemplate($qrcode);
}

/**
 * Generate QR Code Template including copy button
 * 
 * @param array $qrcode QR Code to generate template from
 * 
 * @return string $template Template
 */
function generateQRCodeTemplate($qrcode)
{
    
    // QR Code image
    $qrcodeImage = "<img src='{$qrcode['imagemQrcode']}' />\n";

    // Copy button 
    $copyButton = "<button class='btn btn-default' id='copyButton' onclick=\"copyQrCode('{$qrcode['qrcode']}')\">Copiar QR Code</button>\n";

    // get config gateway

    $paramsGateway = getGatewayVariables('gerencianet');
    $baseUrl = $paramsGateway['systemurl'];
    
    // Script for Copy action
    $script = "<script type=\"text/javascript\" src=\"$baseUrl/modules/gateways/gerencianet/gerencianet_lib/scripts/js/copyQrCode.js\"></script>";
   
    $template = $qrcodeImage.$copyButton.$script.$src;
    return $template;
}
    
    


/**
 * Create WebhookUrl for a Pix Charge
 * 
 * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
 * @param array $gatewayParams Payment Gateway Module Parameters
 * 
 * @return array Charge Refund Infos
 */
function refundCharge($api_instance, $gatewayParams)
{
    $invoiceId = $gatewayParams['invoiceid'];
    $e2eId = getValue('tblgerencianetpix', ['invoiceid' => $invoiceId], 'e2eid');

    if (empty($e2eId)) {
        showException('Gerencianet Exception', array("Fatura #$invoiceId não possui pagamentos a serem reembolsados."));

    } else {
        $requestParams = [
            'e2eId' => $e2eId,
            'id' => uniqid()
        ];

        $requestBody = [
            'valor' => $gatewayParams['amount']
        ];

        // Requesting Pix Devolution
        return devolution($api_instance, $requestParams, $requestBody);
    }
}