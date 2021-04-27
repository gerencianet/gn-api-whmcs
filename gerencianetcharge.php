<?php
include_once 'gerencianet_lib/Gerencianet_WHMCS_Interface.php';

function gerencianetcharge_config()
{
    $configarray = array(
        "FriendlyName"  => array(
            "Type"      => "System",
            "Value"     => "Gerencianet"
        ),

        "clientIDProd"      => array(
            "FriendlyName"  => "Client_Id Produção (*)",
            "Type"          => "text",
            "Size"          => "50",
            "Description"   => " (preenchimento obrigatório)",
        ),

        "clientSecretProd"  => array(
            "FriendlyName"  => "Client_Secret Produção (*)",
            "Type"          => "text",
            "Size"          => "54",
            "Description"   => " (preenchimento obrigatório)",
        ),

        "clientIDDev"       => array(
            "FriendlyName"  => "Client_Id Desenvolvimento (*)",
            "Type"          => "text",
            "Size"          => "50",
            "Description"   => " (preenchimento obrigatório)",
        ),

        "clientSecretDev"   => array(
            "FriendlyName"  => "Client_Secret Desenvolvimento (*)",
            "Type"          => "text",
            "Size"          => "54",
            "Description"   => " (preenchimento obrigatório)",
        ),

        "idConta"           => array(
            "FriendlyName"  => "Identificador da Conta (*)",
            "Type"          => "text",
            "Size"          => "32",
            "Description"   => " (preenchimento obrigatório)",
        ),

        "whmcsAdmin"    => array(
            "FriendlyName"  => "Usuario administrador do WHMCS (*)",
            "Type"          => "text",
            "Description"   => "Insira o nome do usuário administrador do WHMCS.",
            "Description"   => " (preenchimento obrigatório)",
        ),

        "descontoBoleto"    => array(
            "FriendlyName"  => "Desconto do Boleto",
            "Type"          => "text",
            "Description"   => "Desconto para pagamentos no boleto bancário.",
        ),

        "tipoDesconto"      => array(
            'FriendlyName'  => 'Tipo de desconto',
            'Type'          => 'dropdown',
            'Options'       => array(
                '1'         => '% (Porcentagem)',
                '2'         => 'R$ (Reais)',
            ),
            'Description'   => 'Escolha a forma do desconto: Porcentagem ou em Reais',
        ),

        "numDiasParaVencimento" => array(
            "FriendlyName"      => "Número de dias para o vencimento da cobrança",
            "Type"              => "text",
            "Description"       => "Número de dias corridos para o vencimento da cobrança depois que a mesma foi gerada",
        ),

        "documentField" => array(
            "FriendlyName"      => "Nome do campo referente à CPF e/ou CNPJ (*)",
            "Type"              => "text",
            "Description"       => "Informe o nome do campo referente à CPF e/ou CNPJ no seu WHMCS. (preenchimento obrigatório)",
        ),

        "configSandbox"     => array(
            "FriendlyName"  => "Sandbox",
            "Type"          => "yesno",
            "Description"   => "Habilita o ambiente de testes da API Gerencianet",
        ),

        "configDebug"       => array(
            "FriendlyName"  => "Debug",
            "Type"          => "yesno",
            "Description"   => "Habilita logs de transação e de erros referentes à integração da API Gerencianet com o WHMCS",
        ),

        "sendEmailGN"       => array(
            "FriendlyName"  => "Email de cobraça - Gerencianet",
            "Type"          => "yesno",
            "Description"   => "Marque esta opção se você deseja que a Gerencianet envie emails de transações para o cliente final",
        ),

        "fineValue"         => array(
            "FriendlyName"  => "Configuração de Multa",
            'Type'          => 'text',
            "Description"   => "Valor da multa se pago após o vencimento - informe em porcentagem (mínimo 0,01% e máximo 10%).",
        ),

        "interestValue"         => array(
            "FriendlyName"  => "Configuração de Juros",
            'Type'          => 'text',
            "Description"   => "Valor de juros por dia se pago após o vencimento - informe em porcentagem (mínimo 0,001% e máximo 0,33%).",
        ),

        "message"      => array(
            'FriendlyName'  => 'Observação',
            'Type'          => 'text',
            'Size'          => '80',
            'Description'   => 'Permite incluir no boleto uma mensagem para o cliente (máximo de 80 caracteres).',
        ),

    );
    return $configarray;
}

