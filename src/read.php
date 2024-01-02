<?php
header('Content-Type: application/json');
use horstoeko\zugferd\ZugferdDocumentPdfReader;
use horstoeko\zugferd\ZugferdDocumentJsonExporter;

require dirname(__FILE__) . "/../vendor/autoload.php";

$document = ZugferdDocumentPdfReader::readAndGuessFromFile(dirname(__FILE__) . "/xinvoice.pdf");

$jsonExporter = new ZugferdDocumentJsonExporter($document);

echo $jsonExporter->toPrettyJsonString();
