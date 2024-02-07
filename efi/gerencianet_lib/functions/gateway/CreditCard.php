<?php



use Gerencianet\Gerencianet;



define("NAME_ERROR_MESSAGE", "Nome Inválido: O nome é muito curto. Você deve digitar seu nome completo.");

define("EMAIL_ERROR_MESSAGE", "Email Inválido: O email informado é inválido ou não existe.");

define("BIRTHDATE_ERROR_MESSAGE", "Data de nascimento Inválida: A data de nascimento informada deve seguir o padrão Ano-mes-dia.");

define("PHONENUMBER_ERROR_MESSAGE", "Telefone Inválido: O telefone informado não existe ou o DDD está incorreto.");

define("DOCUMENT_NULL_ERROR_MESSAGE", "Documento Nulo: O campo referente à CPF e/ou CNPJ não existe ou não está preenchido.");

define("CPF_ERROR_MESSAGE", "Documento Inválido: O número do CPF do cliente é invalido.");

define("CNPJ_ERROR_MESSAGE", "Documento Inválido: O número do CNPJ do cliente é invalido.");

define("CORPORATE_ERROR_MESSAGE", "Razão Social Inválida: O nome da empresa é inválido. Você deve digitar no campo \"Empresa\" de seu site o nome que consta na Receita Federal.");

define("CORPORATE_NULL_ERROR_MESSAGE", "Razao Social Nula: O campo \"Empresa\" de seu site não está preenchido.");

define("INTEGRATION_ERROR_MESSAGE", "Erro Inesperado: Ocorreu um erro inesperado. Entre em contato com o responsável do site.");



function getClientVariablesCard($params)

{

    $isJuridica = true;

    if (strpos($params['paramsCartao']['clientDocumentCredit'], '/') == true) {

        $document = str_replace('/', '', str_replace('-', '', str_replace('.', '', $params['paramsCartao']['clientDocumentCredit'])));
    } else {

        $document = str_replace('-', '', str_replace('.', '', $params['paramsCartao']['clientDocumentCredit']));
    }

    $corporateName    = $params['paramsCartao']['clientNameCredit'];



    $name  = $params['paramsCartao']['clientNameCredit'];

    $phone = str_replace(' ', '', str_replace('-', '', str_replace('(', '', str_replace(')', '', $params['paramsCartao']['clientTelephoneCredit']))));

    $email = $params['paramsCartao']['clientEmailCredit'];

    $birthDate = $params['paramsCartao']['dataNasce'];



    if (strlen($document) <= 11)

        $isJuridica = false;



    if ($isJuridica == false) {



        $customer = array(

            'name'          => $name,

            'cpf'           => (string)$document,

            'email'         => $email,

            'birth'         => $birthDate,

            'phone_number'  => (string) $phone

        );
    } else {

        $juridical_data = array(

            'corporate_name' => (string)$corporateName,

            'cnpj'           => (string)$document

        );

        $customer = array(

            'email'             => $email,

            'phone_number'      => (string) $phone,

            'birth'             => $birthDate,

            'juridical_person'  => $juridical_data

        );
    }





    return $customer;
}

function validationParamsCard($params)

{





    $validations   = new GerencianetValidation();

    $mensagensErros = array();

    $birthDate = $params['paramsCartao']['dataNasce'];

    $cardNumber = str_replace(' ', '', (strlen($params['paramsCartao']['numCartao']) >= 14) ? $params['paramsCartao']['numCartao'] : $params['paramsCartao']['numCartaoMobile']);





    /* ********************************************* Coleta os dados do cliente ************************************************* */

    if (strpos($params['paramsCartao']['clientDocumentCredit'], '/')) {

        $document = str_replace('/', '', str_replace('-', '', str_replace('.', '', $params['paramsCartao']['clientDocumentCredit'])));
    } else {

        $document = str_replace('-', '', str_replace('.', '', $params['paramsCartao']['clientDocumentCredit']));
    }

    $corporateName    = $params['paramsCartao']['clientNameCredit'];

    $name  = $params['paramsCartao']['clientNameCredit'];

    $phone = str_replace(' ', '', str_replace('-', '', str_replace('(', '', str_replace(')', '', $params['paramsCartao']['clientTelephoneCredit']))));

    $email = $params['paramsCartao']['clientEmailCredit'];

    if ($document == null || $document == '') {

        array_push($mensagensErros, DOCUMENT_NULL_ERROR_MESSAGE);
    } else {

        if (strlen($document) <= 11) {

            $isJuridica = false;

            if (!$validations->_cpf($document))

                array_push($mensagensErros, CPF_ERROR_MESSAGE);

            if (!$validations->_name($name))

                array_push($mensagensErros, NAME_ERROR_MESSAGE);
        } else {

            $isJuridica = true;

            if (!$validations->_cnpj($document))

                array_push($mensagensErros, CNPJ_ERROR_MESSAGE);

            if ($corporateName == null || $corporateName == '')

                array_push($mensagensErros, CORPORATE_NULL_ERROR_MESSAGE);

            elseif (!$validations->_corporate($corporateName))

                array_push($mensagensErros, CORPORATE_ERROR_MESSAGE);
        }
    }



    if (!$validations->_phone_number($phone))

        array_push($mensagensErros, PHONENUMBER_ERROR_MESSAGE);



    if (!$validations->_email($email))

        array_push($mensagensErros, EMAIL_ERROR_MESSAGE);



    if (!$validations->_birthdate($birthDate)) {

        array_push($mensagensErros, BIRTHDATE_ERROR_MESSAGE);
    }

    if (!$validations->_cardNumber($cardNumber)) {

        array_push($mensagensErros, CARD_ERROR_MESSAGE);
    }



    return $mensagensErros;
}

