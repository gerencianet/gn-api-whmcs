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

$params = [
	"id" => 0
];

$customer = [
	"name" => "Gorbadoc Oldbuck",
	"cpf" => "94271564656"
];

$body = [
	"payment" => [
		"banking_billet" => [
			"expire_at" => "2024-12-10",
			"customer" => $customer
		]
	]
];

try {
	$api = new Gerencianet($options);
	$response = $api->definePayMethod($params, $body);

	print_r("<pre>Second step:<br>" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
} catch (GerencianetException $e) {
	print_r($e->code);
	print_r($e->error);
	print_r($e->errorDescription);
} catch (Exception $e) {
	print_r($e->getMessage());
}
