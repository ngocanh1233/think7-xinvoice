<?php

use horstoeko\zugferd\ZugferdDocumentPdfMerger;

require dirname(__FILE__) . "/../vendor/autoload.php";
$existingXml =$_FILES['dm_XMLFile']["tmp_name"];
$existingPdf =$_FILES['dm_File']["tmp_name"];
$mergeToPdf = dirname(__FILE__) . "/result.pdf";

if (!file_exists($existingXml) || !file_exists($existingPdf)) {
    throw new \Exception("XML and/or PDF does not exist");
}

$pdfBuilder = (new ZugferdDocumentPdfMerger($existingXml, $existingPdf))->generateDocument()->downloadString("merged.pdf");

header('Content-Type: application/pdf');
header('Content-Length: '.strlen( $pdfBuilder ));
header('Content-disposition: inline; filename="xInvoice.pdf"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
echo $pdfBuilder;