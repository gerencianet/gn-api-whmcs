<?php
function generateAutoCompleteFields($gatewayParams)
{
    $clientFullName = $gatewayParams['clientdetails']['firstname'] ." ". $gatewayParams['clientdetails']['lastname'];
    $clientEmail = $gatewayParams['clientdetails']['email'];
    $clientRua = str_replace(',','',explode(',',$gatewayParams['clientdetails']['address1'])[0]);
    $clientNumero = explode(',',$gatewayParams['clientdetails']['address1'])[1];
    $clientCidade = $gatewayParams['clientdetails']['city'];
    $clientEstado = $gatewayParams['clientdetails']['state'];
    $clientCep = str_replace('-', '', $gatewayParams['clientdetails']['postcode']);
    $clientTelefone = $gatewayParams['clientdetails']['phonenumber'];
    $clientBairro = $gatewayParams['clientdetails']['address2'];
    return  "<script type=\"text/javascript\">
        var input = '';
        input = $(\"<input />\",{value: '$clientFullName',type: 'hidden',name: 'nomeCompletoCliente',class: 'nomeCompletoCliente'});
        $(document.body).append(input);
        input = $(\"<input />\",{value: '$clientEmail',type: 'hidden',name: 'emailCliente',class: 'emailCliente'});
        $(document.body).append(input);
        input = $(\"<input />\",{
            value: '$clientRua',
            type: 'hidden',
            name: 'ruaCliente',
            class: 'ruaCliente'
        });
        $(document.body).append(input);
        input = $(\"<input />\",{
            value: '$clientNumero',
            type: 'hidden',
            name: 'numeroCasaCliente',
            class: 'numeroCasaCliente'
        });
        $(document.body).append(input);
        input = $(\"<input />\",{
            value:  '$clientCidade' ,
            type: 'hidden',
            name: 'cidadeCliente',
            class: 'cidadeCliente'
        });
        $(document.body).append(input);
        input = $(\"<input />\",{
            value: '$clientEstado',
            type: 'hidden',
            name: 'estadoCliente',
            class: 'estadoCliente'
        });
        $(document.body).append(input);
        input = $(\"<input />\",{
            value: '$clientBairro',
            type: 'hidden',
            name: 'bairroCliente',
            class: 'bairroCliente'
        });
        $(document.body).append(input);
        input = $(\"<input />\",{
            value: '$clientCep',
            type: 'hidden',
            name: 'cepCliente',
            class: 'cepCliente'
        });
        $(document.body).append(input);
        input = $(\"<input />\",{
            value: '$clientTelefone',
            type: 'hidden',
            name: 'telefoneCliente',
            class: 'telefoneCliente'
        });
        $(document.body).append(input);
    </script>";
    
}
function generateAutoCompleteTotal($gatewayParams)
{
    $total = floatval($gatewayParams['amount']);
    $tipoDescontoBoleto = $gatewayParams['tipoDesconto'];
    $descontoBoleto = floatval($gatewayParams['descontoBoleto']);
    $descontoPix = floatval($gatewayParams['pixDiscount']); 
    $totalDescontoPix = ($descontoPix * $total) / 100;
    $totalDescontoBoleto = ($tipoDescontoBoleto == '1')?(($total * $descontoBoleto)/100):$descontoBoleto;
    $creditOption = $gatewayParams['activeCredit'] == "on";
    $pixOption = $gatewayParams['activePix'] == "on";
    $boletoOption = $gatewayParams['activeBoleto'] == "on";
    $openFinanceOption = $gatewayParams['activeOpenFinance'] == "on";

    return "<script type=\"text/javascript\">
            var input = '';
            input = $(\"<input />\",{
                value:$total ,
                type: 'hidden',
                name: 'invoice_value',
                class: 'invoice_value'
            });
            $(document.body).append(input);
            input = $(\"<input />\", {
                value:$totalDescontoBoleto ,
                type: 'hidden',
                name: 'totalDescontoBoleto',
                class: 'totalDescontoBoleto'
            });
            $(document.body).append(input);
            input = $(\"<input />\", {
                value:$totalDescontoPix ,
                type: 'hidden',
                name: 'totalDescontoPix',
                class: 'totalDescontoPix'
            });
            $(document.body).append(input);
            input = $(\"<input />\", {
                value:'$creditOption' ,
                type: 'hidden',
                name: 'ativarCredito',
                class: 'ativarCredito'
            });
            $(document.body).append(input);
            input = $(\"<input />\", {
                value:'$pixOption' ,
                type: 'hidden',
                name: 'pixOption',
                class: 'pixOption'
            });
            $(document.body).append(input);
            input = $(\"<input />\", {
                value:'$boletoOption' ,
                type: 'hidden',
                name: 'boletoOption',
                class: 'boletoOption'
            });
            $(document.body).append(input);
            input = $(\"<input />\", {
                value:'$openFinanceOption' ,
                type: 'hidden',
                name: 'openFinanceOption',
                class: 'openFinanceOption'
            });
            $(document.body).append(input);
            </script>
            ";
}