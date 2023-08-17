<?php

require_once __DIR__ . '/../../../../init.php';


include_once __DIR__ . '../../efi/gerencianet_lib/Gerencianet_WHMCS_Interface.php';
include_once __DIR__ . '../../efi/gerencianet_lib/GerencianetIntegration.php';





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

    $paymentFee = $gatewayParams['tarifaBoleto'];



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

                extra_amounts_Gerencianet_WHMCS($invoiceId, $descontoBoleto, $discountType);

                // $amount     = $lastModificationData['value'] / 100;

                // $amount     = number_format((double)$amount, 2, '.', '');

                $amount = '5.00';


          

            

            $conditionsToDeletOldTrans = array('id' => $whmcsTransactionId, 

                                    'gateway' => $gatewayModuleName,

                                    'description' => 'Efí: Cobrança aguardando pagamento.',

                                    'amountin' => 0.00,

                                    'transid' => (string)$transactionId,

                                    'invoiceid' => (int)$invoiceId);



            $deleteTrans = deleteCob('tblaccounts', $conditionsToDeletOldTrans);


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
                addInvoicePayment($invoiceId, $transactionId, $amount, $paymentFee, $gatewayModuleName);
                if ($debug == "on")
                    logTransaction($gatewayParams['name'],$logGerencianet,'Divergent Payment');

                die('WHMCS: Pagamento divergente - O valor pago foi maior do que o descrito na cobrança.');

            }

            else

            {

                $resultado =  addInvoicePayment($invoiceId, $transactionId, $amount, $paymentFee, $gatewayModuleName);
                $values = [
                    "invoiceID"=>$invoiceID,
                    "transactionId"=>$transactionId,
                    "amount"=>$amount,
                    "paymentFee"=>$paymentFee,
                    "gatewayModuleName"=>$gatewayModuleName
                ];

                $caminhoLog = __DIR__ . '/log.txt'; // 'log.txt' é o nome do arquivo de log

                // Abra o arquivo de log em modo de escrita
                $arquivoLog = fopen($caminhoLog, 'a');

                // Verifique se o arquivo de log foi aberto com sucesso
                if ($arquivoLog) {
                    // Obtenha a data e hora atual para registro no log
                    $dataHora = date('Y-m-d H:i:s');

                    // Crie a mensagem a ser registrada no log
                    $mensagem = "Data/Hora: $dataHora - Retorno da função addInvoicePayment: " . ($resultado ? 'Sucesso' : 'Erro')  . "\n" . json_encode($values) ;

                    // Escreva a mensagem no arquivo de log
                    fwrite($arquivoLog, $mensagem);

                    // Feche o arquivo de log
                    fclose($arquivoLog);
                } else {
                    // Lidere com erros na abertura do arquivo de log
                    echo 'Erro ao abrir o arquivo de log.';
                }

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

}






