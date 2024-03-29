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
	"inicio" => "2022-01-01T00:00:00Z",
	"fim" => "2024-12-31T23:59:59Z",
	// "status" => "CONCLUIDA", // "ATIVA","CONCLUIDA", "REMOVIDA_PELO_USUARIO_RECEBEDOR", "REMOVIDA_PELO_PSP"
	// "cpf" => "12345678909", // Filter by payer's CPF. It cannot be used at the same time as the CNPJ.
	// "cnpj" => "12345678909", // Filter by payer's CNPJ. It cannot be used at the same time as the CPF.
	// "locationPresente" => true,
	// "loteCobVId" => "123456789",
	// "paginacao.paginaAtual" => 1,
	// "paginacao.itensPorPagina" => 10
];

try {
	$api = Gerencianet::getInstance($options);
	$response = $api->pixListDueCharges($params);

	print_r("<pre>" . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>");
} catch (GerencianetException $e) {
	print_r($e->code);
	print_r($e->error);
	print_r($e->errorDescription);
} catch (Exception $e) {
	print_r($e->getMessage());
}