function existingChargeCredit($params, $gnIntegration)

{

    $getTransactionValues['invoiceid']  = $params['invoiceid'];

    $transactionData                    = localAPI("gettransactions", $getTransactionValues, $params['whmcsAdmin']);

    $totalTransactions                  = $transactionData['totalresults'];

    $existCharge                        = false;

    $resultado =  array();

    $resultado['existCharge'] = $existCharge;



    if ($totalTransactions > 0) {

        $lastTransaction   = end($transactionData['transactions']['transaction']);

        $transactionId     = (string)$lastTransaction['id'];

        $chargeId          = (int)$lastTransaction['transid'];

        $chargeDetailsJson = $gnIntegration->detail_charge($chargeId);

        $chargeDetails     = json_decode($chargeDetailsJson, true);



        if ($chargeDetails['code'] == 200) {

            if ($chargeDetails['data']['payment']['method'] == 'credit_card') {

                $existCharge = true;

                $resultado['existCharge'] = $existCharge;



                $code = generateResult();

                $resultado['code'] = $code;

                return $resultado;
            }
        } else {

            return $resultado;
        }
    }
}





function createCard($params, $gnIntegration, $errorMessages, $existCharge)

{

    $invoiceValues['invoiceid'] = $params['invoiceid'];

    $adminWHMCS = $params['whmcsAdmin'];

    $invoiceData                = localAPI("getinvoice", $invoiceValues, $adminWHMCS);

    $invoiceId = $params['invoiceid'];

    $debug            = $params['debug'];

    $nameGateway = $params['paymentmethod'];





    /* ***************************************** Calcula data de vencimento do boleto **************************************** */





    $userId  = $invoiceData['userid'];

    /* *********************************************** Coleta dados dos itens ************************************************* */



    $valueDiscountWHMCS      = 0;

    $percentageDiscountWHMCS = 0;

    $invoiceItems = $invoiceData['items']['item'];

    $invoiceTax   = (float)$invoiceData['tax'];

    $invoiceTax2  = (float)$invoiceData['tax2'];



    $totalItem     = 0;

    $totalTaxes    = 0;

    $items = array();



    foreach ($invoiceItems as $invoiceItem) {

        if ((float)$invoiceItem['amount'] > 0) {

            $itemValue = number_format((float)$invoiceItem['amount'], 2, '.', '');

            $itemValue = preg_replace("/[.,-]/", "", $itemValue);

            $item = array(

                'name'   => str_replace("'", "", $invoiceItem['description']),

                'amount' => 1,

                'value'  => (int)$itemValue

            );

            array_push($items, $item);

            $totalItem += (float)$invoiceItem['amount'];
        } else {

            $valueDiscountWHMCS += (float)$invoiceItem['amount'];
        }
    }



    if ($invoiceTax > 0) {

        $totalTaxes += (float)$invoiceTax;

        $item = array(

            'name'   => 'Taxa 1: Taxa adicional do WHMCS',

            'amount' => 1,

            'value'  => (int)($invoiceTax * 100)

        );

        array_push($items, $item);
    }



    if ($invoiceTax2 > 0) {

        $totalTaxes += (float)$invoiceTax2;

        $item = array(

            'name'   => 'Taxa 2: Taxa adicional do WHMCS',

            'amount' => 1,

            'value'  => (int)($invoiceTax2 * 100)

        );

        array_push($items, $item);
    }



    $valueDiscountWHMCSFormated = number_format((float)$valueDiscountWHMCS, 2, '.', '');

    $valueDiscountWHMCSinCents  = preg_replace("/[.,-]/", "", $valueDiscountWHMCSFormated);
    $valueDiscountWHMCSinCents = ($valueDiscountWHMCSinCents != 0) ? $valueDiscountWHMCSinCents: null;

    $percentageDiscountWHMCS    = ((100 * $valueDiscountWHMCS) / $totalItem);

    $percentageDiscountWHMCS    = number_format((float)$percentageDiscountWHMCS, 2, '.', '');

    $percentageDiscountWHMCS    = preg_replace("/[.,-]/", "", $percentageDiscountWHMCS);

    



    /* ******************************************************* Gera a charge e transação do cartão ************************************************ */



    if (empty($errorMessages)) {

        $customer = getClientVariablesCard($params);

        $permitionToPay = true;

        $resultCheck = array();



        if ($existCharge == false) {



            $gnApiResult = $gnIntegration->create_charge($items, $invoiceId, $params['systemurl'] . "modules/gateways/callback/efi/card.php");

            $resultCheck = json_decode($gnApiResult, true);



            if ($resultCheck['code'] != 0) {

                $chargeId                               = $resultCheck['data']['charge_id'];

                $addTransactionCommand                  = "addtransaction";

                $addTransactionValues['userid']         = $userId;

                $addTransactionValues['invoiceid']      = $invoiceId;

                $addTransactionValues['description']    = "Cartão Efí: Cobrança gerada.";

                $addTransactionValues['amountin']       = '0.00';

                $addTransactionValues['fees']           = '0.00';

                $addTransactionValues['paymentmethod']  = 'efi';

                $addTransactionValues['transid']        = (string)$chargeId;

                $addTransactionValues['date']           = date('d/m/Y');

                $addtransresults = localAPI($addTransactionCommand, $addTransactionValues, $adminWHMCS);

                $permitionToPay = true;
            } else

                $permitionToPay = false;
        }



        if ($permitionToPay == true) {

            $resultPayment = $gnIntegration->pay_credit($chargeId, $customer, $params['message'], $params, $valueDiscountWHMCSinCents);



            $resultPaymentDecoded = json_decode($resultPayment, true);
            $data      = $resultPaymentDecoded['data'];

            if ($resultPaymentDecoded['code'] != 0) {

                if ($data['status'] == "approved") {
                    addInvoicePayment($invoiceId, $chargeId, $params['amount'], '0.00', $params['paymentmethod']);
                    return generateResult($data['status']);
                } else {
                    $getTransactionValues['invoiceid']  = $invoiceId;

                    $transactionData                    = localAPI("gettransactions", $getTransactionValues, $adminWHMCS);

                    $lastTransaction                    = end($transactionData['transactions']['transaction']);

                    $transactionId                      = (string)$lastTransaction['id'];





                    $chargeId  = $data["charge_id"];



                    $updateTransactionCommand                  = "updatetransaction";

                    $updateTransactionValues['transactionid']  = $transactionId;

                    $updateTransactionValues['description']    = "Cartão de crédito Efí: Cobrança aguardando pagamento.";

                    $updatetransresults = localAPI($updateTransactionCommand, $updateTransactionValues, $adminWHMCS);

                    return generateResult();
                }
            } else {

                array_push($errorMessages, $resultPaymentDecoded['message']);

                if ($debug == "on")

                    logTransaction('efi', array('invoiceid' => $invoiceId, 'charge_id' => $chargeId, 'error' => $resultPaymentDecoded['messageAdmin']), 'Erro Efí: Geração do boleto');

                return send_errors($errorMessages);
            }
        } else {

            array_push($errorMessages, $resultCheck['message']);

            if ($debug == "on")

                logTransaction('efi', array('invoiceid' => $invoiceId, 'error' => $resultCheck['messageAdmin']), 'Erro Efí: Geração da cobrança');

            return send_errors($errorMessages);
        }
    } else {

        $validationErrors = array('invoiceid' => $invoiceId);



        foreach ($errorMessages as $error) {

            array_push($validationErrors, $error);
        }



        if ($debug == "on")

            logTransaction('efi', $validationErrors, 'Erro Efí: Validação');

        return send_errors($errorMessages);
    }
}

function generateResult($status = null)

{
    if ($status == "approved") {
        return '
                <div class="alert alert-success" role="alert">

                    <h4 class="alert-heading">Pagamento Aprovado</h4>

                    <hr>

                    <p class="mb-0">Seu pagamento foi aprovado! Em alguns segundos a sua fatura será atualizada</p>

                </div>
                <script>
                    setTimeout(function() {
                        window.location.reload();
                    }, 4000);
                  
                </script>

            ';
    }

    return '

            <div class="alert alert-success" role="alert">

                <h4 class="alert-heading">Pagamento em processamento!</h4>

                <hr>

                <p class="mb-0">A transação do seu cartão está sendo processada. Assim que for confirmada lhe enviaremos um email para confirmação</p>

            </div>

        ';
}
