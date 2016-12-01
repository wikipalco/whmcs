<?php
if(file_exists("../../../dbconnect.php"))
{
	include("../../../dbconnect.php");
	
}else
{
	include("../../../init.php");
}
include('../../../includes/functions.php');
include('../../../includes/gatewayfunctions.php');
include('../../../includes/invoicefunctions.php');

$gatewaymodule = 'wikipal'; 

$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY['type']) die('Module Not Activated'); 

$invoiceid = $_GET['invoiceid'];
$transid = $_POST['authority'];
$amount = $_GET['amount'];
$au = $_GET['au'];

$invoiceid = checkCbInvoiceID($invoiceid,$GATEWAY['name']); 

if(isset($_POST['authority']))
{	
	checkCbTransID($transid); 

	if($GATEWAY['Currencies']=='Rial')    
	$amount = $amount/10;

	$MerchantID 			= $GATEWAY['merchantID'];
	$Price 					= $amount;
	$Authority 				= $_POST['authority'];
	$InvoiceNumber 			= $_POST['InvoiceNumber'];

	if ($_POST['status'] == 1) {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://gatepay.co/webservice/paymentVerify.php');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, "MerchantID=$MerchantID&Price=$Price&Authority=$Authority");
		curl_setopt($curl, CURLOPT_TIMEOUT, 400);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($curl));
		curl_close($curl);

		if ($result->Status == 100) {
			addInvoicePayment($invoiceid,$transid,$amount,$fee,$gatewaymodule); 
			logTransaction($GATEWAY['name'],$_GET,'Successful');
		} else {
			logTransaction($GATEWAY['name'],$_GET,'Unsuccessful');
		}

	} else {
		logTransaction($GATEWAY['name'],$_GET,'Unsuccessful');
	}
}
else 
{
	logTransaction($GATEWAY['name'],$_GET,'Unsuccessful'); 
}
header('Location: '.$CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceid);
?>