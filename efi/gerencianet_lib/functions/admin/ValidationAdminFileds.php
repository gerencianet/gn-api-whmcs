<?php

/**
 * Valida os campos do módulo na área de administração do WHMCS
 *
 * @param array $params Array contendo as configurações do módulo
 *
 * @throws Exception Caso algum erro de validação ocorra
 */
function ValidationFieldsAdmin($params)
{
        // Verifica o ambiente e a versão do TLS
        environment();
        tlsVersion();

        // // Valida campos obrigatórios
         requiredFields($params);

        // // Valida autenticação
         testAuthentication($params);

        // // Valida campos do boleto, se estiver ativo
         if (isset($params['activeBoleto']) && $params['activeBoleto'] == 'on') {
             requiredBilletFields($params);
        }

        // // Valida campos do PIX, se estiver ativo
        if (isset($params['activePix']) && $params['activePix'] == 'on') {
            requiredPixFields($params);
        }

        // // Valida campos do Open Finance, se estiver ativo
        if (isset($params['activeOpenFinance']) && $params['activeOpenFinance'] == 'on') {
            requiredOpenFinanceFields($params);
        }
    
}
function verifyEmptyValue($value, $fieldDescription)
{
   if (empty($value)) {
        generateException("O campo $fieldDescription  não foi preenchido");
   }
}
function environment()
{

    if ($_SERVER['HTTPS'] !== 'on' || strpos($_SERVER['HTTP_REFERER'], 'localhost') !== false ||  strpos($_SERVER['HTTP_REFERER'], '127.0.0.1')) {

       
        generateException('
                    <span style="line-height: 2;>Identificamos que o seu domínio não possui certificado de segurança HTTPS ou 
                     não é válido para registrar o Webhook!</span><br><br>
            ');
    }
}

function tlsVersion()
{
    $ch = curl_init('https://www.howsmyssl.com/a/check'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    $response = curl_exec($ch); 
    curl_close($ch); 
    $data = json_decode($response);
    $tls_version = floatval(explode(" ",$data->tls_version)[1]);

    if (!($tls_version > 1.1) ) {
        generateException('
                                 <span  style="line-height: 2;">Identificamos que a sua hospedagem não suporta uma versão segura do TLS(Transport Layer Security) para se comunicar  
                                 com a Efí. Para conseguir gerar transações, será necessário que contate o administrador do seu servidor e solicite que 
                                 a hospedagem seja atualizada para suportar comunicações por meio do TLS na versão mínima 1.2. 
                                 Em caso de dúvidas e para maiores informações, entre cm contato com    a Equipe Técnica da Efí at ravés do suporte da empresa.</span><br><br>
                            ');                        

    }
}

function requiredFields($params)
{
    $tests = [
        "Client_id produção" => $params['clientIdProd'],
        "Client_secret produção" => $params['clientSecretProd'],
        "Client_id sandbox" => $params['clientIdSandbox'],
        "Client_secret sandbox" => $params['clientSecretSandbox'],
        "identificador da conta" => $params['idConta'],
        "administrador do whmcs" => $params['whmcsAdmin']
    ];
    foreach ($tests as $message => $value) {
        verifyEmptyValue($value,$message);
    }

    if ($params['activeBoleto'] != 'on' && $params['activeCredit'] != 'on' && $params['activePix'] != 'on' && $params['activeOpenFinance'] != 'on') {
        generateException('Nenhuma forma de pagamento ativa!');
    }
}

function requiredBilletFields($params)
{

        if (!empty($params['descontoBoleto'])) {
            $desconto = str_replace(',', '.', $params['descontoBoleto']);
            if (!is_numeric($desconto)) {
                generateException('O campo desconto do boleto  não foi preenchido corretamente.');
            }
            $desconto = (float) $desconto;
            if($params['tipoDesconto']  == '1' &&  $desconto >= 100 ) {
                generateException('Parece que você tentou aplicar um desconto no Boleto igual ou superior a 100%. Por favor, corrija o valor informado para conseguir salvar as configurações.');
            }
        }

        if ( empty($params['numDiasParaVencimento']) || !(is_numeric($params['numDiasParaVencimento']))) {
            generateException('O campo dias para vencimento do boleto  não foi preenchido corretamente');
        }

        if (!empty($params['fineValue'])) {
            $multa = str_replace(',', '.', $params['fineValue']);
            if (!is_numeric($multa)) {
                generateException('O campo multa  não foi preenchido corretamente');
            }
        }
        if (!empty($params['interestValue'])) {
            $juros = str_replace(',', '.', $params['interestValue']);
            if (!is_numeric($juros)) {
                generateException('O campo juros  não foi preenchido corretamente');
            }
        }
    
}

function requiredPixFields($params)
{
    $tests = [
        "chave Pix" => $params['pixKey'],
        "validade  da cobrança Pix" => $params['pixDays']
    ];
    foreach ($tests as $message => $value) {
        verifyEmptyValue($value, $message);
    }
    
    $chave_pix = $params['pixKey'];

    if (!isPixKeyValid($chave_pix)) {
        generateException('Chave PIX inválida');
    }

        if (empty($params['pixCert']) || !(file_exists($params['pixCert']))) {
            $pathCert = $params['pixCert'];
            $fullPath = __DIR__;
            $message = "<div style=\"line-height: 1.5; word-break: break-all;\">Não foi possível encontrar o certificado no caminho informado: <br>  <strong>'$pathCert'</strong> <br><br>
                Insira o caminho completo do arquivo, como no exemplo abaixo:<br>
                <strong> '$fullPath'</strong>
                <br></div>
            ";
            generateException($message);
        }

        if (!empty($params['pixDiscount'])) {
            $descontoPix =  str_replace(',', '.', str_replace('%', '', $params['pixDiscount']));
            if (!is_numeric($descontoPix)) {
                generateException('O campo desconto Pix  não foi preenchido corretamente');
            }
            $descontoPix =  (float) $descontoPix;
            if ($descontoPix >= 100) {
                generateException('Parece que você tentou aplicar um  desconto Pix igual ou superior a 100%. Por favor, corrija o valor informado para conseguir salvar as configurações.');
            }
        }
        
        try {
            $params['debug'] = 'off';
              $gn_instance = getGerencianetApiInstance($params);
              createWebhook($gn_instance, $params);
        }catch(\Throwable $th) {
                generateException("Falha ao cadastrar WEBHOOK");
        }
        
    
}
function requiredOpenFinanceFields($params)
{
    $tests = [
        "Nome" => $params['nome'],
        "Documento" => $params['documento'],
        "Agência" => $params['agencia'],
        "Conta" => $params['conta']
    ];
    foreach ($tests as $message => $value) {
        verifyEmptyValue($value, $message);
    }
    try {
          $params['debug'] = 'off';
          $open_finance_instance = new OpenFinanceEfi($params);
          $open_finance_instance->updateConfigOpenFinance();
    }catch(\Throwable $th) {
            generateException($th->getMessage());       
    }
    
}
function testAuthentication($params)
{
    
    try {
        $gnIntegration = new GerencianetIntegration($params['clientIdProd'], $params['clientSecretProd'], $params['clientIdSandbox'], $params['clientSecretSandbox'], $params['sandbox'], $params['idConta']);

        $gnIntegration->testIntegration();
   
        
    }catch (\Throwable $th) {
        generateException("<strong>Credenciais inválidas</strong>. Por favor, verifique se as suas credencias estão corretas e tente novamente.");
    }
}
function isPixKeyValid($pixKey) {
    define('CPF_PATTERN', '/^[0-9]{11}$/');
    define('CNPJ_PATTERN', '/^[0-9]{14}$/');
    define('PHONE_PATTERN', '/^\+[1-9][0-9]\d{1,14}$/');
    define('EMAIL_PATTERN', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
    define('EVP_PATTERN', "/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/");

    if (preg_match(CPF_PATTERN, $pixKey)
        || preg_match(CNPJ_PATTERN, $pixKey)
        || preg_match(PHONE_PATTERN, $pixKey)
        || preg_match(EMAIL_PATTERN, $pixKey)
        || preg_match(EVP_PATTERN, $pixKey)) {
        return true;
    }
    return false;
}

function generateException($message)
{
    throw new \Exception($message);
}


