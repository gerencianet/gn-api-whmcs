<?php
require 'efi/gerencianet-sdk/autoload.php';
include_once 'efi/gerencianet_lib/api_interaction.php';
include_once 'efi/gerencianet_lib/database_interaction.php';
include_once 'efi/gerencianet_lib/handler/exception_handler.php';
include_once 'efi/gerencianet_lib/GerencianetValidation.php';
include_once 'efi/gerencianet_lib/GerencianetIntegration.php';
include_once 'efi/gerencianet_lib/functions/gateway/Billet.php';
include_once 'efi/gerencianet_lib/functions/gateway/CreditCard.php';
include_once 'efi/gerencianet_lib/functions/gateway/PIX.php';
include_once 'efi/gerencianet_lib/Gerencianet_WHMCS_Interface.php';
include_once 'efi/gerencianet_lib/functions/viewAutoComplete/viewAutoComplete.php';
include_once 'efi/gerencianet_lib/functions/admin/ValidationAdminFileds.php';


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
function efi_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Efí'
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
            'FriendlyName' => '<i   data-toggle="tooltip" data-placement="top" title="Obrigatório" class="test_toggle fas icon-required icon-weight fa-xs fa-asterisk"></i>Client_Id de Produção ',
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
        'activeBoleto'       => array(
            'FriendlyName'  => 'Boleto',
            'Type'          => 'yesno',
            'Description'   => '<span id="billet">Ativar boleto como forma de pagamento</span>',
        ),
        'tipoDesconto'      => array(
            'FriendlyName'  => 'Tipo de desconto <i class="toggle_billet fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Escolha a forma do desconto: Porcentagem ou em Reais"></i>',
            'Type'          => 'dropdown',
            'Options'       => array(
                '1'         => '% (Porcentagem)',
                '2'         => 'R$ (Reais)',
            ),
            'Description'   => '',
        ),
        'descontoBoleto'    => array(
            'FriendlyName'  => 'Desconto do Boleto <i class="toggle_billet fas fa-question-circle" id="discount_billet" data-toggle="tooltip" data-placement="top" title="Desconto para pagamentos no boleto bancário."></i>',
            'Type'          => 'text',
            'Description'   => '',
        ),
        'numDiasParaVencimento' => array(
            'FriendlyName'      => 'Número de dias para o vencimento do boleto <i class="toggle_billet fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Número de dias corridos para o vencimento do boleto  depois  de sua criação"></i>',
            'Type'              => 'text',
            'Description'       => '',
        ),
        'sendEmailGN'       => array(
            'FriendlyName'  => '<span class="toggle_billet"></span>Email de cobraça - Efí',
            'Type'          => 'yesno',
            'Description'   => 'Marque esta opção se você deseja que a Efí envie emails de transações para o cliente final',
        ),
        'fineValue'         => array(
            'FriendlyName'  => 'Configuração de Multa <i class="toggle_billet fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Valor da multa se pago após o vencimento - informe em porcentagem (mínimo 0,01% e máximo 10%)."></i>',
            'Type'          => 'text',
            'Description'   => '',
        ),
        'interestValue'         => array(
            'FriendlyName'  => 'Configuração de Juros <i class="toggle_billet fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Valor de juros por dia se pago após o vencimento - informe em porcentagem (mínimo 0,001% e máximo 0,33%)."></i>',
            'Type'          => 'text',
            'Description'   => '',
        ),
        'message'      => array(
            'FriendlyName'  => 'Observação <i class="toggle_billet fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Permite incluir no boleto uma mensagem para o cliente (máximo de 80 caracteres)."></i>',
            'Type'          => 'text',
            'Size'          => '80',
            'Description'   => '',
        ),
        'activePix'       => array(
            'FriendlyName'  => 'Pix',
            'Type'          => 'yesno',
            'Description'   => '<span id="pix"> Ativar PIX como forma de pagamento</span>',
        ),
        'pixKey' => array(
            'FriendlyName' => 'Chave Pix <i class="toggle_pix fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Insira sua chave Pix padrão para recebimentos"></i>',
            'Type' => 'text',
            'Size' => '250',
            'Default' => '',
            'Description' => '',
        ),
        'pixDiscount' => array(
            'FriendlyName' => 'Desconto do Pix (%) <i class="toggle_pix fas fa-question-circle" id="discount_pix" data-toggle="tooltip" data-placement="top" title="Preencha um valor caso queira dar um desconto para pagamentos via Pix"></i>',
            'Type' => 'text',
            'Size' => '3',
            'Default' => '0%',
            'Description' => '',
        ),
        'pixDays' => array(
            'FriendlyName' => 'Validade da Cobrança Pix <i class="toggle_pix fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Tempo em dias de validade da cobrança"></i>',
            'Type' => 'text',
            'Size' => '3',
            'Default' => '1',
            'Description' => '',
        ),
        'pixCert' => array(
            'FriendlyName' => 'Certificado de Autenticação <i class="toggle_pix fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Insira o caminho do seu certificado .pem ou .p12 "></i>',
            'Type' => 'text',
            'Size' => '350',
            'Default' => '/var/certs/cert.pem',
            'Description' => '',
        ),
        'activeCredit'       => array(
            'FriendlyName'  => 'Cartão de Credito',
            'Type'          => 'yesno',
            'Description'   => 'Ativar cartão de crédito como forma de pagamento',
        ),
        'sandbox' => array(
            'FriendlyName' => 'Sandbox',
            'Type' => 'yesno',
            'Description' => 'Habilita o modo Sandbox da Efí',
        ),
        'debug' => array(
            'FriendlyName' => 'Debug',
            'Type' => 'yesno',
            'Description' => 'Habilita o modo Debug',
        ),
        'mtls' => array(
            'FriendlyName' => 'Validar mTLS <i class=" fas fa-question-circle " data-toggle="tooltip" data-placement="top" title="Marque essa opção caso o seu servidor tenha sido configurado para realizar a validação mTLS"></i>',
            'Type' => 'yesno',
            'Default' => false,
            'Description' => 'Entenda os riscos de não configurar o mTLS acessando o link https://gnetbr.com/rke4baDVyd',
        ),
        
        'gn_script' => array(
            'Description' => '
            <script>
            function limitarDuasCasasDecimais(ids) {
                
                ids.forEach(function(id) {
                    let row = $("#discount_" + id).parents()[1];
                    let tdDiscount = $(row).children()[1];
                    let inputDiscount = $(tdDiscount).children()[0];
                    
                    $(inputDiscount).on("input", function() {
                        let value = $(this).val().replace(/[^0-9.]/g, "");
                        let hasDecimal = value.indexOf(".") >= 0;

                        if (hasDecimal) {
                            let decimalIndex = value.indexOf(".");
                            let decimalSubstring = value.substr(decimalIndex + 1);
                            if (decimalSubstring.length > 2) {
                            value = value.substr(0, decimalIndex + 3);
                            }
                        }

                        $(this).val(value);
                    });
                
                })
            }
                function isCheckedOptPayment(method){
                    let labelOptPaymentMethod = $("#" + method).parent();
                    let inputOptPaymentMethod = $(labelOptPaymentMethod).children()[1];
                    let isChecked      = $(inputOptPaymentMethod).is(":checked");

                    if(!isChecked){
                        toggleFieldsPaymentMethod(".toggle_" + method,0)
                    }
                    $(inputOptPaymentMethod).click(()=>{
                        toggleFieldsPaymentMethod(".toggle_" + method,400)
                    })

                }
                

                function toggleFieldsPaymentMethod(method, timeToggle){
                    let fields = $(method);
                    fields.each((i, field)=>{
                        let rowField = $(field).parents()[1];
                        $(rowField).animate({
                            height: "toggle",
                            opacity: "toggle"
                        }, timeToggle).css("overflow", "hidden");
                    })
                }
                isCheckedOptPayment("billet")
                isCheckedOptPayment("pix")
                limitarDuasCasasDecimais(["billet","pix"])
                
               
            </script>

            '
        )
    );
}

