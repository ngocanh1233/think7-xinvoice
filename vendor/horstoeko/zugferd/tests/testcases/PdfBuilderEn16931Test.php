<?php

namespace horstoeko\zugferd\tests\testcases;

use DateTime;
use \horstoeko\zugferd\tests\TestCase;
use \horstoeko\zugferd\tests\traits\HandlesXmlTests;
use \horstoeko\zugferd\ZugferdProfiles;
use \horstoeko\zugferd\ZugferdDocumentBuilder;
use \horstoeko\zugferd\ZugferdDocumentPdfBuilder;
use \horstoeko\zugferd\codelists\ZugferdPaymentMeans;
use \horstoeko\zugferd\ZugferdDocumentPdfReader;
use \Smalot\PdfParser\Parser as PdfParser;

class PdfBuilderEn16931Test extends TestCase
{
    use HandlesXmlTests;

    /**
     * Source pdf filename
     *
     * @var string
     */
    protected static $sourcePdfFilename = "";

    /**
     * Destination pdf filename
     *
     * @var string
     */
    protected static $destPdfFilename = "";

    public static function setUpBeforeClass(): void
    {
        self::$sourcePdfFilename = dirname(__FILE__) . "/../assets/EmpyPDF.pdf";
        self::$destPdfFilename = dirname(__FILE__) . "/../assets/GeneratedPDF.pdf";

        self::$document = (ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_EN16931))
            ->setDocumentInformation("471102", "380", \DateTime::createFromFormat("Ymd", "20180305"), "EUR")
            ->addDocumentNote('Rechnung gemäß Bestellung vom 01.03.2018.')
            ->addDocumentNote('Lieferant GmbH' . PHP_EOL . 'Lieferantenstraße 20' . PHP_EOL . '80333 München' . PHP_EOL . 'Deutschland' . PHP_EOL . 'Geschäftsführer: Hans Muster' . PHP_EOL . 'Handelsregisternummer: H A 123' . PHP_EOL . PHP_EOL, null, 'REG')
            ->setDocumentSupplyChainEvent(\DateTime::createFromFormat('Ymd', '20180305'))
            ->addDocumentPaymentMean(ZugferdPaymentMeans::UNTDID_4461_58, null, null, null, null, null, "DE12500105170648489890", null, null, null)
            ->setDocumentSeller("Lieferant GmbH", "549910")
            ->addDocumentSellerGlobalId("4000001123452", "0088")
            ->addDocumentSellerTaxRegistration("FC", "201/113/40209")
            ->addDocumentSellerTaxRegistration("VA", "DE123456789")
            ->setDocumentSellerAddress("Lieferantenstraße 20", "", "", "80333", "München", "DE")
            ->setDocumentSellerContact("Heinz Mükker", "Buchhaltung", "+49-111-2222222", "+49-111-3333333", "info@lieferant.de")
            ->setDocumentBuyer("Kunden AG Mitte", "GE2020211")
            ->setDocumentBuyerReference("34676-342323")
            ->setDocumentBuyerAddress("Kundenstraße 15", "", "", "69876", "Frankfurt", "DE")
            ->addDocumentTax("S", "VAT", 275.0, 19.25, 7.0)
            ->addDocumentTax("S", "VAT", 198.0, 37.62, 19.0)
            ->setDocumentSummation(529.87, 529.87, 473.00, 0.0, 0.0, 473.00, 56.87, null, 0.0)
            ->addDocumentPaymentTerm("Zahlbar innerhalb 30 Tagen netto bis 04.04.2018, 3% Skonto innerhalb 10 Tagen bis 15.03.2018")
            ->addNewPosition("1")
            ->setDocumentPositionNote("Bemerkung zu Zeile 1")
            ->setDocumentPositionProductDetails("Trennblätter A4", "", "TB100A4", null, "0160", "4012345001235")
            ->addDocumentPositionProductCharacteristic("Farbe", "Gelb")
            ->addDocumentPositionProductClassification("ClassCode", "ClassName", "ListId", "ListVersionId")
            ->setDocumentPositionProductOriginTradeCountry("CN")
            ->setDocumentPositionGrossPrice(9.9000)
            ->setDocumentPositionNetPrice(9.9000)
            ->setDocumentPositionQuantity(20, "H87")
            ->addDocumentPositionTax('S', 'VAT', 19)
            ->setDocumentPositionLineSummation(198.0)
            ->addNewPosition("2")
            ->setDocumentPositionNote("Bemerkung zu Zeile 2")
            ->setDocumentPositionProductDetails("Joghurt Banane", "", "ARNR2", null, "0160", "4000050986428")
            ->addDocumentPositionProductCharacteristic("Suesstoff", "Nein")
            ->addDocumentPositionProductClassification("ClassCode", "ClassName", "ListId", "ListVersionId")
            ->SetDocumentPositionGrossPrice(5.5000)
            ->SetDocumentPositionNetPrice(5.5000)
            ->SetDocumentPositionQuantity(50, "H87")
            ->AddDocumentPositionTax('S', 'VAT', 7)
            ->SetDocumentPositionLineSummation(275.0);
    }

    public static function tearDownAfterClass(): void
    {
        @unlink(self::$destPdfFilename);
    }

    public function testBuildPdf(): void
    {
        $pdfBuilder = new ZugferdDocumentPdfBuilder(self::$document, self::$sourcePdfFilename);
        $pdfBuilder->generateDocument();
        $pdfBuilder->saveDocument(self::$destPdfFilename);

        $this->assertTrue(file_exists(self::$destPdfFilename));
    }

    public function testBuildPdfString(): void
    {
        $pdfBuilder = new ZugferdDocumentPdfBuilder(self::$document, self::$sourcePdfFilename);
        $pdfBuilder->generateDocument();
        $pdfBuilder->downloadString(self::$destPdfFilename);

        $this->assertIsString(self::$destPdfFilename);
    }

    public function testPdfMetaData(): void
    {
        $pdfParser = new PdfParser();
        $pdfParsed = $pdfParser->parseFile(self::$destPdfFilename);
        $pdfDetails = $pdfParsed->getDetails();

        $this->assertIsArray($pdfDetails);
        $this->assertArrayHasKey("Producer", $pdfDetails); //"FPDF 1.84"
        $this->assertArrayHasKey("CreationDate", $pdfDetails); //"2020-12-09T05:19:39+00:00"
        $this->assertArrayHasKey("Pages", $pdfDetails); //"1"
        $this->assertEquals("1", $pdfDetails["Pages"]);
    }

    public function testReadPdf(): void
    {
        $document = ZugferdDocumentPdfReader::readAndGuessFromFile(self::$destPdfFilename);

        $this->assertNotNull($document);
        $this->assertEquals(ZugferdProfiles::PROFILE_EN16931, $document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_BASIC, $document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_BASICWL, $document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_EXTENDED, $document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_XRECHNUNG, $document->profileId);
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfBuilderAbstract::getXmlContent
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfBuilder::getXmlContent
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfMerger::getXmlContent
     *
     * @return void
     */
    public function testGetXmlContent(): void
    {
        $mockedObject = $this->getMockBuilder(ZugferdDocumentPdfBuilder::class)
            ->setConstructorArgs([self::$document, self::$sourcePdfFilename])
            ->onlyMethods(['getXmlContent'])
            ->getMock();

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $mockedObject
         */
        $mockedObject->expects($this->exactly(2))
            ->method("getXmlContent")
            ->willReturn(self::$document->getContent());

        /**
         * @var \horstoeko\zugferd\ZugferdDocumentPdfBuilder $mockedObject
         */
        $result = $mockedObject->generateDocument();

        $this->assertInstanceOf(ZugferdDocumentPdfBuilder::class, $result);
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfBuilderAbstract::getXmlAttachmentFilename
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfBuilder::getXmlAttachmentFilename
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfMerger::getXmlAttachmentFilename
     *
     * @return void
     */
    public function testGetXmlAttachmentFilename(): void
    {
        $mockedObject = $this->getMockBuilder(ZugferdDocumentPdfBuilder::class)
            ->setConstructorArgs([self::$document, self::$sourcePdfFilename])
            ->onlyMethods(['getXmlAttachmentFilename'])
            ->getMock();

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $mockedObject
         */
        $mockedObject->expects($this->exactly(2))
            ->method("getXmlAttachmentFilename")
            ->willReturn(self::$document->getProfileDefinition()['attachmentfilename']);

        /**
         * @var \horstoeko\zugferd\ZugferdDocumentPdfBuilder $mockedObject
         */
        $result = $mockedObject->generateDocument();

        $this->assertInstanceOf(ZugferdDocumentPdfBuilder::class, $result);
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfBuilderAbstract::getXmlAttachmentXmpName
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfBuilder::getXmlAttachmentXmpName
     * @covers \horstoeko\zugferd\ZugferdDocumentPdfMerger::getXmlAttachmentXmpName
     *
     * @return void
     */
    public function testGetXmlAttachmentXmpName(): void
    {
        $mockedObject = $this->getMockBuilder(ZugferdDocumentPdfBuilder::class)
            ->setConstructorArgs([self::$document, self::$sourcePdfFilename])
            ->onlyMethods(['getXmlAttachmentXmpName'])
            ->getMock();

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $mockedObject
         */
        $mockedObject->expects($this->exactly(1))
            ->method("getXmlAttachmentXmpName")
            ->willReturn(self::$document->getProfileDefinition()["xmpname"]);

        /**
         * @var \horstoeko\zugferd\ZugferdDocumentPdfBuilder $mockedObject
         */
        $result = $mockedObject->generateDocument();

        $this->assertInstanceOf(ZugferdDocumentPdfBuilder::class, $result);
    }
}
