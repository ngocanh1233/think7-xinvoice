<?php

/**
 * This file is a part of horstoeko/zugferd.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace horstoeko\zugferd;

use DOMDocument;
use DOMXpath;
use horstoeko\stringmanagement\PathUtils;
use horstoeko\zugferd\codelists\ZugferdInvoiceType;
use horstoeko\zugferd\ZugferdPackageVersion;
use horstoeko\zugferd\ZugferdPdfWriter;
use horstoeko\zugferd\ZugferdSettings;
use setasign\Fpdi\PdfParser\StreamReader as PdfStreamReader;

/**
 * Class representing the base facillity adding XML data
 * to an existing PDF with conversion to PDF/A
 *
 * @category Zugferd
 * @package  Zugferd
 * @author   D. Erling <horstoeko@erling.com.de>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/horstoeko/zugferd
 */
abstract class ZugferdDocumentPdfBuilderAbstract
{
    /**
     * Instance of the pdfwriter
     *
     * @var ZugferdPdfWriter
     */
    private $pdfWriter = null;

    /**
     * Contains the data of the original PDF documjent
     *
     * @var string
     */
    private $pdfData = "";

    /**
     * Constructor
     *
     * @param string $pdfData
     * The full filename or a string containing the binary pdf data. This
     * is the original PDF (e.g. created by a ERP system)
     */
    public function __construct(string $pdfData)
    {
        $this->pdfData = $pdfData;
        $this->pdfWriter = new ZugferdPdfWriter();
    }

    /**
     * Generates the final document
     *
     * @return self
     */
    public function generateDocument()
    {
        $this->startCreatePdf();

        return $this;
    }

    /**
     * Saves the document generated with generateDocument to a file
     *
     * @param  string $toFilename
     * The full qualified filename to which the generated PDF (with attachment)
     * is stored
     * @return self
     */
    public function saveDocument(string $toFilename)
    {
        $this->pdfWriter->Output($toFilename, 'F');

        return $this;
    }

    /**
     * Returns the PDF as a string
     *
     * @param  string $toFilename
     * @return string
     */
    public function downloadString(string $toFilename): string
    {
        return $this->pdfWriter->Output($toFilename, 'S');
    }

    /**
     * Get the content of XML to attach
     *
     * @return string
     */
    abstract protected function getXmlContent(): string;

    /**
     * Get the filename of the XML to attach
     *
     * @return string
     */
    abstract protected function getXmlAttachmentFilename(): string;

    /**
     * Get the XMP name for the XML to attach
     *
     * @return string
     */
    abstract protected function getXmlAttachmentXmpName(): string;

    /**
     * Internal function which sets up the PDF
     *
     * @return void
     */
    private function startCreatePdf(): void
    {
        // Get PDF data

        $pdfDataRef = null;

        if ($this->pdfDataIsFile($this->pdfData)) {
            $pdfDataRef = $this->pdfData;
        } elseif (is_string($this->pdfData)) {
            $pdfDataRef = PdfStreamReader::createByString($this->pdfData);
        }

        // Get XML data from Builder

        $documentBuilderXmlDataRef = PdfStreamReader::createByString($this->getXmlContent());

        // Start

        $this->pdfWriter->attach(
            $documentBuilderXmlDataRef,
            $this->getXmlAttachmentFilename(),
            'Factur-X Invoice',
            'Data',
            'text#2Fxml'
        );

        $this->pdfWriter->openAttachmentPane();

        // Copy pages from the original PDF

        $pageCount = $this->pdfWriter->setSourceFile($pdfDataRef);

        for ($pageNumber = 1; $pageNumber <= $pageCount; ++$pageNumber) {
            $pageContent = $this->pdfWriter->importPage($pageNumber, '/MediaBox');
            $this->pdfWriter->AddPage();
            $this->pdfWriter->useTemplate($pageContent, 0, 0, null, null, true);
        }

        // Set PDF version 1.7 according to PDF/A-3 ISO 32000-1

        $this->pdfWriter->setPdfVersion('1.7', true);

        // Update meta data (e.g. such as author, producer, title)

        $this->updatePdfMetadata();
    }

