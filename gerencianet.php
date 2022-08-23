<?php
require 'gerencianet/gerencianet-sdk/autoload.php';
include_once 'gerencianet/gerencianet_lib/api_interaction.php';
include_once 'gerencianet/gerencianet_lib/database_interaction.php';
include_once 'gerencianet/gerencianet_lib/handler/exception_handler.php';
include_once 'gerencianet/gerencianet_lib/functions/pix/gateway_functions.php';
include_once 'gerencianet/gerencianet_lib/GerencianetValidation.php';
include_once 'gerencianet/gerencianet_lib/GerencianetIntegration.php';
include_once 'gerencianet/gerencianet_lib/functions/boleto/gateway_functions.php';
include_once 'gerencianet/gerencianet_lib/functions/cartao/gateway_functions.php';
include_once 'gerencianet/gerencianet_lib/Gerencianet_WHMCS_Interface.php';
include_once 'gerencianet/gerencianet_lib/functions/viewAutoComplete/viewAutoComplete.php';

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @see https://developers.whmcs.com/payment-gateways/configuration/
 *
 * @return array
 */
function gerencianet_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Gerencianet'
        ),
        'gn_style' => array(
            'Description' => '
            <style type="text/css">
                .icon-required{
                    font-size:0.6em;
                    vertical-align: middle;
                    color: red;
                    margin-right: 4px;
                    
                }
                .icon-weight{
                    font-weight: normal;
                }
            </style>

            '
        ),

        'clientIdProd' => array(
            'FriendlyName' => '<i  data-toggle="tooltip" data-placement="top" title="Obrigatório" class="fas icon-required icon-weight fa-xs fa-asterisk"></i>Client_Id de Produção ',
            'Type' => 'text',
            'Size' => '250',
            'Default' => '',
            'Description' => '',
        ),
        'clientSecretProd' => array(
            'FriendlyName' => '<i  data-toggle="tooltip" data-placement="top" title="Obrigatório" class="fas icon-required icon-weight fa-xs fa-asterisk"></i>Client_Secret de Produção ',
            'Type' => 'text',
            'Size' => '250',
            'Default' => '',
            'Description' => '',
        ),
        'clientIdSandbox' => array(
            'FriendlyName' => '<i  data-toggle="tooltip" data-placement="top" title="Obrigatório" class="fas icon-required icon-weight fa-xs fa-asterisk"></i>Client_Id de Sandbox  ',
            'Type' => 'text',
            'Size' => '250',
            'Default' => '',
            'Description' => '',
        ),
        'clientSecretSandbox' => array(
            'FriendlyName' => '<i  data-toggle="tooltip" data-placement="top" title="Obrigatório" class="fas icon-required icon-weight fa-xs fa-asterisk"></i> Client_Secret de Sandbox ',
            'Type' => 'text',
            'Size' => '250',
            'Default' => '',
            'Description' => '',
        ),
        'idConta'           => array(
            'FriendlyName'  => '<i  data-toggle="tooltip" data-placement="top" title="Obrigatório" class="fas icon-required icon-weight fa-xs fa-asterisk"></i> Identificador da Conta ',
            'Type'          => 'text',
            'Size'          => '32',
            'Description'   => '',
        ),

        'whmcsAdmin'    => array(
            'FriendlyName'  => '<i  data-toggle="tooltip" data-placement="top" title="Obrigatório" class="fas icon-required icon-weight fa-xs fa-asterisk"></i> Usuário administrador do WHMCS ',
            'Type'          => 'text',
            'Description'   => 'Insira o nome do usuário administrador do WHMCS.',
            'Description'   => '',
        ),
        'descontoBoleto'    => array(
            'FriendlyName'  => 'Desconto do Boleto <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Desconto para pagamentos no boleto bancário."></i>',
            'Type'          => 'text',
            'Description'   => '',
        ),
        'tipoDesconto'      => array(
            'FriendlyName'  => 'Tipo de desconto <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Escolha a forma do desconto: Porcentagem ou em Reais"></i>',
            'Type'          => 'dropdown',
            'Options'       => array(
                '1'         => '% (Porcentagem)',
                '2'         => 'R$ (Reais)',
            ),
            'Description'   => '',
        ),
        'numDiasParaVencimento' => array(
            'FriendlyName'      => 'Número de dias para o vencimento do boleto <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Número de dias corridos para o vencimento do boleto  depois  de sua criação"></i>',
            'Type'              => 'text',
            'Description'       => '',
        ),
        'sendEmailGN'       => array(
            'FriendlyName'  => 'Email de cobraça - Gerencianet',
            'Type'          => 'yesno',
            'Description'   => 'Marque esta opção se você deseja que a Gerencianet envie emails de transações para o cliente final',
        ),
        'fineValue'         => array(
            'FriendlyName'  => 'Configuração de Multa <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Valor da multa se pago após o vencimento - informe em porcentagem (mínimo 0,01% e máximo 10%)."></i>',
            'Type'          => 'text',
            'Description'   => '',
        ),
        'interestValue'         => array(
            'FriendlyName'  => 'Configuração de Juros <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Valor de juros por dia se pago após o vencimento - informe em porcentagem (mínimo 0,001% e máximo 0,33%)."></i>',
            'Type'          => 'text',
            'Description'   => '',
        ),
        'message'      => array(
            'FriendlyName'  => 'Observação <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Permite incluir no boleto uma mensagem para o cliente (máximo de 80 caracteres)."></i>',
            'Type'          => 'text',
            'Size'          => '80',
            'Description'   => '',
        ),
        'sandbox' => array(
            'FriendlyName' => 'Sandbox',
            'Type' => 'yesno',
            'Description' => 'Habilita o modo Sandbox da Gerencianet',
        ),
        'debug' => array(
            'FriendlyName' => 'Debug',
            'Type' => 'yesno',
            'Description' => 'Habilita o modo Debug',
        ),
        'pixKey' => array(
            'FriendlyName' => 'Chave Pix <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Insira sua chave Pix padrão para recebimentos"></i>',
            'Type' => 'text',
            'Size' => '250',
            'Default' => '',
            'Description' => '',
        ),
        'pixCert' => array(
            'FriendlyName' => 'Certificado Pix <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Insira o caminho do seu certificado .pem"></i>',
            'Type' => 'text',
            'Size' => '350',
            'Default' => '/var/certs/cert.pem',
            'Description' => '',
        ),
        'pixDiscount' => array(
            'FriendlyName' => 'Desconto do Pix (%) <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Preencha um valor caso queira dar um desconto para pagamentos via Pix"></i>',
            'Type' => 'text',
            'Size' => '3',
            'Default' => '0%',
            'Description' => '',
        ),
        'pixDays' => array(
            'FriendlyName' => 'Validade da Cobrança em Dias <i class="fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Tempo em dias de validade da cobrança"></i>',
            'Type' => 'text',
            'Size' => '3',
            'Default' => '1',
            'Description' => '',
        ),
        'mtls' => array(
            'FriendlyName' => 'Validar mTLS',
            'Type' => 'yesno',
            'Default' => true,
            'Description' => 'Entenda os riscos de não configurar o mTLS acessando o link https://gnetbr.com/rke4baDVyd',
        ),
        'activePix'       => array(
            'FriendlyName'  => 'Pix',
            'Type'          => 'yesno',
            'Description'   => 'Ativar PIX como forma de pagamento',
        ),
        'activeBoleto'       => array(
            'FriendlyName'  => 'Boleto',
            'Type'          => 'yesno',
            'Description'   => 'Ativar boleto como forma de pagamento',
        ),
        'activeCredit'       => array(
            'FriendlyName'  => 'Cartão de Credito',
            'Type'          => 'yesno',
            'Description'   => 'Ativar cartão de crédito como forma de pagamento',
        ),
    );
}

