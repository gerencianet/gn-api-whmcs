<?php

require_once __DIR__ . '/../../../init.php';

include_once 'efi/gerencianet_lib/database_interaction.php';
include_once 'efi/gerencianet_lib/handler/exception_handler.php';
include_once '../efi/gerencianet_lib/Gerencianet_WHMCS_Interface.php';


App::load_function('gateway');
App::load_function('invoice');


// Fetch gateway configuration parameters
$gatewayModuleName = 'efi';
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

if (isset($_POST['notification'])) {

    $notificationToken  = $_POST['notification'];
    $clientIdProd       = $gatewayParams['clientIdProd'];
    $clientSecretProd   = $gatewayParams['clientSecretProd'];
    $clientIdSandbox        = $gatewayParams['clientIdSandbox'];
    $clientSecretSandbox    = $gatewayParams['clientSecretSandbox'];
    $sandbox      = $gatewayParams['sandbox'];
    $debug        = $gatewayParams['debug'];
    $idConta            = $gatewayParams['idConta'];
    $descontoBoleto     = (double)$gatewayParams['descontoBoleto'];
    $discountType       = $gatewayParams['tipoDesconto'];
    $adminWHMCS         = $gatewayParams['whmcsAdmin'];

    $gnIntegration = new GerencianetIntegration($clientIdProd, $clientSecretProd, $clientIdSandbox, $clientSecretSandbox, $sandbox, $idConta);
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

    if ($debug == "on" && $status != 'paid')
        logTransaction($gatewayParams['name'],$logGerencianet,$status);

    /* ************************************************************************************ */

    if ($status == "paid") 
    {
        $invoiceValues['invoiceid'] = $invoiceId;
        $invoiceData                = localAPI("getinvoice", $invoiceValues, $adminWHMCS);
        $invoiceStatus              = $invoiceData['status'];
        $credit                     = number_format((double)$invoiceData['credit'] , 2, '.', '');
        $invoiceValueTotal = number_format((double)$invoiceData['total'] , 2, '.', '');

        $getTransactionValues['invoiceid']  = $invoiceId;
        $transactionData                    = localAPI("gettransactions", $getTransactionValues, $adminWHMCS);
        $totalTransactions                  = $transactionData['totalresults'];
        $lastTransaction                    = end($transactionData['transactions']['transaction']);
        $whmcsTransactionId                 = (int)$lastTransaction['id'];
        $whmcsTransactionAmountin           = (double)$lastTransaction['amountin'];

        if(($invoiceStatus == 'Unpaid' || $invoiceStatus == 'Cancelled') && $whmcsTransactionAmountin == 0.00)
        {
           
            
            $typeCharge  = typeCharge($gnIntegration,$transactionId);
            if(!$typeCharge){
                extra_amounts_Gerencianet_WHMCS($invoiceId, $descontoBoleto, $discountType);
                $amount     = $lastModificationData['value'] / 100;
                $amount     = number_format((double)$amount, 2, '.', '');
            }else{
                if(($lastModificationData['value']/100) > $invoiceValueTotal){
                    $amount     =  $invoiceValueTotal;
                    $amount     = number_format((double)$amount, 2, '.', '');
                }else{
                    $amount     = $lastModificationData['value'] / 100;
                    $amount     = number_format((double)$amount, 2, '.', '');
                }
            }
            
            $conditionsToDeletOldTrans = array('id' => $whmcsTransactionId, 
                                    'gateway' => $gatewayModuleName,
                                    'description' => 'Efí: Cobrança aguardando pagamento.',
                                    'amountin' => 0.00,
                                    'transid' => (string)$transactionId,
                                    'invoiceid' => (int)$invoiceId);

            $deleteTrans = deleteCob('tblaccounts', $conditionsToDeletOldTrans);

            $addInvoicePaymentCommand               = "addinvoicepayment";
            $addInvoicePaymentValues["invoiceid"]   = (int)$invoiceId;
            $addInvoicePaymentValues["transid"]     = (string)$transactionId;
            $addInvoicePaymentValues["amount"]      = (string)$amount;
            $addInvoicePaymentValues["gateway"]     = "efi";

            $results = localAPI($addInvoicePaymentCommand, $addInvoicePaymentValues, $adminWHMCS);

            $gerencianetPrice = get_price($invoiceId, true) - $credit;
            $gerencianetPrice = number_format((double)$gerencianetPrice, 2, '.', '');

            $logGerencianet = array(
                "invoiceid"     => $invoiceId,
                "charge_id"     => $transactionId,
                "created_at"    => $creationDate,
                "paid_value"    => $amount,
                "real_price"    => $gerencianetPrice
            );

            if ((double)$amount < (double)$gerencianetPrice)
            {
                if ($debug == "on")
                    logTransaction($gatewayParams['name'],$logGerencianet,'Divergent Payment');

                die('WHMCS: Pagamento divergente - O valor pago foi menor do que o descrito na cobrança. ');
            }
            elseif ((double)$amount > (double)$gerencianetPrice)
            {
                $response = update_invoice_status($invoiceId, "Paid", $adminWHMCS, $isNewTransaction, $creationDate);

                if ($debug == "on")
                    logTransaction($gatewayParams['name'],$logGerencianet,'Divergent Payment');

                die('WHMCS: Pagamento divergente - O valor pago foi maior do que o descrito na cobrança.');
            }
            else
            {
                $response = update_invoice_status($invoiceId, "Paid", $adminWHMCS, $isNewTransaction, $creationDate);

                if ($debug == "on")
                    logTransaction($gatewayParams['name'],$logGerencianet,'Payment Successful');

                die('WHMCS - Pagamento confirmado');
            }
        }
        elseif($invoiceStatus == 'Paid' || $whmcsTransactionAmountin > 0.00)
        {
            die('WHMCS: Pagamento duplicado - O cliente tentou efetuar o pagamento da mesma cobrança duas ou mais vezes');
        }
        
    } 

    elseif ($status == "canceled") 
    {
        $response = update_invoice_status($invoiceId, "Cancelled", $adminWHMCS, false);
        die('WHMCS - Cobrança cancelada pelo vendedor ou pelo pagador');
    }
}else{
    
    // Hook data retrieving
    @ob_clean();
    $postData = json_decode(file_get_contents('php://input'));

    // Hook validation
    if (isset($postData->evento) && isset($postData->data_criacao)) {
        header('HTTP/1.0 200 OK');

        exit();
    }

    $pixPaymentData = $postData->pix;

    // Hook manipulation
    if (empty($pixPaymentData)) {
        showException('Exception', array('Pagamento Pix não recebido pelo Webhook.'));
    } else {
        header('HTTP/1.0 200 OK');
        $tableName = 'tblgerencianetpix';
        $txID  = $pixPaymentData[0]->txid;
        $e2eID = $pixPaymentData[0]->endToEndId;

        $success = !empty($e2eID);

        // Retrieving Invoice ID from 'tblgerencianetpix'
        $savedInvoice = find($tableName, 'txid', $txID);

        // Checking if the invoice has already been paid
        if (empty($savedInvoice['e2eid'])) {
            // Validate Callback Invoice ID
            $invoiceID = checkCbInvoiceID($savedInvoice['invoiceid'], $gatewayParams['name']);

            $conditions = [
                'invoiceid' => $invoiceID,
            ];
            $dataToUpdate = [
                'e2eid' => $e2eID,
            ];

            // Saving e2eid in table 'tblgerencianetpix'
            update($tableName, $conditions, $dataToUpdate);
            $pixDiscount = str_replace('%', '', $gatewayParams['pixDiscount']);

            if ($success) {
                $paymentFee = '0.00';
                $paymentAmount = $pixPaymentData[0]->valor;
                if($pixDiscount > 0)
                {
                    extra_amounts_Gerencianet_WHMCS($savedInvoice['invoiceid'],$pixDiscount,1);
                }
                

                addInvoicePayment($invoiceID, $txID, $paymentAmount, $paymentFee, $gatewayModuleName);
                } else {
                showException('Exception', array("Pagamento Pix não efetuado para a Fatura #$invoiceID"));
            }
        }
    }

}
function typeCharge($gnIntegration,$chargeId)
{
    $response = $gnIntegration->getPaymentMethod($chargeId);
    return ($response['data']['payment']['method'] == 'credit_card');
}


