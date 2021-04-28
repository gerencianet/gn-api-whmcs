<?php

include 'BD_access.php';

function get_custom_field_value($field, $clientId)
{
    $table     = "tblcustomfields";
    $fields    = "id";
    $where     = array(
        "fieldname" => $field,
        "type"      => "client"
    );

    $customFieldData = selectCob($table, $where, $fields);
    $customFieldId   = $customFieldData["id"];
    
    $table     = "tblcustomfieldsvalues";
    $fields    = "value";
    $where     = array(
        "fieldid" => $customFieldId,
        "relid"   => $clientId
    );
    $customFieldValueData = selectCob($table, $where, $fields, 1, 'fieldid');
    $field = $customFieldValueData['value'];

    return $field;
}

function get_admin_credentials()
{
    $table    = "tblpaymentgateways";
    $fields    = "*";
     $where     = array(
        "gateway" => "gerencianetcharge"
    );

    $credentials = array();
    $response = selectCob($table, $where, $fields, 8);
    for($i=0; $i<count($response); $i++){
        $name = $response[$i]["setting"];
        $value = $response[$i]["value"];
        if($name == "clientIDProd" || $name == "clientSecretProd" || $name == "clientIDDev" || $name == "numDiasParaVencimento" ||
            $name == "clientSecretDev" || $name == "idConta" ||  $name == "configSandbox" || $name == "whmcsAdmin")
            $credentials[$name] = $value;
    }

    return $credentials;
}

function extra_amounts_Gerencianet_WHMCS($invoiceId, $descontoBoleto, $discountType)
{
    $total        = get_price($invoiceId);

    if($descontoBoleto == 0) return null;

    $where    = array('invoiceid' => $invoiceId);
    $order    = selectCob('tblorders', $where, '*');
    $where    = array('id' => $invoiceId);              
    $invoice  = selectCob('tblinvoices', $where, '*');     
    $userid   = $order['userid'];

    if ($discountType == '1') 
    {
        $discountValue = ($descontoBoleto / 100) * $total; 
        $amount = number_format(($discountValue*(-1)), 2, '.', '');
        $discountMsg = $descontoBoleto . '% sobre o valor da cobrança.';
    }
    else {
        $amount = number_format(($descontoBoleto*(-1)), 2, '.', '');
        $discountMsg = 'R$ ' . $descontoBoleto . ',00';
    }
    
    $where           =  array('invoiceid' => $invoiceId);
    $dataInvoiceItem = selectCob('tblinvoiceitems', $where, 'duedate', 1);
    $duedate         = $dataInvoiceItem['duedate'];

    $dataDiscount = array(
        'invoiceid'     => $invoiceId,
        'userid'        => $userid,
        'relid'         => 0,
        'description'   => 'Desconto no boleto Gerencianet: ' . $discountMsg,
        'amount'        => $amount,
        'taxed'         => 0,
        'duedate'       => $duedate,
        'paymentmethod' => 'gerencianetcharge'
    );

    insertCob('tblinvoiceitems', $dataDiscount);

    $newOrderAmount      = $order['amount'] + $amount; 
    $newInvoiceSubTotal  = $invoice['subtotal'] + $amount;
    $newInvoiceTotal     = $invoice['total'] + $amount;

    $updateAmountOrder   = updateCob('tblorders', array('invoiceid' => $invoiceId), array('amount' => $newOrderAmount)); 
    $updateData          = array('total'    => $newInvoiceTotal, 'subtotal' => $newInvoiceSubTotal);
    $updateAmountInvoice = updateCob('tblinvoices', array('id' => $invoiceId), $updateData);
}

function get_price($invoiceId, $discount=false)
{
    $invoiceDescription         = $params['description'];
    $invoiceAmount              = $params['amount'];
    $invoiceValues['invoiceid'] = $invoiceId;
    $invoiceData                = localAPI("getinvoice", $invoiceValues, $adminuser);

    $invoiceItems = $invoiceData['items']['item'];
    $totalItem    = 0;

    foreach ($invoiceItems as $invoiceItem)
    {
        if($discount == true)
            $totalItem += $invoiceItem['amount'];
        else
        {
            if($invoiceItem['amount'] > 0)
                $totalItem += $invoiceItem['amount'];
        }
    }
    return $totalItem;
}

