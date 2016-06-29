<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
include_once '../gerencianet_lib/Gerencianet_WHMCS_Interface.php';

if (version_compare(PHP_VERSION, '5.4.39') < 0) 
    die();

include_once '../gerencianet_lib/GerencianetIntegration.php';

$gatewayModuleName = 'gerencianetcharge';
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

if (!isset($_POST['notification'])) {
    die("Don't exist any response");
}

$notificationToken  = $_POST['notification'];
$clientIDProd       = $gatewayParams['clientIDProd'];
$clientSecretProd   = $gatewayParams['clientSecretProd'];
$clientIDDev        = $gatewayParams['clientIDDev'];
$clientSecretDev    = $gatewayParams['clientSecretDev'];
$configSandbox      = $gatewayParams['configSandbox'];
$configDebug        = $gatewayParams['configDebug'];
$idConta            = $gatewayParams['idConta'];
$descontoBoleto     = (double)$gatewayParams['descontoBoleto'];
$discountType       = $gatewayParams['tipoDesconto'];
$adminWHMCS         = $gatewayParams['whmcsAdmin'];

$gnIntegration = new GerencianetIntegration($clientIDProd, $clientSecretProd, $clientIDDev, $clientSecretDev, $configSandbox, $idConta);
$notificationJson = $gnIntegration->notificationCheck($notificationToken);
$notification = json_decode($notificationJson, true);

$notificationCode = $notification['code'];

if($notificationCode != 200){
    die("WHMCS - Falha na autenticacao");
}

$notificationData     = $notification['data'];
$lastModificationData = end($notificationData);
$id                   = $lastModificationData['id'];
$type                 = $lastModificationData['type'];
$invoiceId            = (int)$lastModificationData['custom_id'];
$status               = $lastModificationData['status']['current'];
$transactionId        = $lastModificationData['identifiers']['charge_id'];
$creationDate         = $lastModificationData['created_at'];
$isNewTransaction     = false;

$logGerencianet = array(
    "invoiceid"     => $invoiceId,
    "transactionId" => $transactionId,
    "created_at"    => $creationDate
);

$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

/* **************** registra os logs notificados pela Gerencianet no WHMCS ************ */

if ($configDebug == "on" && $status != 'paid')
    logTransaction($gatewayParams['name'],$logGerencianet,$status);

/* ************************************************************************************ */

if ($status == "paid") 
{
    $invoiceValues['invoiceid'] = $invoiceId;
    $invoiceData                = localAPI("getinvoice", $invoiceValues, $adminWHMCS);
    $invoiceStatus              = $invoiceData['status'];
    $credit                     = number_format((double)$invoiceData['credit'] , 2, '.', '');

    $getTransactionValues['invoiceid']  = $invoiceId;
    $transactionData                    = localAPI("gettransactions", $getTransactionValues, $adminWHMCS);
    $totalTransactions                  = $transactionData['totalresults'];
    $lastTransaction                    = end($transactionData['transactions']['transaction']);
    $whmcsTransactionId                 = (int)$lastTransaction['id'];
    $whmcsTransactionAmountin           = (double)$lastTransaction['amountin'];

    if(($invoiceStatus == 'Unpaid' || $invoiceStatus == 'Cancelled') && $whmcsTransactionAmountin == 0.00)
    {
        $amount     = $lastModificationData['value'] / 100;
        $amount     = number_format($amount, 2, '.', '');

        extra_amounts_Gerencianet_WHMCS($invoiceId, $descontoBoleto, $discountType);

        $updateTransactionCommand                  = "updatetransaction";
        $updateTransactionValues['transactionid']  = $whmcsTransactionId;
        $updateTransactionValues['description']    = "Boleto Gerencianet: Pagamento Efetuado.";
        $updateTransactionValues['date']           = $creationDate;
        $updateTransactionValues['amountin']       = (string)$amount;
        $updateTransactionresults = localAPI($updateTransactionCommand, $updateTransactionValues, $adminWHMCS);

        $gerencianetPrice = get_price($invoiceId, true) - $credit;
        $gerencianetPrice = number_format($gerencianetPrice, 2, '.', '');

        $logGerencianet = array(
            "invoiceid"     => $invoiceId,
            "charge_id"     => $transactionId,
            "created_at"    => $creationDate,
            "paid_value"    => $amount,
            "real_price"    => $gerencianetPrice
        );

        if ((double)$amount < (double)$gerencianetPrice)
        {
            if ($configDebug == "on")
                logTransaction($gatewayParams['name'],$logGerencianet,'Divergent Payment');

            die('WHMCS: Pagamento divergente - O valor pago foi menor do que o descrito no boleto.');
        }
        elseif ((double)$amount > (double)$gerencianetPrice)
        {
            $response = update_invoice_status($invoiceId, "Paid", $adminWHMCS, $isNewTransaction, $creationDate);

            if ($configDebug == "on")
                logTransaction($gatewayParams['name'],$logGerencianet,'Divergent Payment');

            die('WHMCS: Pagamento divergente - O valor pago foi maior do que o descrito no boleto.');
        }
        else
        {
            $response = update_invoice_status($invoiceId, "Paid", $adminWHMCS, $isNewTransaction, $creationDate);

            if ($configDebug == "on")
                logTransaction($gatewayParams['name'],$logGerencianet,'Payment Successful');

            die('WHMCS - Pagamento confirmado');
        }
    }
    elseif($invoiceStatus == 'Paid')
    {
        die('WHMCS: Pagamento duplicado - O cliente tentou efetuar o pagamento do mesmo boleto duas ou mais vezes');
    }
} 

elseif ($status == "canceled") 
{
    $response = update_invoice_status($invoiceId, "Cancelled", $adminWHMCS, false);
    die('WHMCS - Cobrança cancelada pelo vendedor ou pelo pagador');
}

?>