function gerencianetcharge_link($params)
{
    $geraCharge = false;
    if (isset($_POST['geraCharge']))
        $geraCharge = $_POST['geraCharge'];

    /* **************************************** Verifica se a versão do PHP é compatível com a API ******************************** */

    if (version_compare(PHP_VERSION, '7.3') < 0) {
        $errorMsg = 'A versão do PHP do servidor onde o WHMCS está hospedado não é compatível com o módulo Gerencianet. Atualize o PHP para uma versão igual ou superior à versão 5.4.39';
        if ($params['configDebug'] == "on")
            logTransaction('gerencianetcharge', $errorMsg, 'Erro de Versão');

        return send_errors(array('Erro Inesperado: Ocorreu um erro inesperado. Entre em contato com o responsável do WHMCS.'));
    }

    /* ***************************************************** Includes e captura do invoice **************************************** */

    include_once 'gerencianet_lib/GerencianetIntegration.php';
    include_once 'gerencianet_lib/GerencianetValidation.php';

    $invoiceId   = $params['invoiceid'];
    $urlCallback = $params['systemurl'] . 'modules/gateways/callback/gerencianetcharge.php';

    /* ************************************************ Define mensagens de erro ***************************************************/

    $validations   = new GerencianetValidation();
    $errorMessages = array();

    define("NAME_ERROR_MESSAGE", "Nome Inválido: O nome é muito curto. Você deve digitar seu nome completo.");
    define("EMAIL_ERROR_MESSAGE", "Email Inválido: O email informado é inválido ou não existe.");
    define("BIRTHDATE_ERROR_MESSAGE", "Data de nascimento Inválida: A data de nascimento informada deve seguir o padrão Ano-mes-dia.");
    define("PHONENUMBER_ERROR_MESSAGE", "Telefone Inválido: O telefone informado não existe ou o DDD está incorreto.");
    define("DOCUMENT_NULL_ERROR_MESSAGE", "Documento Nulo: O campo referente à CPF e/ou CNPJ não existe ou não está preenchido.");
    define("CPF_ERROR_MESSAGE", "Documento Inválido: O número do CPF do cliente é invalido.");
    define("CNPJ_ERROR_MESSAGE", "Documento Inválido: O número do CNPJ do cliente é invalido.");
    define("CORPORATE_ERROR_MESSAGE", "Razão Social Inválida: O nome da empresa é inválido. Você deve digitar no campo \"Empresa\" de seu WHMCS o nome que consta na Receita Federal.");
    define("CORPORATE_NULL_ERROR_MESSAGE", "Razao Social Nula: O campo \"Empresa\" de seu WHMCS não está preenchido.");
    define("INTEGRATION_ERROR_MESSAGE", "Erro Inesperado: Ocorreu um erro inesperado. Entre em contato com o responsável do WHMCS.");

    /* ******************************************** Gateway Configuration Parameters ******************************************* */

    $clientIDProd           = $params['clientIDProd'];
    $clientSecretProd       = $params['clientSecretProd'];
    $clientIDDev            = $params['clientIDDev'];
    $clientSecretDev        = $params['clientSecretDev'];
    $idConta                = $params['idConta'];
    $descontoBoleto         = $params['descontoBoleto'];
    $tipoDesconto           = $params['tipoDesconto'];
    $numDiasParaVencimento  = $params['numDiasParaVencimento'];
    $documentField          = $params['documentField'];
    $minValue               = 5; //Valor mínimo de emissão de boleto na Gerencianet
    $configSandbox          = $params['configSandbox'];
    $configDebug            = $params['configDebug'];
    $configVencimento       = $params['configVencimento'];
    $sendEmailGN            = $params['sendEmailGN'];
    $fineValue              = $params['fineValue'];
    $interestValue          = $params['interestValue'];
    $billetMessage          = $params['message'];
    $adminWHMCS             = $params['whmcsAdmin'];

    if ($adminWHMCS == '' || $adminWHMCS == null) {
        array_push($errorMessages, INTEGRATION_ERROR_MESSAGE);
        if ($configDebug == "on")
            logTransaction('gerencianetcharge', 'O campo - Usuario administrador do WHMCS - está preenchido incorretamente', 'Erro de Integração');
        return send_errors($errorMessages);
    }

    /* ***************************** Verifica se já existe um boleto para o pedido em questão *********************************** */

    $gnIntegration = new GerencianetIntegration($clientIDProd, $clientSecretProd, $clientIDDev, $clientSecretDev, $configSandbox, $idConta);

    $getTransactionValues['invoiceid']  = $invoiceId;
    $transactionData                    = localAPI("gettransactions", $getTransactionValues, $adminWHMCS);
    $totalTransactions                  = $transactionData['totalresults'];
    $existCharge                        = false;

    if ($totalTransactions > 0) {
        $lastTransaction   = end($transactionData['transactions']['transaction']);
        $transactionId     = (string)$lastTransaction['id'];
        $chargeId          = (int)$lastTransaction['transid'];
        $chargeDetailsJson = $gnIntegration->detail_charge($chargeId);
        $chargeDetails     = json_decode($chargeDetailsJson, true);

        if ($chargeDetails['code'] == 200) {
            $existCharge = true;
            if (isset($chargeDetails['data']['payment']['banking_billet']['pdf']['charge'])) {
                $url  = $chargeDetails['data']['payment']['banking_billet']['pdf']['charge'];
                $code = buttonGerencianet(null, $url);
                return $code;
            }
        }
    }

    $invoiceDescription         = $params['description'];
    $invoiceAmount              = $params['amount'];
    if ($invoiceAmount < $minValue) {
        $limitMsg = "<div id=limit-value-msg style='font-weight:bold; color:#cc0000;'>Transação Não permitida: Você está tentando pagar uma fatura de<br> R$ $invoiceAmount. Para gerar o boleto Gerencianet, o valor mínimo do pedido deve ser de R$ $minValue</div>";
        return $limitMsg;
    }

    if ($geraCharge == true) {
        /* ************************************************* Invoice parameters *************************************************** */

        $invoiceValues['invoiceid'] = $invoiceId;
        $invoiceData                = localAPI("getinvoice", $invoiceValues, $adminWHMCS);

        /* ***************************************** Calcula data de vencimento do boleto **************************************** */

        if ($numDiasParaVencimento == null || $numDiasParaVencimento == '')
            $numDiasParaVencimento = '0';

        $userId  = $invoiceData['userid'];
        $duedate = $invoiceData['duedate'];

        if ($duedate < date('Y-m-d'))
            $duedate = date('Y-m-d');

        $date = DateTime::createFromFormat('Y-m-d', $duedate);
        $date->add(new DateInterval('P' . (string)$numDiasParaVencimento . 'D'));
        $newDueDate = (string)$date->format('Y-m-d');


        /* *********************************************** Coleta dados dos itens ************************************************* */

        $valueDiscountWHMCS      = 0;
        $percentageDiscountWHMCS = 0;
        $invoiceItems = $invoiceData['items']['item'];
        $invoiceTax   = (double)$invoiceData['tax'];
        $invoiceTax2  = (double)$invoiceData['tax2'];

        $totalItem     = 0;
        $totalTaxes    = 0;
        $items = array();

        foreach ($invoiceItems as $invoiceItem) {
            if ((double)$invoiceItem['amount'] > 0) {
                $itemValue = number_format($invoiceItem['amount'], 2, '.', '');
                $itemValue = preg_replace("/[.,-]/", "", $itemValue);
                $item = array(
                    'name'   => str_replace("'", "", $invoiceItem['description']),
                    'amount' => 1,
                    'value'  => (int)$itemValue
                );
                array_push($items, $item);
                $totalItem += (double)$invoiceItem['amount'];
            } else {
                $valueDiscountWHMCS += (double)$invoiceItem['amount'];
            }
        }

        if ($invoiceTax > 0) {
            $totalTaxes += (double)$invoiceTax;
            $item = array(
                'name'   => 'Taxa 1: Taxa adicional do WHMCS',
                'amount' => 1,
                'value'  => (int)($invoiceTax * 100)
            );
            array_push($items, $item);
        }

        if ($invoiceTax2 > 0) {
            $totalTaxes += (double)$invoiceTax2;
            $item = array(
                'name'   => 'Taxa 2: Taxa adicional do WHMCS',
                'amount' => 1,
                'value'  => (int)($invoiceTax2 * 100)
            );
            array_push($items, $item);
        }

        $valueDiscountWHMCSFormated = number_format($valueDiscountWHMCS, 2, '.', '');
        $valueDiscountWHMCSinCents  = preg_replace("/[.,-]/", "", $valueDiscountWHMCSFormated);
        $percentageDiscountWHMCS    = ((100 * $valueDiscountWHMCS) / $totalItem);
        $percentageDiscountWHMCS    = number_format($percentageDiscountWHMCS, 2, '.', '');
        $percentageDiscountWHMCS    = preg_replace("/[.,-]/", "", $percentageDiscountWHMCS);

        /* ***************************************** Calcula desconto do boleto e do WHMCS ******************************************* */

        $discount = false;
        $discountWHMCS = 0;
        $invoiceCredit = (int)(number_format((double)$invoiceData['credit'], 2, '.', '') * 100);

        $descontoBoleto = number_format((double)$descontoBoleto, 2, '.', '');

        if ($tipoDesconto == '1')
            $discounGerencianet         = ((double)$descontoBoleto / 100) * $totalItem;
        else
            $discounGerencianet  = (double)$descontoBoleto;

        $discounGerencianetFormated = number_format($discounGerencianet, 2, '.', '');
        $discounGerencianetinCents  = (double)$discounGerencianetFormated * 100;
        $discountValue = $discounGerencianetinCents + $valueDiscountWHMCSinCents + $invoiceCredit;

        $discountInReals = (double)$discounGerencianetFormated + (double)$valueDiscountWHMCSFormated + (double)$invoiceData['credit'];

        if ($discountValue > 0)
            $discount = array(
                'type' => 'currency',
                'value' => (int)$discountValue
            );

        $invoiceTotalInReals = (double)$totalItem - $discountInReals + $totalTaxes;

        /* ********************************************* Coleta os dados do cliente ************************************************* */

        $clientId         = $params['clientdetails']['id'];
        $document         = preg_replace("/[^0-9]/", "", get_custom_field_value((string)$documentField, $clientId));
        $corporateName    = $params['clientdetails']['companyname'];

        $name  = $params['clientdetails']['firstname'] . ' ' . $params['clientdetails']['lastname'];
        $phone = preg_replace('/[^0-9]/', '', $params['clientdetails']['phonenumber']);
        $email = $params['clientdetails']['email'];

        if ($document == null || $document == '')
            array_push($errorMessages, DOCUMENT_NULL_ERROR_MESSAGE);
        else {
            if (strlen($document) <= 11) {
                $isJuridica = false;
                if (!$validations->_cpf($document))
                    array_push($errorMessages, CPF_ERROR_MESSAGE);
                if (!$validations->_name($name))
                    array_push($errorMessages, NAME_ERROR_MESSAGE);
            } else {
                $isJuridica = true;
                if (!$validations->_cnpj($document))
                    array_push($errorMessages, CNPJ_ERROR_MESSAGE);
                if ($corporateName == null || $corporateName == '')
                    array_push($errorMessages, CORPORATE_NULL_ERROR_MESSAGE);
                elseif (!$validations->_corporate($corporateName))
                    array_push($errorMessages, CORPORATE_ERROR_MESSAGE);
            }
        }

        if (!$validations->_phone_number($phone))
            array_push($errorMessages, PHONENUMBER_ERROR_MESSAGE);

        if (!$validations->_email($email))
            array_push($errorMessages, EMAIL_ERROR_MESSAGE);

        if ($isJuridica == false) {
            if ($sendEmailGN == "on")
                $customer = array(
                    'name'          => $name,
                    'cpf'           => (string)$document,
                    'email'         => $email,
                    'phone_number'  => $phone
                );
            else
                $customer = array(
                    'name'          => $name,
                    'cpf'           => (string)$document,
                    'phone_number'  => $phone
                );
        } else {
            $juridical_data = array(
                'corporate_name' => (string)$corporateName,
                'cnpj'           => (string)$document
            );

            if ($sendEmailGN == "on")
                $customer = array(
                    'email'             => $email,
                    'phone_number'      => $phone,
                    'juridical_person'  => $juridical_data
                );
            else
                $customer = array(
                    'phone_number'      => $phone,
                    'juridical_person'  => $juridical_data
                );
        }

        /* *********************************************** Multa, juros e observação no boleto ******************************************** */

        $fineValue      = preg_replace("/[,]/", ".", $fineValue);
        $fineValue      = preg_replace("/[%]/", "", $fineValue);
        $fineValue      = number_format($fineValue, 2, '.', '');
        $fineValue      = (int)preg_replace("/[.,-]/", "", $fineValue);

        $interestValue  = preg_replace("/[,]/", ".", $interestValue);
        $interestValue  = preg_replace("/[%]/", "", $interestValue);
        $interestValue  = number_format($interestValue, 3, '.', '');
        $interestValue  = (int)preg_replace("/[.,-]/", "", $interestValue);

        /* ******************************************************* Gera a charge e o boleto ************************************************ */
        if (empty($errorMessages)) {
            $permitionToPay = true;
            $resultCheck = array();

            if ($existCharge == false) {
                $gnApiResult = $gnIntegration->create_charge($items, $invoiceId, $urlCallback);
                $resultCheck = json_decode($gnApiResult, true);
                if ($resultCheck['code'] != 0) {
                    $chargeId                               = $resultCheck['data']['charge_id'];
                    $addTransactionCommand                  = "addtransaction";
                    $addTransactionValues['userid']         = $userId;
                    $addTransactionValues['invoiceid']      = $invoiceId;
                    $addTransactionValues['description']    = "Boleto Gerencianet: Cobrança gerada.";
                    $addTransactionValues['amountin']       = '0.00';
                    $addTransactionValues['fees']           = '0.00';
                    $addTransactionValues['paymentmethod']  = 'gerencianetcharge';
                    $addTransactionValues['transid']        = (string)$chargeId;
                    $addTransactionValues['date']           = date('d/m/Y');
                    $addtransresults = localAPI($addTransactionCommand, $addTransactionValues, $adminWHMCS);
                    $permitionToPay = true;
                } else
                    $permitionToPay = false;
            }

            if ($permitionToPay == true) {
                $resultPayment = $gnIntegration->pay_billet($chargeId, $newDueDate, $customer, $billetMessage, $fineValue, $interestValue, $discount);
                $resultPaymentDecoded = json_decode($resultPayment, true);

                if ($resultPaymentDecoded['code'] != 0) {
                    $getTransactionValues['invoiceid']  = $invoiceId;
                    $transactionData                    = localAPI("gettransactions", $getTransactionValues, $adminWHMCS);
                    $lastTransaction                    = end($transactionData['transactions']['transaction']);
                    $transactionId                      = (string)$lastTransaction['id'];

                    $data      = $resultPaymentDecoded['data'];
                    $chargeId  = $data["charge_id"];
                    $url       = (string)$data['pdf']['charge'];

                    $updateTransactionCommand                  = "updatetransaction";
                    $updateTransactionValues['transactionid']  = $transactionId;
                    $updateTransactionValues['description']    = "Boleto Gerencianet: Cobrança aguardando pagamento.";
                    $updatetransresults = localAPI($updateTransactionCommand, $updateTransactionValues, $adminWHMCS);
                    $code = "<meta http-equiv='refresh' content='0;url=" . $resultPaymentDecoded["data"]['pdf']['charge'] . "'>";
                    return $code;
                } else {
                    array_push($errorMessages, $resultPaymentDecoded['message']);
                    if ($configDebug == "on")
                        logTransaction('gerencianetcharge', array('invoiceid' => $invoiceId, 'charge_id' => $chargeId, 'error' => $resultPaymentDecoded['messageAdmin']), 'Erro Gerencianet: Geração do boleto');
                    return send_errors($errorMessages);
                }
            } else {
                array_push($errorMessages, $resultCheck['message']);
                if ($configDebug == "on")
                    logTransaction('gerencianetcharge', array('invoiceid' => $invoiceId, 'error' => $resultCheck['messageAdmin']), 'Erro Gerencianet: Geração da cobrança');
                return send_errors($errorMessages);
            }
        } else {
            $validationErrors = array('invoiceid' => $invoiceId);

            foreach ($errorMessages as $error) {
                array_push($validationErrors, $error);
            }

            if ($configDebug == "on")
                logTransaction('gerencianetcharge', $validationErrors, 'Erro Gerencianet: Validação');
            return send_errors($errorMessages);
        }
    } else {
        return buttonGerencianet(null, null, $descontoBoleto, $tipoDesconto);
    }
}
