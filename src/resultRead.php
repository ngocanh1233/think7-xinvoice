<?php
header('Content-Type: application/json');
use horstoeko\zugferd\ZugferdDocumentPdfReader;
use horstoeko\zugferd\ZugferdDocumentJsonExporter;

require dirname(__FILE__) . "/../vendor/autoload.php";

$existingPdf =$_FILES['dm_File']["tmp_name"];

$document = ZugferdDocumentPdfReader::readAndGuessFromFile($existingPdf);

$jsonExporter = new ZugferdDocumentJsonExporter($document);

echo $jsonExporter->toPrettyJsonString();