function gerencianet_config_validate($params)
{

    if ($params['clientIdProd'] == '') {
        throw new \Exception('O campo Client_id produção não foi preenchido');
    }
    if ($params['clientSecretProd'] == '') {
        throw new \Exception('O campo Client_secret produção não foi preenchido');
    }
    if ($params['clientIdSandbox'] == '') {
        throw new \Exception('O campo Client_id sandbox não foi preenchido');
    }
    if ($params['clientSecretSandbox'] == '') {
        throw new \Exception('O campo Client_secret sandbox não foi preenchido');
    }
    if ($params['idConta'] == '') {
        throw new \Exception('O campo identificador da conta não foi preenchido');
    }
    if ($params['whmcsAdmin'] == '') {
        throw new \Exception('O campo administrador do whmcs  não foi preenchido');
    }
    if ($params['activeBoleto'] != 'on' && $params['activeCredit'] != 'on' && $params['activePix'] != 'on') {
        throw new \Exception('Nenhuma forma de pagamento ativa!');
    }
    if ($params['activeBoleto'] == 'on') {
        if ($params['descontoBoleto'] != '') {
            $desconto = str_replace(',', '.', $params['descontoBoleto']);
            if (!is_numeric($desconto)) {
                throw new \Exception('O campo desconto do boleto  não foi preenchido corretamente');
            }
        }

        if ($params['numDiasParaVencimento'] == '' || !(is_numeric($params['numDiasParaVencimento']))) {
            throw new \Exception('O campo dias para vencimento boleto  não foi preenchido corretamente');
        }

        if ($params['fineValue'] != '') {
            $multa = str_replace(',', '.', $params['fineValue']);
            if (!is_numeric($multa)) {
                throw new \Exception('O campo multa  não foi preenchido corretamente');
            }
        }
        if ($params['interestValue'] != '') {
            $juros = str_replace(',', '.', $params['interestValue']);
            if (!is_numeric($juros)) {
                throw new \Exception('O campo juros  não foi preenchido corretamente');
            }
        }
    }

    if ($params['activePix'] == 'on') {
        $chave_pix = $params['pixKey'];

        $patternCPF = '/^[0-9]{11}$/';
        $patternCNPJ = '/^[0-9]{14}$/';
        $patternPHONE = '/^\+[1-9][0-9]\d{1,14}$/';
        $patternEMAIL = '/^[a-z0-9.]+@[a-z0-9]+\.[a-z]+\.([a-z]+)?$/i';
        $patterEVP = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/";

        if (!preg_match($patternCPF, $chave_pix) && !preg_match($patternCNPJ, $chave_pix) && !preg_match($patternPHONE, $chave_pix) && !preg_match($patternEMAIL, $chave_pix) && !preg_match($patterEVP, $chave_pix)) {
            throw new \Exception('Chave PIX inválida');
        }

        if ($params['pixKey'] == '') {
            throw new \Exception('O campo chave Pix  não foi preenchido corretamente');
        }

        if ($params['pixCert'] == '' || !(file_exists($params['pixCert']))) {
            $pathCert = $params['pixCert'];
            $fullPath = __DIR__;
            $message = "Não foi possível encontrar o certificado no caminho informado: <br>  <strong>'$pathCert'</strong> <br><br>
                Insira o caminho completo do arquivo, como no exemplo abaixo:<br>
                <strong> '$fullPath'</strong>
                <br>
            ";
            throw new \Exception($message);
        }

        if ($params['pixDiscount'] != '') {
            $descontoPix =  str_replace(',', '.', str_replace('%', '', $params['pixDiscount']));
            if (!is_numeric($descontoPix)) {
                throw new \Exception('O campo desconto Pix  não foi preenchido corretamente');
            }
        }

        if ($params['pixDays'] == '') {
            throw new \Exception('O campo validade  da cobrança Pix  não foi preenchido corretamente');
        }
        if ($params['sandbox'] != 'on') {
            try {
                $gn_instance = getGerencianetApiInstance($params);
                createWebhook($gn_instance, $params);
            } catch (\Throwable $th) {
                throw new \Exception($th->getMessage());
            }
        }
    }

    try {
        $gnIntegration = new GerencianetIntegration($params['clientIdProd'], $params['clientSecretProd'], $params['clientIdSandbox'], $params['clientSecretSandbox'], $params['sandbox'], $params['idConta']);

        $gnIntegration->testIntegration();
    } catch (\Throwable $th) {
        throw new \Exception("<strong>Credenciais inválidas</strong>. Por favor, verifique se as suas credencias estão corretas e tente novamente.");
    }
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $gatewayParams Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function gerencianet_link($gatewayParams)
{
    // Creating table 'tblgerencianetpix'
    createGerencianetPixTable();

    $baseUrl = $gatewayParams['systemurl'];



    $identificadorDaConta = $gatewayParams['idConta'];
    $autoCompleteFields = generateAutoCompleteFields($gatewayParams);
    $autoCompletetotal = generateAutoCompleteTotal($gatewayParams);




    $scriptPaymentTokenProducao = "<script type='text/javascript'>  var s=document.createElement('script');s.type='text/javascript';var v=parseInt(Math.random()*1000000);s.src='https://api.gerencianet.com.br/v1/cdn/$identificadorDaConta/'+v;s.async=false;s.id='$identificadorDaConta';if(!document.getElementById('$identificadorDaConta')){document.getElementsByTagName('head')[0].appendChild(s);};\$gn={validForm:true,processed:false,done:{},ready:function(fn){\$gn.done=fn;}};</script>";
    $scriptPaymentTokenSandbox = "<script type='text/javascript'>  var s=document.createElement('script');s.type='text/javascript';var v=parseInt(Math.random()*1000000);s.src='https://sandbox.gerencianet.com.br/v1/cdn/$identificadorDaConta/'+v;s.async=false;s.id='$identificadorDaConta';if(!document.getElementById('$identificadorDaConta')){document.getElementsByTagName('head')[0].appendChild(s);};\$gn={validForm:true,processed:false,done:{},ready:function(fn){\$gn.done=fn;}};</script>";
    $scriptGetPaymentToken = ($gatewayParams['sandbox'] == 'on') ? $scriptPaymentTokenSandbox : $scriptPaymentTokenProducao;


    $paymentOptionsScript = "<div id='modal_content'></div>
    $scriptGetPaymentToken
    <script type=\"text/javascript\" src=\"$baseUrl/modules/gateways/gerencianet/gerencianet_lib/scripts/js/viewInvoiceModal.js\"></script>
    <script type=\"text/javascript\" src=\"$baseUrl/modules/gateways/gerencianet/gerencianet_lib/scripts/js/jquery-mask/jquery.mask.min.js\"></script>
    <script type=\"text/javascript\" src=\"$baseUrl/modules/gateways/gerencianet/gerencianet_lib/scripts/js/validation/validation.js\"></script>
    $autoCompleteFields
    <script type=\"text/javascript\" src=\"$baseUrl/modules/gateways/gerencianet/gerencianet_lib/scripts/js/autoComplete/autoComplete.js\"></script>
    $autoCompletetotal
    ";

    $existingPixCharge = getPixCharge($gatewayParams['invoiceid']);

    if (!empty($existingPixCharge)) {
        $api_instance = getGerencianetApiInstance($gatewayParams);
        $locId =  $existingPixCharge['locid'];
        return createQRCode($api_instance, $locId);
    } else {
        $gnIntegration = new GerencianetIntegration($gatewayParams['clientIdProd'], $gatewayParams['clientSecretProd'], $gatewayParams['clientIdSandbox'], $gatewayParams['clientSecretSandbox'], $gatewayParams['sandbox'], $gatewayParams['idConta']);
        $existingChargeConfirm = existingCharge($gatewayParams, $gnIntegration);
        $existingCharge = $existingChargeConfirm['existCharge'];
        $code = $existingChargeConfirm['code'];
        if ($existingCharge) {
            return $code;
        } else {
            $existingChargeConfirm = existingChargeCredit($gatewayParams, $gnIntegration);
            $existingCharge = $existingChargeConfirm['existCharge'];
            $code = $existingChargeConfirm['code'];
            if ($existingCharge) {
                return $code;
            }
        }
    }


    if (!isset($_POST['optionPayment']) || $_POST['optionPayment'] == '') {
       
        return $paymentOptionsScript;
    } else {
       
        if ($_POST['optionPayment'] == 'pix') {
            $gatewayParams['paramsPix'] = $_POST;

            validateRequiredParams($gatewayParams);

            // Getting API Instance
            $api_instance = getGerencianetApiInstance($gatewayParams);



            // Verifying if exists a Pix Charge for current invoiceId
            $existingPixCharge = getPixCharge($gatewayParams['invoiceid']);

            if (empty($existingPixCharge)) {
                // Creating a new Pix Charge
                $newPixCharge = createPixCharge($api_instance, $gatewayParams);

                if (isset($newPixCharge['txid'])) {
                    // Storing Pix Charge Infos on table 'tblgerencianetpix' for later use
                    storePixChargeInfo($newPixCharge, $gatewayParams);
                }
            }

            // Generating QR Code
            $locId = $existingPixCharge ? $existingPixCharge['locid'] : $newPixCharge['loc']['id'];
            return createQRCode($api_instance, $locId);
        } elseif ($_POST['optionPayment'] == 'boleto') {
            $gatewayParams['paramsBoleto'] = $_POST;
            $errorMessages = array();
            $errorMessages = validationParams($gatewayParams);
            /* **************************************** Verifica se a versão do PHP é compatível com a API ******************************** */

            if (version_compare(PHP_VERSION, '7.3') < 0) {
                $errorMsg = 'A versão do PHP do servidor onde o WHMCS está hospedado não é compatível com o módulo Gerencianet. Atualize o PHP para uma versão igual ou superior à versão 5.4.39';
                if ($gatewayParams['debug'] == "on")
                    logTransaction('gerencianet', $errorMsg, 'Erro de Versão');

                return send_errors(array('Erro Inesperado: Ocorreu um erro inesperado. Entre em contato com o responsável do site.'));
            }
            /* ************************************************ Define mensagens de erro ***************************************************/





            /* ******************************************** Gateway Configuration Parameters ******************************************* */
            $descontoBoleto         = $gatewayParams['descontoBoleto'];
            $tipoDesconto           = $gatewayParams['tipoDesconto'];
            $minValue               = 5; //Valor mínimo de emissão de boleto na Gerencianet
            $debug            = $gatewayParams['debug'];
            $adminWHMCS             = $gatewayParams['whmcsAdmin'];
            if ($adminWHMCS == '' || $adminWHMCS == null) {
                array_push($errorMessages, INTEGRATION_ERROR_MESSAGE);
                if ($debug == "on")
                    logTransaction('gerencianet', 'O campo - Usuario administrador do WHMCS - está preenchido incorretamente', 'Erro de Integração');
                return send_errors($errorMessages);
            }

            /* ***************************** Verifica se já existe um boleto para o pedido em questão *********************************** */

            $gnIntegration = new GerencianetIntegration($gatewayParams['clientIdProd'], $gatewayParams['clientSecretProd'], $gatewayParams['clientIdSandbox'], $gatewayParams['clientSecretSandbox'], $gatewayParams['sandbox'], $gatewayParams['idConta']);
            $existingChargeConfirm = existingCharge($gatewayParams, $gnIntegration);
            $existingCharge = $existingChargeConfirm['existCharge'];
            $code = $existingChargeConfirm['code'];
            if ($existingCharge) {
                return $code;
            }
            $invoiceAmount              = $gatewayParams['amount'];
            if ($invoiceAmount < $minValue) {
                $limitMsg = "<div id=limit-value-msg style='font-weight:bold; color:#cc0000;'>Transação Não permitida: Você está tentando pagar uma fatura de<br> R$ $invoiceAmount. Para gerar o boleto Gerencianet, o valor mínimo do pedido deve ser de R$ $minValue</div>";
                return $limitMsg;
            }
            /* ************************************************* Invoice parameters *************************************************** */
            $linkPagamento = createBillet($gatewayParams, $gnIntegration, $errorMessages, $existingCharge);
            if (strpos($linkPagamento, 'modules/gateways/gerencianet/gerencianet_lib/gerencianet_errors.php')) {
                return ($linkPagamento);
            }
            return buttonGerencianet($errorMessages, $linkPagamento, $descontoBoleto, $tipoDesconto);
        } else {
            $gatewayParams['paramsCartao'] = $_POST;

            $errorMessages = array();
            $errorMessages = validationParamsCard($gatewayParams);
            $gnIntegration = new GerencianetIntegration($gatewayParams['clientIdProd'], $gatewayParams['clientSecretProd'], $gatewayParams['clientIdSandbox'], $gatewayParams['clientSecretSandbox'], $gatewayParams['sandbox'], $gatewayParams['idConta']);
            $existingChargeConfirm = existingChargeCredit($gatewayParams, $gnIntegration);
            $existingCharge = $existingChargeConfirm['existCharge'];
            $code = $existingChargeConfirm['code'];
            if ($existingCharge) {
                return $code;
            }

            if (version_compare(PHP_VERSION, '7.3') < 0) {
                $errorMsg = 'A versão do PHP do servidor onde o WHMCS está hospedado não é compatível com o módulo Gerencianet. Atualize o PHP para uma versão igual ou superior à versão 5.4.39';
                if ($gatewayParams['debug'] == "on")
                    logTransaction('gerencianet', $errorMsg, 'Erro de Versão');

                return send_errors(array('Erro Inesperado: Ocorreu um erro inesperado. Entre em contato com o responsável do site.'));
            }
            $invoiceAmount              = $gatewayParams['amount'];
            if ($invoiceAmount < $minValue) {
                $limitMsg = "<div id=limit-value-msg style='font-weight:bold; color:#cc0000;'>Transação Não permitida: Você está tentando pagar uma fatura de<br> R$ $invoiceAmount. Para gerar o boleto Gerencianet, o valor mínimo do pedido deve ser de R$ $minValue</div>";
                return $limitMsg;
            }
            return createCard($gatewayParams, $gnIntegration, $errorMessages, $existingCharge);
        }
    }
}

/**
 * Refund transaction
 *
 * Called when a refund is requested for a previously successful transaction
 *
 * @param array $gatewayParams Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function gerencianet_refund($gatewayParams)
{
    //  Validating if required parameters are empty
    validateRequiredParams($gatewayParams);

    // Getting API Instance
    $api_instance = getGerencianetApiInstance($gatewayParams);

    // Refunding Pix Charge
    $responseData = refundCharge($api_instance, $gatewayParams);

    return array(
        'status' => $responseData['rtrId'] ? 'success' : 'error',
        'rawdata' => $responseData,
        'transid' => $responseData['rtrId'] ? $responseData['rtrId'] : 'Not Refunded',
    );
}
