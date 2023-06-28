<?PHP

use Gerencianet\Gerencianet;

use Gerencianet\Exception\GerencianetException;

use Illuminate\Database\Capsule\Manager as Capsule;


class OpenFinanceEfi {

    private  $params;



    public function __construct( $params) {
        $this->params = $params;
    }

    
    /**
     * Start an Open Finance operation
     * 
     * @param \Gerencianet\Endpoints $api_instance Gerencianet API Instance
     * @param array $gatewayParams Payment Gateway Module Parameters
     * 
     * @return array Generated Open Finance Charge
     */
    function startOFPayment($api_instance, $gatewayParams)
    {

        $pagador = [
            "idParticipante" => $gatewayParams['paramsOF']['favoredbankOF'],
            "cpf" => str_replace(array('.','-','/'), "", $gatewayParams['paramsOF']['favoredDocumentOF'])
        ];


        if ($gatewayParams['paramsOF']['favoredPJDocumentOF'] != "") 
            $pagador["cnpj"] = str_replace(array('.','-','/'), "", $gatewayParams['paramsOF']['favoredPJDocumentOF']);

        //Open Finance Body
        $body = [
            "pagador" => $pagador,
            "favorecido" => [
                "contaBanco" => [
                    "codigoBanco" => "09089356",
                    'nome'        => $gatewayParams['nome'],
                    'documento'   => $gatewayParams['documento'],
                    'conta'       => $gatewayParams['conta'],
                    'tipoConta'   => $gatewayParams['tipoConta'],
                    'agencia'     => $gatewayParams['agencia'],
                ],
            ],
            "valor" => strval($gatewayParams["amount"]),
            "idProprio" => strval($gatewayParams['invoiceid']),
        ];
        
        try {
            $responseData = $api_instance->ofStartPixPayment($params=[], $body);
            //showException('Retorno', $responseData);
            return $responseData;
        } catch (GerencianetException $e) {
            showException('Gerencianet Exception', array($e));
        } catch (Exception $e) {
            showException('Exception', array($e));
        }
    }


    function validateRequiredParamsOF($gatewayParams)
    {
        

        $requiredParams = array(
            'clientIdProd'        => $gatewayParams['clientIdProd'],
            'clientSecretProd'    => $gatewayParams['clientSecretProd'],
            'clientIdSandbox'     => $gatewayParams['clientIdSandbox'],
            'clientSecretSandbox' => $gatewayParams['clientSecretSandbox'],
            'nome'                => $gatewayParams['nome'],
            'documento'           => $gatewayParams['documento'],
            'conta'               => $gatewayParams['conta'],
            'tipoConta'           => $gatewayParams['tipoConta'],
            'agencia'             => $gatewayParams['agencia'],
            'invoiceID'           => $gatewayParams['invoiceid'],
        );


        $errors = array();

        foreach ($requiredParams as $key => $value) {
            if (empty($value)) {
                $errors[] = "$key é um campo obrigatório. Verificar as configurações do Portal de Pagamento.";
            };
        };

        if (!empty($errors)) {
            showException('Exception', $errors);
        }
    }



    public function updateConfigOpenFinance()

    {

        

        $api_instance = new Gerencianet($this->getOptionsApiInstance());

        $requestBody = $this->getBodyConfigOpenFinance();
        $logFile = __DIR__ . '/log.txt'; // caminho completo para o arquivo de log



        $logString = date('Y-m-d H:i:s') . ' - ' . json_encode($requestBody) . "\n"; // string formatada com a data atual e os dados do array em formato JSON

        // abre o arquivo em modo de escrita, acrescentando os dados ao final do arquivo, se ele já existir
        $fileHandle = fopen($logFile, 'a');
        if ($fileHandle === false) {
            die('Não foi possível abrir o arquivo de log para escrita');
        }

        // escreve os dados no arquivo e fecha o handle
        fwrite($fileHandle, $logString);
        fclose($fileHandle);


        try {

           $response = $api_instance->ofConfigUpdate($params = [],$requestBody); 

        } catch (GerencianetException $e) {

            throw new \Exception($e->errorDescription);

        } catch (Exception $e) {

            throw new \Exception($e->getMessage());

        }

        

    }


