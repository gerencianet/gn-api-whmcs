<?php

require_once __DIR__ . '/../../../../init.php';
include_once __DIR__ . '../../efi/gerencianet_lib/Gerencianet_WHMCS_Interface.php';
include_once __DIR__ . '../../efi/gerencianet_lib/database_interaction.php';

App::load_function('gateway');
App::load_function('invoice');

function validateWebhook($gatewayParams)
{
    
   
  
    if ($gatewayParams['mtls'] != 'on' && isset($_GET['hmac'])) {
        $hmac = $_GET['hmac'];
        $hash = hash('sha256', $gatewayParams['clientIdProd']);
        if ($hmac != $hash) {
            header('HTTP/1.1 403 Forbidden');
            die("Não foi possível receber a notificação de pagamento do Open Finance.");
        }
    }
}

function handleWebhook($gatewayParams)
{
    
    $postData = json_decode(file_get_contents('php://input'));
    $logFile = __DIR__ . '/log.txt'; // caminho completo para o arquivo de log



        $logString = date('Y-m-d H:i:s') . ' - ' . json_encode($postData) . "\n"; // string formatada com a data atual e os dados do array em formato JSON

        // abre o arquivo em modo de escrita, acrescentando os dados ao final do arquivo, se ele já existir
        $fileHandle = fopen($logFile, 'a');
        if ($fileHandle === false) {
            die('Não foi possível abrir o arquivo de log para escrita');
        }

        // escreve os dados no arquivo e fecha o handle
        fwrite($fileHandle, $logString);
        fclose($fileHandle);

    if (isset($postData->evento) && isset($postData->data_criacao)) {
        header('HTTP/1.0 200 OK');
        exit();
    }

    $ofPaymentData = $postData;

    if (empty($ofPaymentData)) {
        showException('Exception', array('Pagamento Open Finance não recebido pelo Webhook.'));
    } else {
        header('HTTP/1.0 200 OK');

        $tableName = 'tblefiopenfinance';
        $txID = $ofPaymentData->identificadorPagamento;
        $e2eID = $ofPaymentData->endToEndId;
        $statusAceito = $ofPaymentData->status == "aceito";
        $tipoPagamento = $ofPaymentData->tipo == "pagamento";
        $success = !empty($e2eID);

        $savedInvoice = find($tableName, 'identificadorPagamento', $txID);

        if (empty($savedInvoice['e2eid']) && $tipoPagamento && $statusAceito) {
            $invoiceID = checkCbInvoiceID($savedInvoice['invoiceid'], $gatewayParams['name']);

            $conditions = [
                'invoiceid' => $invoiceID
            ];

            $dataToUpdate = [
                'e2eid' => $e2eID
            ];

            update($tableName, $conditions, $dataToUpdate);

            if ($success) {
                $paymentFee = '0.00';
                $paymentAmount = $ofPaymentData->valor;

                $result =  addInvoicePayment($invoiceID, $txID, $paymentAmount, $paymentFee, $gatewayModuleName);
                if ($result) {
                    // A função addInvoicePayment foi bem-sucedida
                    echo "Pagamento adicionado com sucesso à fatura.";
                } else {
                    // A função addInvoicePayment falhou
                    echo "Falha ao adicionar o pagamento à fatura.";
                }
            } else {
                showException('Exception', array("Pagamento Open Finance não efetuado para a Fatura #$invoiceID"));
            }
        }
    }
}

// Fetch gateway configuration parameters
$gatewayModuleName = 'efi';
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Verifying webhook and handling payment
validateWebhook($gatewayParams);
handleWebhook($gatewayParams);
