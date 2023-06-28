<?php
require_once __DIR__ . '/../../../../../../../init.php';
use WHMCS\Client;
use Illuminate\Database\Capsule\Manager as Capsule;



if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) != "xmlhttprequest") {
    exit("Invalid request");
}
function getClientCustomFields($clientId) {
    try {
        // Consulta os valores dos campos personalizados do cliente no banco de dados
        $customFields = Capsule::table('tblcustomfieldsvalues')
            ->join('tblcustomfields', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
            ->select('tblcustomfields.fieldname', 'tblcustomfieldsvalues.value')
            ->where('tblcustomfieldsvalues.fieldid', '>', 0)
            ->where('tblcustomfieldsvalues.relid', $clientId)
            ->get();

        // Cria um array com as informações dos campos personalizados
        $customFieldsArray = [];
        foreach ($customFields as $field) {
            $customFieldsArray[] = (object) [
                'fieldName' => $field->fieldname,
                'value' => $field->value
            ];
        }

        return $customFieldsArray;
    } catch (\Throwable $th) {
       
    }
}

    // Obtém os dados do cliente logado na sessão
    $clientId = $_SESSION['uid'];
    $client = new Client($clientId);


    // Obtém todos os campos personalizados do cliente
    $customFields = getClientCustomFields($clientId);
    $attributes = $client->getClientModel()->getAttributes();

    // Cria um array com todas as informações do cliente
    $clientData = [
      'firstName' => $attributes["firstname"],
      'lastName' => $attributes["lastname"],
      'email' => $attributes["email"],
      'address1' => $attributes["address1"],
      'address2' => $attributes["address2"],
      'city' => $attributes["city"],
      'state' => $attributes["state"],
      'postcode' => $attributes["postcode"],
      'country' => $attributes["country"],
      'phoneNumber' => $attributes["phonenumber"],
      'customFields' => $customFields
    ];

    // Retorna os dados do cliente como JSON
    echo json_encode($clientData);