    /**
     * Create table for store Open Finance Charge Infos
     */
    static function createEfiOFTable()
    {
        $tableName = 'tblefiopenfinance';

        if(!hasTable($tableName)) {
            $callback = function ($table) {
                #$table->increments('id');
                $table->integer('invoiceid')->unique()->primary();
                $table->string('identificadorPagamento')->unique();
                $table->string('e2eid');
            };

            createTable($tableName, $callback);
        }
    }


    public function storePaymentIdentifier($paymentID, $invoiceID) {
      

        try {
            
            $paymentOF = Capsule::table('tblefiopenfinance')
                ->select('invoiceID')
                ->where('invoiceID', '=', $invoiceID)
                ->get();

            if ($paymentOF->count() > 0) {
                $paymentUpdate = Capsule::table('tblefiopenfinance')
                    ->where('invoiceID', '=', $invoiceID)
                    ->update(['identificadorPagamento' => $paymentID]);
            } else {
                $info = [
                    'invoiceID' => $invoiceID,
                    'identificadorPagamento' => $paymentID,
                    'e2eid' => "",
                ];
                insert('tblefiopenfinance', $info);
            }
    
        } catch (\Throwable $th) {
            $logFile = __DIR__ . '/log.txt'; // caminho completo para o arquivo de log
        
            $logString = date('Y-m-d H:i:s') . ' - ' . json_encode(array("Error updating OF" => $th->getMessage())) . "\n"; // string formatada com a data atual e os dados do array em formato JSON
    
            // abre o arquivo em modo de escrita, acrescentando os dados ao final do arquivo, se ele já existir
            $fileHandle = fopen($logFile, 'a');
            if ($fileHandle === false) {
                die('Não foi possível abrir o arquivo de log para escrita');
            }
    
            // escreve os dados no arquivo e fecha o handle
            fwrite($fileHandle, $logString);
            fclose($fileHandle);
        }
    }




    private function getBodyConfigOpenFinance()

    {

        $moduleName  = $this->params['paymentmethod'];

        $systemUrl   = $this->params['systemurl'];

        $callbackUrl = $systemUrl . 'modules/gateways/callback/efi/openFinance.php';

        $redirectUrl = $systemUrl . 'modules/gateways/efi/gerencianet_lib/html/redirect.php';



        if ($this->params['mtls'] == 'on') {

           $webhookSecurity =["type"=>"mtls" ];

        }else {

            $hash = hash('sha256',$this->params['clientIdProd']);

            $webhookSecurity =["type"=>"hmac","hash"=>$hash ];

        }

        $config = [

            'webhookURL' => $callbackUrl,

            'redirectURL' => $redirectUrl,

            'webhookSecurity' => $webhookSecurity

        ];

        return $config;

        

    }

    private function getOptionsApiInstance()

    {

        // Pix Parameters

        $certificate = $this->params['pixCert'];



        // Boolean Parameters

        $mtls    = ($this->params['mtls'] == 'on');

        $debug   = ($this->params['debug'] == 'on');

        $sandbox = ($this->params['sandbox'] == 'on');



        // Client Authentication Parameters

        $clientIdSandbox     = $this->params['clientIdSandbox'];

        $clientIdProd        = $this->params['clientIdProd'];

        $clientSecretSandbox = $this->params['clientSecretSandbox'];

        $clientSecretProd    = $this->params['clientSecretProd'];

        $options =  array(

            'client_id' => $sandbox ? $clientIdSandbox : $clientIdProd,

            'client_secret' => $sandbox ? $clientSecretSandbox : $clientSecretProd,

            'certificate' => $certificate,

            'sandbox' => $sandbox,

            'debug' => $debug,

            'headers' => [

                'x-skip-mtls-checking' => $mtls ? 'false' : 'true' // Needs to be string

            ]

        );



        return $options;

    }

}