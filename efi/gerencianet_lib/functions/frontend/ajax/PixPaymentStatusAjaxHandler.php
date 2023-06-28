<?php
require_once __DIR__ . '/../../../../../../../init.php';
use Illuminate\Database\Capsule\Manager as Capsule;



if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) != "xmlhttprequest") {
    exit("Invalid request");
}
$invoiceId = $_GET['id'];

$pix = Capsule::table('tblgerencianetpix')
    ->where('invoiceid', $invoiceId)
    ->whereRaw('LENGTH(`e2eid`) > 0')
    ->first();
if ($pix) {
    $clientData = ["paid"=> true];
    echo json_encode($clientData);
}else{
    $clientData = ["paid"=> false];
    echo json_encode($clientData); 
}
    