function efi_config_validate($params)
{
   ValidationFieldsAdmin($params);
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
function efi_link($gatewayParams)
{
 

    /* **************************************** Verifica se a versão do PHP é compatível com o módulo ******************************** */

    if (version_compare(PHP_VERSION, '8.1') < 0) {
        $errorMsg = 'A versão do PHP do servidor onde o WHMCS está hospedado não é compatível com o módulo Efí.';
        if ($gatewayParams['debug'] == "on")
            logTransaction('efi', $errorMsg, 'Erro de Versão');

        return send_errors(array('Erro Inesperado: Ocorreu um erro inesperado. Entre em contato com o responsável do site.'));
    }
    // Creating table 'tblgerencianetpix'
    createGerencianetPixTable();

    $baseUrl = $gatewayParams['systemurl'];
    


    $identificadorDaConta = $gatewayParams['idConta'];
    $autoCompleteFields = generateAutoCompleteFields($gatewayParams);
    $autoCompletetotal = generateAutoCompleteTotal($gatewayParams);
    $viewInvoiceModal = file_get_contents("$baseUrl/modules/gateways/efi/gerencianet_lib/scripts/js/viewInvoiceModal.js");



    $apiEnvironment = ($gatewayParams['sandbox'] == 'on') ? "sandbox" : "api"; 
    $scriptGetPaymentToken = "<script  type='text/javascript'> 
        var inputEnviroment = $(\"<input />\",{value: '$apiEnvironment',type: 'hidden',id: 'apiEnvironment'}); 
        $(document.body).append(inputEnviroment); 

        var inputIdentificador = $(\"<input />\",{value: '$identificadorDaConta',type: 'hidden',id: 'identificadorDaConta'}); 
        $(document.body).append(inputIdentificador); 
    </script>";


    $paymentOptionsScript = "<div id='modal_content'></div>
    $scriptGetPaymentToken
    <script defer type=\"text/javascript\" src=\"$baseUrl/modules/gateways/efi/gerencianet_lib/scripts/js/viewInvoiceModal.js\"></script>
    <script defer type=\"text/javascript\" src=\"$baseUrl/modules/gateways/efi/gerencianet_lib/scripts/js/jquery-mask/jquery.mask.min.js\"></script>
    <script defer type=\"text/javascript\" src=\"$baseUrl/modules/gateways/efi/gerencianet_lib/scripts/js/validation/validation.js\"></script>
    $autoCompleteFields
    <script defer type=\"text/javascript\" src=\"$baseUrl/modules/gateways/efi/gerencianet_lib/scripts/js/autoComplete/autoComplete.js\"></script>
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
             
        switch ($_POST['optionPayment']) {
            case 'pix':
                return definedPixPayment($gatewayParams);
                break;
            case 'billet':
                return definedBilletPayment($gatewayParams);
                break;
            case 'credit':
                return definedCreditCardPayment($gatewayParams);
                break;
            case 'openFinance':
                return definedOpenFinancePayment($gatewayParams);
                break;
            default:
                break;
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
function efi_refund($gatewayParams)
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

function definedPixPayment($gatewayParams)
{
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
   
}

function definedBilletPayment($gatewayParams)
{
                    
            $gatewayParams['paramsBoleto'] = $_POST;
            $errorMessages = array();
            $errorMessages = validationParams($gatewayParams);
            
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
                    logTransaction('efi', 'O campo - Usuario administrador do WHMCS - está preenchido incorretamente', 'Erro de Integração');
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
            if (strpos($linkPagamento, 'modules/gateways/efi/gerencianet_lib/gerencianet_errors.php')) {
                return ($linkPagamento);
            }
            return buttonGerencianet($errorMessages, $linkPagamento, $descontoBoleto, $tipoDesconto);
}

function definedCreditCardPayment($gatewayParams)
{
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
            $invoiceAmount              = $gatewayParams['amount'];
            if ($invoiceAmount < $minValue) {
                $limitMsg = "<div id=limit-value-msg style='font-weight:bold; color:#cc0000;'>Transação Não permitida: Você está tentando pagar uma fatura de<br> R$ $invoiceAmount. Para gerar o boleto Efí, o valor mínimo do pedido deve ser de R$ $minValue</div>";
                return $limitMsg;
            }
            return createCard($gatewayParams, $gnIntegration, $errorMessages, $existingCharge);
}

