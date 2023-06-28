<?php
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