<?php
/**
 * Gerencianet Integration Class.
 */

include_once('gerencianet/autoload.php');
use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;


class GerencianetIntegration {

	public $client_id_production;
	public $client_secret_production;
	public $client_id_development;
	public $client_secret_development;
	public $sandbox;
	public $payee_code;

	public function __construct($clientIdProduction,$clientSecretProduction,$clientIdDevelopment,$clientSecretDevelopment,$sandbox,$payeeCode) {
		$this->client_id_production = $clientIdProduction;
		$this->client_secret_production = $clientSecretProduction;
		$this->client_id_development = $clientIdDevelopment;
		$this->client_secret_development = $clientSecretDevelopment;
		$this->sandbox = $sandbox;
		$this->payee_code = $payeeCode;
	}

	public function get_gn_api_credentials() {

		if ($this->sandbox == "on") {
			$gn_credentials_options = array (
			  'client_id' =>  $this->client_id_development,
			  'client_secret' => $this->client_secret_development,
			  'sandbox' => true
			);

		} else {
			$gn_credentials_options = array (
			  'client_id' =>  $this->client_id_production,
			  'client_secret' => $this->client_secret_production,
			  'sandbox' => false
			);

		}

		return $gn_credentials_options;
	}

	public function create_charge($items, $order_id = null, $notification_url = '', $shipping = null) {

		$options = GerencianetIntegration::get_gn_api_credentials();

		$metadata = array (
		    'custom_id' => strval($order_id),
		    'notification_url' => $notification_url
		);

		if ($shipping) {
			$body = array (
			    'items' => $items,
	    		'shippings' => $shipping,
	    		'metadata' => $metadata
			);
		} else {
			$body = array (
			    'items' => $items,
	    		'metadata' => $metadata
			);
		}

		try {
		    $api = new Gerencianet($options);
		    $charge = $api->createCharge(array(), $body);

		    return GerencianetIntegration::result_api($charge, true);

		} catch (GerencianetException $e) {
		    $errorResponse = array(
		        "code" => $e->code,
		        "error" => $e->error,
		        "message" => $e->errorDescription,
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		} catch (Exception $e) {
		    $errorResponse = array(
		        "message" => $e->getMessage(),
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		}
	}

	public function detail_charge($charge_id) {

		$options = GerencianetIntegration::get_gn_api_credentials();

		$params = array("id" => $charge_id);

		try {
		    $api = new Gerencianet($options);
		    $charge = $api->detailCharge($params, array());

		    return GerencianetIntegration::result_api($charge, true);

		} catch (GerencianetException $e) {
		    $errorResponse = array(
		        "code" => $e->code,
		        "error" => $e->error,
		        "message" => $e->errorDescription,
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		} catch (Exception $e) {
		    $errorResponse = array(
		        "message" => $e->getMessage(),
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		}
	}

	public function cancel_charge($charge_id)
	{
		$options = GerencianetIntegration::get_gn_api_credentials();
		$params = array ('id' => $charge_id);

		try {
		    $api = new Gerencianet($options);
		    $charge = $api->cancelCharge($params, array());

		    return GerencianetIntegration::result_api($charge, true);

		} catch (GerencianetException $e) {
		    $errorResponse = array(
		        "code" => $e->code,
		        "error" => $e->error,
		        "message" => $e->errorDescription,
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		    
		} catch (Exception $e) {
		    $errorResponse = array(
		        "message" => $e->getMessage(),
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		}
	}

	public function pay_billet($charge_id, $expirationDate, $customer, $instructions, $discount=false) {
		$options = GerencianetIntegration::get_gn_api_credentials();
		$params = array ('id' => $charge_id);
		
		if ($discount) {
			if(sizeof($instructions) == 0)
			{
				$banking_billet = array (
		            'expire_at' => $expirationDate,
		            'customer' => $customer,
		            'discount' => $discount
		      	);
			} else {
				$banking_billet = array (
		            'expire_at' => $expirationDate,
		            'customer' => $customer,
		            'instructions' => $instructions,
		            'discount' => $discount
		      	);
			}

		} 

		else 
		{
			if(sizeof($instructions) == 0)
			{
				$banking_billet = array (
		            'expire_at' => $expirationDate,
		            'customer' => $customer
		      	);
			} else {
				$banking_billet = array (
		            'expire_at' => $expirationDate,
		            'customer' => $customer,
		            'instructions' => $instructions
		      	);
			}
		}

		$body = array (
		    'payment' => array (
		        'banking_billet' => $banking_billet
		    )
		);

		try {
		    $api = new Gerencianet($options);
		    $charge = $api->payCharge($params, $body);

		    return GerencianetIntegration::result_api($charge, true);
		} catch (GerencianetException $e) {
		    $errorResponse = array(
		        "code" => $e->code,
		        "error" => $e->error,
		        "message" => $e->errorDescription,
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		} catch (Exception $e) {
		    $errorResponse = array(
		        "message" => $e->getMessage(),
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		}
	}


	public function notificationCheck($notificationToken) {

		$options = GerencianetIntegration::get_gn_api_credentials();

		$params = array (
		  	'token' => $notificationToken
		);

		try {
		    $api = new Gerencianet($options);
		    $notification = $api->getNotification($params, array());

	 		return GerencianetIntegration::result_api($notification, true);
		} catch (GerencianetException $e) {
		    $errorResponse = array(
		        "message" => "Error retrieving notification: " . $notificationToken,
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		} catch (Exception $e) {
		    $errorResponse = array(
		        "message" => "Error retrieving notification: " . $notificationToken,
		    );

		    return GerencianetIntegration::result_api($errorResponse, false);
		}
	}

	public function result_api($result, $success) {
		if ($success) {
			return json_encode($result);
		} else {
			$messageAdmin = '';
			if (isset($result['message']['property'])) {
				$property = explode("/",$result['message']['property']);
				$propertyAux = $property;
				array_pop($propertyAux);

				if(end($propertyAux) == 'instructions')
					$propertyName = end($propertyAux) . '/' . end($property);
				else
					$propertyName = end($property);
			} else {
				$propertyName="";
			}
			
			if (isset($result['code'])) {
				if (isset($result['message']) && $propertyName=="") {
					$response = $this->getErrorMessage(intval($result['code']), $result['message']);
					$messageShow = $response['message'];
					$messageAdmin = $response['messageAdmin'];
 				} else {
					$response = $this->getErrorMessage(intval($result['code']), $propertyName);
					$messageShow = $response['message'];
					$messageAdmin = $response['messageAdmin'];
				}
			} else {
				if (isset($result['message'])) {
					$messageShow = $result['message'];
				} else {
					$response = $this->getErrorMessage(1, $propertyName);
					$messageShow = $response['message'];
					$messageAdmin = $response['messageAdmin'];
				}
			}

			if($messageAdmin == '')
				$messageAdmin = $messageShow;
			$errorResponse = array(
				"code" => 0,
		        "message" => $messageShow,
		        "messageAdmin" => $messageAdmin
		    );
			return json_encode($errorResponse);
		}
	}

	public function getErrorMessage($error_code, $property) {
		$messageErrorDefault = 'Ocorreu um erro ao tentar realizar a sua requisição. Entre em contato com o proprietário do WHMCS.';
		$messageAdmin = '';
		switch($error_code) {
			case 3500000:
				$message = 'Erro interno do servidor.';
				break;
			case 3500001:
				$message = $messageErrorDefault;
				break;
			case 3500002:
				$message = $messageErrorDefault;
				break;
			case 3500007:
				$message = 'O tipo de pagamento informado não está disponível.';
				break;
			case 3500008:
				$message = 'Requisição não autorizada.';
				break;
			case 3500010:
				$message = $messageErrorDefault;
				break;
			case 3500016:
				$message = 'A transação deve possuir um cliente antes de ser paga.';
				break;	
			case 3500021:
				$message = 'Não é permitido parcelamento para assinaturas.';
				break;
			case 3500030:
				$message = 'Esta transação já possui uma forma de pagamento definida.';
				break;
			case 3500034:
				if($property == 'items' || $property == 'customer')
				{
					$message = $messageErrorDefault;
					$messageAdmin = 'O campo ' . $this->getFieldName($property) . ' não está preenchido corretamente: ';
				}
				elseif(strpos($property, 'instructions/') !== false)
				{
					$message = $messageErrorDefault;
					$messageAdmin = $this->getFieldName($property);
				}
				else{
					$message = 'O campo ' . $this->getFieldName($property) . ' não está preenchido corretamente: ';
				}
				break;
			case 3500036:
				$message = 'A forma de pagamento da transação não é boleto bancário.';
				break;
			case 3500042:
				$message = $messageErrorDefault;
				$messageAdmin = 'O parâmetro [data] deve ser um JSON.';
				break;
			case 3500044:
				$message = 'A transação não pode ser paga. Entre em contato com o vendedor.';
				break;
			case 4600002:
				$message = $messageErrorDefault;
				break;
			case 4600012:
				$message = 'Ocorreu um erro ao tentar realizar o pagamento: ' . $property;
				break;
			case 4600022:
				$message = $messageErrorDefault;
				break;
			case 4600026:
				$message = 'cpf inválido';
				break;
			case 4600029:
				$message = 'pedido já existe';
				break;
			case 4600032:
				$message = $messageErrorDefault;
				break;
			case 4600035:
				$message = 'Serviço indisponível para a conta. Por favor, solicite que o recebedor entre em contato com o suporte Gerencianet.';
				break;
			case 4600037:
				$message = 'O valor da emissão é superior ao limite operacional da conta. Por favor, solicite que o recebedor entre em contato com o suporte Gerencianet.';
				break;
			case 4600073:
				$message = 'O telefone informado não é válido.';
				break;
			case 4600111:
				$message = 'valor de cada parcela deve ser igual ou maior que R$5,00';
				break;
			case 4600142:
				$message = 'Transação não processada por conter incoerência nos dados cadastrais.';
				break;
			case 4600148:
				$message = 'já existe um pagamento cadastrado para este identificador.';
				break;
			case 4600196:
				$message = $messageErrorDefault;
				break;
			case 4600204:
				$message = 'cpf deve ter 11 dígitos';
				break;
			case 4600209:
				$message = 'Limite de emissões diárias excedido. Por favor, solicite que o recebedor entre em contato com o suporte Gerencianet.';
				break;
			case 4600210:
				$message = 'não é possível emitir três emissões idênticas. Por favor, entre em contato com nosso suporte para orientações sobre o uso correto dos serviços Gerencianet.';
				break;
			case 4600212:
				$message = 'Número de telefone já associado a outro CPF. Não é possível cadastrar o mesmo telefone para mais de um CPF.';
				break;
			case 4600222:
				$message = 'Recebedor e cliente não podem ser a mesma pessoa.';
				break;
			case 4600219:
				$message = 'Ocorreu um erro ao validar seus dados: ' . $property;
				break;
			case 4600224:
				$message = $messageErrorDefault;
				break;
			case 4600254:
				$message = 'identificador da recorrência não foi encontrado';
				break;
			case 4600257:
				$message = 'pagamento recorrente já executado';
				break;
			case 4600329:
				$message = 'código de segurança deve ter três digitos';
				break;
			case 4699999:
				$message = 'falha inesperada';
				break;
			default:
				$message = $messageErrorDefault;
				break;
		}
		if($messageAdmin == null || $messageAdmin == '') 
			$messageAdmin = $message;
		return array("message" => $message, "messageAdmin" => $messageAdmin);
	}
	
	public function getFieldName($name) {
		if(strpos($name, 'instructions/') !== true)
		{
			$property = explode('/', $name);
			$name = $property[0];
			$id   = $property[1] + 1;
		}

		switch($name) {
			case "neighborhood":
				return 'Bairro';
				break;
			case "street":
				return 'Endereço';
				break;
			case "complement":
				return 'Complemento';
				break;
			case "number":
				return 'Número';
				break;
			case "city":
				return 'Cidade';
				break;
			case "zipcode":
				return 'CEP';
				break;
			case "name":
				return 'Nome';
				break;
			case "cpf":
				return 'CPF';
				break;
			case "phone_number":
				return 'Telefone de contato';
				break;
			case "email":
				return 'Email';
				break;
			case "cnpj":
				return 'CNPJ';
				break;
			case "corporate_name":
				return 'Razão Social';
				break;
			case "installments":
				return 'Quantidade de Parcelas';
				break;
			case "birth":
				return 'Data de nascimento';
				break;
			case 'customer':
				return 'Dados do cliente';
				break;
			case 'items':
				return 'Itens';
				break;
			case 'instructions':
				return 'A instrução nº ' . $id . ' do boleto ultrapassa o tamanho limite de 90 caracteres.';
				break;
			default:
				return '';
				break;
		}
	}

}