function update_invoice_status($invoiceId, $status, $adminWHMCS, $isNewTransaction, $datePaid=null)
{   
    if($isNewTransaction == false)
    {
        $updateInvoiceCommand             = "updateinvoice";
        $updateInvoiceValues["invoiceid"] = (int)$invoiceId;
        $updateInvoiceValues["status"]    = $status;
        if($datePaid != null)
            $updateInvoiceValues["datepaid"] = $datePaid;
        $results = localAPI($updateInvoiceCommand, $updateInvoiceValues, $adminWHMCS);
        return $results;
    }
    return 0;
}

function buttonGerencianet($errorMessages=null, $link=null, $discount=0, $discountType=null){
    $src = '<style>
        .botao {
            background-color: #f26522;
            background-image:url("modules/gateways/gerencianet_lib/images/gn-laranja.png");
            background-size: 35px;
            background-repeat: no-repeat;
            background-position-x: 10px;
            background-position-y: 5.5px;
            font-weight: bold;
            font-size: 20px;
            color: white;
            border: none;
            padding: 10px 36px 10px 58px;
            cursor: pointer;
        }
        .botao:hover {
            background-color: #ff751a;
            background-image:url("modules/gateways/gerencianet_lib/images/gn-laranja.png");
            background-size: 35px;
            background-repeat: no-repeat;
            color: white;
            background-position-x: 10px;
            background-position-y: 5.5px;
            cursor: pointer;
        }
        #text-gnbutton {
            margin-left: 5px;
        }
        #desconto-gn{
            font-size: 13px;
        }
    </style>
    <script>
        window.onload = function() {
            document.querySelector("body > div.container-fluid.invoice-container > div:nth-child(4) > div.col-12.col-sm-6.order-sm-last.text-sm-right.invoice-col.right").innerHTML = ""
        };
    </script>
    ';

    if($errorMessages != null)
    {
        $src .= '<form action="modules/gateways/gerencianet_lib/gerencianet_errors.php" method="post">';
        foreach ($errorMessages as $error) {
            $src = $src . '<input type="hidden" name="errors[]" value="' . $error . '"></input>';
        }
        $src .= '<input title="Boleto Gerencianet" target="_blank" type="submit" class="btn botao" value="Boleto"></form><br>';
    }
    else
    {
        if($link == null)
        {
            $src .= "<form action='#' method='post'>
                        <input type='hidden' name='geraCharge' value='true'>
                        <input type='submit' target='_blank' title='Boleto Gerencianet' value='Boleto' class='btn botao'>
                    </form><br>";
        }
        else 
            $src .= '<a title="Boleto Gerencianet" target="_blank" class="btn botao" href="'.$link.'">Boleto</a><br><br>';

        $discount = number_format($discount, 2, '.', '');

        if((double)$discount > 0)
        {
            if($discountType == '1')
                $src .= '<div id="desconto-gn">No boleto Gerencianet <br> você ainda ganha mais '. $discount .'% de desconto <br> sobre o valor bruto da cobrança.</div>';
            else 
                $src .= '<div id="desconto-gn">No boleto Gerencianet <br> você ainda ganha mais R$ '. $discount .' de desconto.</div>';
        }
    }

    return $src;
}

function send_errors($errorMessages)
{
    $url = 'modules/gateways/gerencianet_lib/gerencianet_errors.php';
    
    $code = "<script>
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '" . $url ."';";
    foreach ($errorMessages as $error) {
        $code = $code . 
        "var input = document.createElement('input');
        input.type = 'text';
        input.name = 'errors[]';
        input.value = '" . $error ."';
        form.appendChild(input);";
    }
    
    $code = $code .
    "document.body.appendChild(form);
    form.submit();
    </script>";

    return $code;
}

?>