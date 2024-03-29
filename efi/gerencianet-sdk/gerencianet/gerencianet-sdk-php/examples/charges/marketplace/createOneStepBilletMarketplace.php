<?php

if (file_exists($autoload = realpath(__DIR__ . "/../../../vendor/autoload.php"))) {
	require_once $autoload;
} else {
	print_r("Autoload not found or on path <code>$autoload</code>");
}

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;

if (file_exists($options = realpath(__DIR__ . "/../../credentials/options.php"))) {
	require_once $options;
}

$item_1 = [
	"name" => "Product 1",
	"amount" => 1,
	"value" => 1500,
	"marketplace" => [
		"mode" => 2, // "mode = 1", rate will be discounted from issuing account only. "mode = 2", rate will be discounted proportionally to the percentage defined for all accounts
		"repasses" => [
			[
				"payee_code" => "Enter_Destination_Account_Identifier_Here", // Gerencianet account identifier (transfer 1)
				"percentage" => 2500 // transfer percentage (2500 = 25%)
			],
			[
				"payee_code" => "Enter_Destination_Account_Identifier_Here", // Gerencianet account identifier (transfer 2)
				"percentage" => 1500 // transfer percentage	(1500 = 15%)
			]
		]
	]
];

$items = [
	$item_1
];

$shippings = [
	[
		"name" => "Shipping to City",
		"value" => 1200,
		"payee_code" => "Enter_Destination_Account_Identifier_Here", // Gerencianet account identifier (transfer shipping)
	]
];

$metadata = [
	"custom_id" => "Order_0001",
	"notification_url" => "https://your-domain.com.br/notification/"
];

$customer = [
	"name" => "Gorbadoc Oldbuck",
	"cpf" => "94271564656",
	"phone_number" => "5144916523",
];

$discount = [
	"type" => "currency", // "currency", "percentage"
	"value" => 599
];

$conditional_discount = [
	"type" => "percentage", // "currency", "percentage"
	"value" => 500,
	"until_date" => "2024-12-08"
];

$configurations = [
	"fine" => 200,
	"interest" => 33
];

$bankingBillet = [
	"expire_at" => "2024-12-10",
	"message" => "This is a space\n of up to 80 characters\n to tell\n your client something",
	"customer" => $customer,
	"discount" => $discount,
	"conditional_discount" => $conditional_discount
];

$payment = [
	"banking_billet" => $bankingBillet
];

$body = [
	"items" => $items,
	"metadata" => $metadata,
	"shippings" => $shippings,
	"payment" => $payment
];

try {
	$api = new Gerencianet($options);
	$response = $api->createOneStepCharge($params = [], $body);

	print_r("<pre>" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
} catch (GerencianetException $e) {
	print_r($e->code);
	print_r($e->error);
	print_r($e->errorDescription);
} catch (Exception $e) {
	print_r($e->getMessage());
}
