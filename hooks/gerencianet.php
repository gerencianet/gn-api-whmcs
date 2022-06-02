<?php
define('BASE_DIR', dirname(dirname(dirname(__FILE__))) . '/');
if (!defined('WHMCS')) {
    die();
}
require_once __DIR__ . '/../../init.php';
require_once ROOTDIR . '/modules/gateways/gerencianet/gerencianet_lib/handler/exception_handler.php';
require_once ROOTDIR . '/modules/gateways/gerencianet/gerencianet_lib/api_interaction.php';
require_once ROOTDIR . '/modules/gateways/gerencianet/gerencianet_lib/functions/pix/gateway_functions.php';
include BASE_DIR . 'modules/gateways/gerencianet/gerencianet_lib/Gerencianet_WHMCS_Interface.php';
include BASE_DIR . 'modules/gateways/gerencianet/gerencianet_lib/GerencianetIntegration.php';

use WHMCS\Database\Capsule;
// defina o método de pagamento 
define('PAYMENT_METHOD', 'gerencianet');
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
    if ($status != "Paid" && $paymentmethod == "gerencianet") {

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
add_hook('AdminAreaPage', 1, function ($vars) {

    $extraVariables = [];
    $url = $_SERVER['REQUEST_URI'];

    if ($_REQUEST['updated'] == 'gerencianet') {

        if ($_SERVER['HTTPS'] !== 'on' || strpos($_SERVER['HTTP_REFERER'], 'localhost') !== false ||  strpos($_SERVER['HTTP_REFERER'], '127.0.0.1')) {

            $extraVariables['jquerycode'] = '
            $("#Payment-Gateway-Config-gerencianet")
            .prepend(`
                <div class="errorbox">
                    <strong>
                        <span class="title">Erro!</span>
                    </strong>
                    <br>
                        Identificamos que o seu domínio não possui certificado de segurança HTTPS ou 
                         não é válido para registrar o Webhook!
                    </br>    
                </div>`);
            $(".successbox").remove();
            ';
        }
    }

    $credentials    = get_admin_credentials();
    if ($vars['filename'] == 'configgateways') {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL         => "https://tls.testegerencianet.com.br",
            CURLOPT_RETURNTRANSFER         => true,
            CURLOPT_FOLLOWLOCATION         => true,
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 5,    // time-out on connect
            CURLOPT_TIMEOUT        => 5,    // time-out on response
        );
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $info = curl_getinfo($ch);
        if (($info['http_code'] !== 200) && ($content !== 'Gerencianet_Connection_TLS1.2_OK!')) {
            setcookie("tlsOk", false);
            $extraVariables['jquerycode'] = '
            let formGateway = $("#frmActivateGatway");
            formGateway.prepend(`<div class="errorbox" role="alert">
                                    <p>
                                        Identificamos que a sua hospedagem não suporta uma versão segura do TLS(Transport Layer Security) para se comunicar  
                                        com a Gerencianet. Para conseguir gerar transações, será necessário que contate o administrador do seu servidor e solicite que 
                                        a hospedagem seja atualizada para suportar co municações por meio do TLS na versão mínima 1.2. 
                                        Em caso de dúvidas e para maiores informações, contat e  a Equi pe Técnica da Gerencia net at ravés do suporte da empresa.
                                    </p>    
                                </div>`);
        ';
        } else {
            setcookie("tlsOk", true);
            if (isset($_COOKIE["gnTestTlsLog"])) {
                setcookie("gnTestTlsLog", false, time() - 1);
            }
        }
        curl_close($ch);

        if (!$_COOKIE["tlsOk"] && !isset($_COOKIE["gnTestTlsLog"]) && ($credentials['idConta'] != null || $credentials['idConta'] != '')) {
            setcookie("gnTestTlsLog", true);
            // register log
            $account = $credentials['idConta'];
            $ip = $_SERVER['SERVER_ADDR'];
            $modulo = 'WHMCS';
            $control = md5($account . $ip . 'modulologs-tls');
            $dataPost = array(
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'modulo' => $modulo,
            );
            $post = array(
                'control' => $control,
                'account' => $account,
                'ip' => $ip,
                'origin' => 'modulo',
                'data' => json_encode($dataPost)
            );
            $ch1 = curl_init();
            $options1 = array(
                CURLOPT_URL         => "https://fortunus.gerencianet.com.br/logs/tls",
                CURLOPT_RETURNTRANSFER         => true,
                CURLOPT_FOLLOWLOCATION         => true,
                CURLOPT_HEADER         => true,  // don't return headers
                CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
                CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
                CURLOPT_CONNECTTIMEOUT => 5,    // time-out on connect
                CURLOPT_TIMEOUT        => 5,    // time-out on response
                CURLOPT_POST        => true,
                CURLOPT_POSTFIELDS        => json_encode($post),
            );
            curl_setopt_array($ch1, $options1);
            $content1 = curl_exec($ch1);
            $info1 = curl_getinfo($ch1);
            curl_close($ch1);
        }
    }

    return $extraVariables;
});