    /**
     * Update PDF metadata to according to FacturX/ZUGFeRD XML data.
     *
     * @return void
     */
    private function updatePdfMetadata(): void
    {
        $pdfMetadataInfos = $this->preparePdfMetadata();
        $this->pdfWriter->setPdfMetadataInfos($pdfMetadataInfos);

        $xmp = simplexml_load_file(PathUtils::combinePathWithFile(ZugferdSettings::getAssetDirectory(), 'facturx_extension_schema.xmp'));
        $descriptionNodes = $xmp->xpath('rdf:Description');

        $descFx = $descriptionNodes[0];
        $descFx->children('fx', true)->{'ConformanceLevel'} = strtoupper($this->getXmlAttachmentXmpName());
        $descFx->children('fx', true)->{'DocumentFileName'} = $this->getXmlAttachmentFilename();
        $this->pdfWriter->addMetadataDescriptionNode($descFx->asXML());

        $this->pdfWriter->addMetadataDescriptionNode($descriptionNodes[1]->asXML());

        $descPdfAid = $descriptionNodes[2];
        $this->pdfWriter->addMetadataDescriptionNode($descPdfAid->asXML());

        $descDc = $descriptionNodes[3];
        $descNodes = $descDc->children('dc', true);
        $descNodes->title->children('rdf', true)->Alt->li = $pdfMetadataInfos['title'];
        $descNodes->creator->children('rdf', true)->Seq->li = $pdfMetadataInfos['author'];
        $descNodes->description->children('rdf', true)->Alt->li = $pdfMetadataInfos['subject'];
        $this->pdfWriter->addMetadataDescriptionNode($descDc->asXML());

        $descAdobe = $descriptionNodes[4];
        $descAdobe->children('pdf', true)->{'Producer'} = 'FPDF';
        $this->pdfWriter->addMetadataDescriptionNode($descAdobe->asXML());

        $descXmp = $descriptionNodes[5];
        $xmpNodes = $descXmp->children('xmp', true);
        $xmpNodes->{'CreatorTool'} = sprintf('Factur-X PHP library v%s by HorstOeko', ZugferdPackageVersion::getInstalledVersion());
        $xmpNodes->{'CreateDate'} = $pdfMetadataInfos['createdDate'];
        $xmpNodes->{'ModifyDate'} = $pdfMetadataInfos['modifiedDate'];
        $this->pdfWriter->addMetadataDescriptionNode($descXmp->asXML());
    }

    /**
     * Prepare PDF Metadata informations from FacturX/ZUGFeRD XML.
     *
     * @return array
     */
    private function preparePdfMetadata(): array
    {
        $invoiceInformations = $this->extractInvoiceInformations();

        $dateString = date('Y-m-d', strtotime($invoiceInformations['date']));
        $title = sprintf('%s : %s %s', $invoiceInformations['seller'], $invoiceInformations['docTypeName'], $invoiceInformations['invoiceId']);
        $subject = sprintf('FacturX/ZUGFeRD %s %s dated %s issued by %s', $invoiceInformations['docTypeName'], $invoiceInformations['invoiceId'], $dateString, $invoiceInformations['seller']);

        $pdfMetadata = array(
            'author' => $invoiceInformations['seller'],
            'keywords' => sprintf('%s, FacturX/ZUGFeRD', $invoiceInformations['docTypeName']),
            'title' => $title,
            'subject' => $subject,
            'createdDate' => $invoiceInformations['date'],
            'modifiedDate' => date('Y-m-d\TH:i:s') . '+00:00',
        );

        return $pdfMetadata;
    }

    /**
     * Extract major invoice information from FacturX/ZUGFeRD XML.
     *
     * @return array
     */
    protected function extractInvoiceInformations(): array
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->getXmlContent());

        $xpath = new DOMXPath($domDocument);

        $dateXpath = $xpath->query('//rsm:ExchangedDocument/ram:IssueDateTime/udt:DateTimeString');
        $date = $dateXpath->item(0)->nodeValue;
        $dateReformatted = date('Y-m-d\TH:i:s', strtotime($date)) . '+00:00';

        $invoiceIdXpath = $xpath->query('//rsm:ExchangedDocument/ram:ID');
        $invoiceId = $invoiceIdXpath->item(0)->nodeValue;

        $sellerXpath = $xpath->query('//ram:ApplicableHeaderTradeAgreement/ram:SellerTradeParty/ram:Name');
        $sellerName = $sellerXpath->item(0)->nodeValue;

        $docTypeXpath = $xpath->query('//rsm:ExchangedDocument/ram:TypeCode');
        $docTypeCode = $docTypeXpath->item(0)->nodeValue;

        switch ($docTypeCode) {
            case ZugferdInvoiceType::CREDITNOTE:
                $docTypeName = 'Credit Note';
                break;
            default:
                $docTypeName = 'Invoice';
                break;
        }

        $invoiceInformation = array(
            'invoiceId' => $invoiceId,
            'docTypeName' => $docTypeName,
            'seller' => $sellerName,
            'date' => $dateReformatted,
        );

        return $invoiceInformation;
    }

    /**
     * Returns true if the submittet parameter $pdfData is a valid file.
     * Otherwise it will return false
     *
     * @param  string $pdfData
     * @return boolean
     */
    private function pdfDataIsFile($pdfData): bool
    {
        try {
            return @is_file($pdfData);
        } catch (\TypeError $ex) {
            return false;
        }
    }
}
