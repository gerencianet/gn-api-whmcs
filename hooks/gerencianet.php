<?php
define('BASE_DIR', dirname( dirname( dirname( __FILE__ ) ) ) . '/');

include BASE_DIR . 'modules/gateways/gerencianet_lib/Gerencianet_WHMCS_Interface.php';
include BASE_DIR . 'modules/gateways/gerencianet_lib/GerencianetIntegration.php';

/**
 * Gerencianet Hook Calls
 *
 * @author     Filipe Mata
 */

function getChargeId($vars, $credentials)
{
    $invoiceid          = $vars['invoiceid'];
    $clientIDProd       = $credentials['clientIDProd'];
    $clientSecretProd   = $credentials['clientSecretProd']; 
    $clientIDDev        = $credentials['clientIDDev'];
    $clientSecretDev    = $credentials['clientSecretDev'];
    $adminWHMCS         = $credentials['whmcsAdmin'];
    $idConta            = $credentials['idConta'];
    $configSandbox      = false;
    if($credentials['configSandbox'] == "on")
        $configSandbox  = true;

    $getTransactionValues['invoiceid']  = $invoiceid;
    $transactionData                    = localAPI("gettransactions", $getTransactionValues, $adminWHMCS);
    $totalTransactions                  = $transactionData['totalresults'];

    $gnIntegration = new GerencianetIntegration($clientIDProd, $clientSecretProd, $clientIDDev, $clientSecretDev, $configSandbox, $idConta);
    $finalChargeId = 0;
    $expire_at = '';

    if ($totalTransactions > 0)
    {
        $lastTransaction   = end($transactionData['transactions']['transaction']);
        $transactionId     = (string)$lastTransaction['id'];
        $chargeId          = (int)$lastTransaction['transid'];

        $chargeDetailsJson = $gnIntegration->detail_charge($chargeId);
        $chargeDetails     = json_decode($chargeDetailsJson, true);

        if($chargeDetails['code'] == 200)
        {
            $customId = $chargeDetails['data']['custom_id'];
            if((int)$customId == (int)$invoiceid){
                $finalChargeId = (int)$chargeId;
            }

            if(isset($chargeDetails['data']['payment']['banking_billet']['expire_at']))
                $expire_at = $chargeDetails['data']['payment']['banking_billet']['expire_at'];
        }
    }

    return array('gn_object' => $gnIntegration, 'charge_id' => $finalChargeId, 'expire_at' => $expire_at);
}

function gerencianetCancelCharge($vars)
{
    $credentials    = get_admin_credentials();
    $response       = getChargeId($vars, $credentials);
    $gnIntegration  = $response['gn_object'];
    $chargeId       = (int)$response['charge_id'];

    if($chargeId != 0)
        $gnIntegration->cancel_charge($chargeId);

}

function gerencianetUpdateBillet($vars)
{
    $invoiceid                  = $vars['invoiceid'];
    $invoiceValues['invoiceid'] = $invoiceid;
    $invoiceData                = localAPI("getinvoice", $invoiceValues, $adminWHMCS);
    $dueDate                    = $invoiceData['duedate'];
    $status                     = $invoiceData['status'];
    $paymentmethod              = $invoiceData['paymentmethod'];

    if($status != "Paid" && $paymentmethod == "gerencianetcharge")
    {
        $credentials    = get_admin_credentials();
        $numDiasParaVencimento = $credentials['numDiasParaVencimento'];

        $date = DateTime::createFromFormat('Y-m-d', $dueDate);
        if((int)$numDiasParaVencimento > 0)
            $date->add(new DateInterval('P' . (string)$numDiasParaVencimento . 'D'));
        $newDueDate = (string)$date->format('Y-m-d'); 

        if ($newDueDate >= date('Y-m-d'))
        {
            $response       = getChargeId($vars, $credentials);
            $gnIntegration  = $response['gn_object'];
            $chargeId       = (int)$response['charge_id'];
            $expireAt       = $response['expire_at'];

            if($chargeId != 0 && $newDueDate != $expireAt)
            {
                $gnIntegration->update_billet($chargeId, $newDueDate);
            }
        }    
    }
}

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

add_hook('InvoiceCancelled', 1, gerencianetCancelCharge);
add_hook('UpdateInvoiceTotal', 2, gerencianetUpdateBillet);