<?php
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use horstoeko\zugferd\codelists\ZugferdPaymentMeans;

require dirname(__FILE__) . "/../vendor/autoload.php";

$documentNo = $_POST['documentNo'];
$documentTypeCode =  $_POST['documentTypeCode'];
$documentDate = $_POST['documentDate'];
$documentNote = $_POST['documentNote'];

$invoiceCurrency = $_POST['invoiceCurrency'];
$sellerName = $_POST['sellerName'];
$sellerAddress = $_POST['sellerAddress'];
$sellerPostcode = $_POST['sellerPostcode'];
$sellerCity = $_POST['sellerCity'];
$sellerCountry = $_POST['sellerCountry'];


$sellerContactPersonName = $_POST['sellerContactPersonName'];
$sellerContactDepartmentName = $_POST['sellerContactDepartmentName'];
$sellerContactPhoneNo = $_POST['sellerContactPhoneNo'];
$sellerContactFaxNo = $_POST['sellerContactFaxNo'];
$sellerContactEmail= $_POST['sellerContactEmail'];


$buyerName = $_POST['buyerName'];
$buyerAddress= $_POST['buyerAddress'];
$buyerCity= $_POST['buyerCity'];
$buyerPostcode= $_POST['buyerPostcode'];
$buyerCountry= $_POST['buyerCountry'];
$paymentTerm= $_POST['paymentTerm'];
$taxCategoryCode= $_POST['taxCategoryCode'];
$taxTypeCode= $_POST['taxTypeCode'];
$taxBasisAmount= $_POST['taxBasisAmount'];
$taxCalculatedAmount= $_POST['taxCalculatedAmount'];
$taxRateApplicablePercent= $_POST['taxRateApplicablePercent'];

$productName= $_POST['productName'];
$productDescription= $_POST['productDescription'];
$productGrossPrice= $_POST['productGrossPrice'];
$productNetPrice= $_POST['productNetPrice'];
$productPositionQuantity= $_POST['productPositionQuantity'];
$productPositionUnitCode= $_POST['productPositionUnitCode'];
$productTaxCategoryCode= $_POST['productTaxCategoryCode'];
$productTaxTypeCode= $_POST['productTaxTypeCode'];
$productTaxRateApplicablePercent= $_POST['productTaxRateApplicablePercent'];
$productPositionLineSummation= $_POST['productPositionLineSummation'];

$document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_XRECHNUNG_3);
$document
    ->setDocumentInformation($documentNo, $documentTypeCode, \DateTime::createFromFormat("Ymd", $documentDate), $invoiceCurrency)
    ->addDocumentNote($documentNote)
    ->setDocumentSeller($sellerName)
    ->setDocumentSellerAddress($sellerAddress, "", "", $sellerPostcode, $sellerCity, $sellerCountry)
    ->setDocumentSellerContact($sellerContactPersonName, $sellerContactDepartmentName, $sellerContactPhoneNo, $sellerContactFaxNo,$sellerContactEmail)
    ->setDocumentBuyer($buyerName, "")
    ->setDocumentBuyerAddress($buyerAddress, "", "", $buyerPostcode, $buyerCity, $buyerCountry)
    ->addDocumentTax($taxCategoryCode, $taxTypeCode, $taxBasisAmount, $taxCalculatedAmount, $taxRateApplicablePercent)
    ->addDocumentPaymentTerm($paymentTerm)
    ->addNewPosition("1")
    ->setDocumentPositionProductDetails($productName, $productDescription, "")
    ->setDocumentPositionGrossPrice($productGrossPrice)
    ->setDocumentPositionNetPrice($productNetPrice)
    ->setDocumentPositionQuantity($productPositionQuantity, $productPositionUnitCode)
    ->addDocumentPositionTax($productTaxCategoryCode, $productTaxTypeCode, $productTaxRateApplicablePercent)
    ->setDocumentPositionLineSummation($productPositionLineSummation);

header('Content-Type: application/xml');
header('Content-Length: '.strlen( $document ));
header('Content-disposition: inline; filename="data.xml"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
echo $document;
