<?php
require_once __DIR__ . '/../../../../../../../init.php';
use Illuminate\Database\Capsule\Manager as Capsule;



if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) != "xmlhttprequest") {
    exit("Invalid request");
}
header('Content-Type: application/json');
$invoiceId = $_GET['id'];

$OF = Capsule::table('tblefiopenfinance')
    ->where('invoiceid', $invoiceId)
    ->whereRaw('LENGTH(`e2eid`) > 0')
    ->first();
if ($OF) {
    $clientData = ["paid"=> true];
    echo json_encode($clientData);
}else{
    $clientData = ["paid"=> false];
    echo json_encode($clientData); 
}
    