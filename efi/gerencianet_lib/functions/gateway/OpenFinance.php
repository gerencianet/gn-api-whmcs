<?PHP
use Gerencianet\Gerencianet;
use Gerencianet\Exception\GerencianetException;

class OpenFinanceEfi {
    private array $params;

    public function __construct(array $params) {
        $this->params = $params;
    
    }
    public function createOpenFinance(array $params) {

        
    }

    public function updateConfigOpenFinance()
    {
        
        $api_instance = new Gerencianet::getInstance($this->getOptionsApiInstance());
        $requestBody = $this->getBodyConfigOpenFinance();
        try {
            $response = $api_instance->ofConfigUpdate($params = [],$requestBody); 
        } catch (GerencianetException $e) {
            throw new \Exception($e->errorDescription);
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
    }

    private function getBodyConfigOpenFinance()
    {
        $moduleName  = $this->params['paymentmethod'];
        $systemUrl   = $this->params['systemurl'];
        $callbackUrl = $systemUrl . 'modules/gateways/callback/' . $moduleName . '.php';
        $redirectUrl = $systemUrl . 'clientarea.php?action=invoices';

        if ($this->params['mtls'] == 'on') {
           $webhookSecurity =["type"=>"mtls" ];
        }else {
            $hash = hash('sha256',$this->params['clientIdProd']);
            $webhookSecurity =["type "=>"hmac","hash"=>$hash ];
        }
        $config = [
            'webhookUrl' => $callbackUrl,
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