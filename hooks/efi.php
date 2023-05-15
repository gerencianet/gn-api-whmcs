<?php
define('BASE_DIR', dirname(dirname(dirname(__FILE__))) . '/');
if (!defined('WHMCS')) {
    die();
}
require_once __DIR__ . '/../../init.php';
require_once ROOTDIR . '/modules/gateways/efi/gerencianet_lib/handler/exception_handler.php';
require_once ROOTDIR . '/modules/gateways/efi/gerencianet_lib/api_interaction.php';
include BASE_DIR . 'modules/gateways/efi/gerencianet_lib/Gerencianet_WHMCS_Interface.php';
include BASE_DIR . 'modules/gateways/efi/gerencianet_lib/GerencianetIntegration.php';

use WHMCS\Database\Capsule;
// defina o método de pagamento 
define('PAYMENT_METHOD', 'efi');

function getChargeId($vars, $credentials)
{
    
    $invoiceid          = $vars['invoiceid'];
    $clientIdProd       = $credentials['clientIdProd'];
    $clientSecretProd   = $credentials['clientSecretProd'];
    $clientIdSandbox        = $credentials['clientIdSandbox'];
    $clientSecretSandbox    = $credentials['clientSecretSandbox'];
    $adminWHMCS         = $credentials['whmcsAdmin'];
    $idConta            = $credentials['idConta'];
    $configSandbox      = $credentials['sandbox'];
   

    $getTransactionValues['invoiceid']  = $invoiceid;
    $transactionData                    = localAPI("gettransactions", $getTransactionValues, $adminWHMCS);
    $totalTransactions                  = $transactionData['totalresults'];
    $gnIntegration = new GerencianetIntegration($clientIdProd, $clientSecretProd, $clientIdSandbox, $clientSecretSandbox, $configSandbox, $idConta);
    $finalChargeId = 0;
    $expire_at = '';
    if ($totalTransactions > 0) {
        $lastTransaction   = end($transactionData['transactions']['transaction']);
        $transactionId     = (string)$lastTransaction['id'];
        $chargeId          = (int)$lastTransaction['transid'];
        $chargeDetailsJson = $gnIntegration->detail_charge($chargeId);
        $chargeDetails     = json_decode($chargeDetailsJson, true);
        if ($chargeDetails['code'] == 200) {
            $customId = $chargeDetails['data']['custom_id'];
            if ((int)$customId == (int)$invoiceid) {
                $finalChargeId = (int)$chargeId;
            }
            if (isset($chargeDetails['data']['payment']['banking_billet']['expire_at']))
                $expire_at = $chargeDetails['data']['payment']['banking_billet']['expire_at'];
        }
    }
    return array('gn_object' => $gnIntegration, 'charge_id' => $finalChargeId, 'expire_at' => $expire_at);
}
function gerencianetCancelCharge($vars)
{

    $credentials    = getGatewayVariables(PAYMENT_METHOD);
    $response       = getChargeId($vars, $credentials);
    $gnIntegration  = $response['gn_object'];
    $chargeId       = (int)$response['charge_id'];
    if ($chargeId != 0)
        $gnIntegration->cancel_charge($chargeId);
}

function gerencianetUpdateBillet($vars)
{

    $paramsGateway = getGatewayVariables(PAYMENT_METHOD);
    $invoiceid                  = $vars['invoiceid'];
    $invoiceValues['invoiceid'] = $invoiceid;
    $adminWHMCS         = $paramsGateway['whmcsAdmin'];
    $invoiceData                = localAPI("getinvoice", $invoiceValues, $adminWHMCS);

    $dueDate                    = $invoiceData['duedate'];
    $status                     = $invoiceData['status'];
    $paymentmethod              = $invoiceData['paymentmethod'];
    if ($status != "Paid" && $paymentmethod == "efi") {

        $numDiasParaVencimento = (string) $paramsGateway['numDiasParaVencimento'];
        $date = DateTime::createFromFormat('Y-m-d', $dueDate);
        $diasFormatado = 'P' . $numDiasParaVencimento . 'D';
        if ((int)$numDiasParaVencimento > 0) {
            $date->add(new DateInterval($diasFormatado));
        }
        $newDueDate = (string)$date->format('Y-m-d');
        if ($newDueDate >= date('Y-m-d')) {
            $response       = getChargeId($vars, $paramsGateway);
            $gnIntegration  = $response['gn_object'];
            $chargeId       = (int)$response['charge_id'];
            $expireAt       = $response['expire_at'];

            if ($chargeId != 0 && $newDueDate != $expireAt) {
                $gnIntegration->update_billet($chargeId, $newDueDate);
            }
        }
    }
}
add_hook('InvoiceCancelled', 1, "gerencianetCancelCharge");
add_hook('UpdateInvoiceTotal', 1, "gerencianetUpdateBillet");
