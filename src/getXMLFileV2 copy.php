<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use horstoeko\zugferd\codelists\ZugferdPaymentMeans;

require dirname(__FILE__) . "/../vendor/autoload.php";

$dt_RECHFormat_Nr = $_POST['dt_RECHFormat_Nr'];
$dd_Invoice_Date = $_POST['dd_Invoice_Date'];
$dt_ACC_Name = $_POST['dt_ACC_Name'];
$dt_ACC_Street = $_POST['dt_ACC_Street'];
$dt_ACC_Ort = $_POST['dt_ACC_Ort'];
$dz_ACC_PLZ = $_POST['dz_ACC_PLZ'];
$dt_ACC_UstID = $_POST['dt_ACC_UstID'];
$dt_Land = $_POST['dt_Land'];
$dt_ACC_Tele = $_POST['dt_ACC_Tele'];
$dt_ACC_Email = $_POST['dt_ACC_Email'];
$dz_Seller_Order_reference = $_POST['dz_Seller_Order_reference'];
$dd_Delivery_date = $_POST['dd_Delivery_date'];

$dz_Description_payment_first = $_POST['dz_Description_payment_first'];

$dt_Name_recipien = $_POST['dt_Name_recipien'];
$dt_recipien_address = $_POST['dt_recipien_address'];
$dt_recipien_PLZ = $_POST['dt_recipien_PLZ'];
$dt_recipien_city = $_POST['dt_recipien_city'];
$dz_Description_payment = $_POST['dz_Description_payment'];
$dt_recipien_tele =  $_POST['dt_recipien_tele'];
$dt_recipien_Email =  $_POST['dt_recipien_Email'];
$dt_additional_address =  $_POST['dt_additional_address'];

$dFzu_TotalPrice = $_POST['dFzu_TotalPrice'];
$dFzu_SumBeforeTax = $_POST['dFzu_SumBeforeTax'];
$dFzu_SumRabatt = !$_POST['dFzu_SumRabatt'] ? 0.0 : $_POST['dFzu_SumRabatt'];
$dz_Amount_already_paid = !$_POST['dz_Amount_already_paid'] ? 0.0 : $_POST['dz_Amount_already_paid'];
$dFzu_TotalTax = $_POST['dFzu_TotalTax'];
$dt_CodeInvoice = $_POST['dt_CodeInvoice'];
$dz_Tax = $_POST['dz_Tax'];
$dz_Purchaser_Order_reference = $_POST['dz_Purchaser_Order_reference'];
$dd_due_date_Invoice_amount = $_POST['dd_due_date_Invoice_amount'];
$dFzu_Price_Discount_percent = !$_POST['dFzu_Price_Discount_percent'] ? 0.0 : $_POST['dFzu_Price_Discount_percent'];

$dataJSON = str_replace("'", "",$_POST['dt_Json']);

$dt_Json = json_decode($dataJSON, true);

$originalDate = $dd_Invoice_Date;
$newDate = date("Ymd", strtotime($originalDate));

$deliveryDate = $dd_Delivery_date;
$newDateDelivery = date("Ymd", strtotime($deliveryDate));

$dueDate = $dd_due_date_Invoice_amount;
$newDueDate = date("Ymd", strtotime($dueDate));

$document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_XRECHNUNG_3);
$document
    ->setDocumentInformation($dt_RECHFormat_Nr, "380", \DateTime::createFromFormat("Ymd", $newDate), "EUR")
    ->addDocumentNote($dz_Description_payment_first)
    ->addDocumentNote($dt_ACC_Name . PHP_EOL . $dt_ACC_Street . PHP_EOL . $dz_ACC_PLZ." ". $dt_ACC_Ort . PHP_EOL . $dt_Land)
    ->setDocumentSupplyChainEvent(\DateTime::createFromFormat('Ymd', $newDateDelivery))
    ->addDocumentPaymentMean(ZugferdPaymentMeans::UNTDID_4461_58, null, null, null, null, null, $dt_CodeInvoice, null, null, null)
    ->setDocumentSeller($dt_ACC_Name, null)
    ->addDocumentSellerTaxRegistration("VA", $dt_ACC_UstID)
    ->setDocumentSellerAddress($dt_ACC_Street, "", "", $dz_ACC_PLZ , $dt_ACC_Ort, $dt_Land)
    ->setDocumentSellerContact($dt_ACC_Name, "", $dt_ACC_Tele, null,$dt_ACC_Email)
    ->setDocumentSellerOrderReferencedDocument($dz_Seller_Order_reference)
    ->setDocumentBuyer($dt_Name_recipien, null)
    ->setDocumentBuyerReference($dz_Purchaser_Order_reference)
    ->setDocumentBuyerAddress($dt_recipien_address, $dt_additional_address, "", $dt_recipien_PLZ, $dt_recipien_city, $dt_Land)
    ->setDocumentBuyerContact($dt_Name_recipien, "", "", $dt_recipien_tele, "", $dt_recipien_Email)
	->addDocumentTax("S", "VAT", $dFzu_SumBeforeTax, $dFzu_TotalTax, $dz_Tax)
    ->setDocumentSummation($dFzu_TotalPrice, $dFzu_TotalPrice , $dFzu_SumBeforeTax, 0.0, 0.0, $dFzu_SumBeforeTax, $dFzu_TotalTax, null, $dz_Amount_already_paid)
    ->addDocumentPaymentTerm($dz_Description_payment);

	foreach($dt_Json as $key => $value){
		$document
		->addNewPosition($value['pos'])
		->setDocumentPositionProductDetails($value['Bezeichung'], "", "")
		->setDocumentPositionGrossPrice($value['Netto'])
		->setDocumentPositionNetPrice($value['Netto'])
		->setDocumentPositionQuantity($value['Menge'], $value['Einheit'])
		->addDocumentPositionTax('S', 'VAT', $dz_Tax)
		->setDocumentPositionLineSummation($value['Netto']);
	}

header('Content-Type: application/xml');
header('Content-Length: '.strlen( $document ));
header('Content-disposition: inline; filename="data.xml"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
echo $document;
