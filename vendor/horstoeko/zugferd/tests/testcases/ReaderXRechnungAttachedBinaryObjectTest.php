<?php

namespace horstoeko\zugferd\tests\testcases;

use \horstoeko\zugferd\tests\TestCase;
use \horstoeko\zugferd\ZugferdProfiles;
use \horstoeko\zugferd\ZugferdDocumentReader;
use \horstoeko\zugferd\codelists\ZugferdInvoiceType;

class ReaderXRechnungAttachedBinaryObjectTest extends TestCase
{
    /**
     * @var ZugferdDocumentReader
     */
    protected static $document;

    public static function setUpBeforeClass(): void
    {
        self::$document = ZugferdDocumentReader::readAndGuessFromFile(dirname(__FILE__) . "/../assets/xrechnung_simple_2.xml");
    }

    public function testDocumentProfile(): void
    {
        $this->assertNotEquals(ZugferdProfiles::PROFILE_EN16931, self::$document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_BASIC, self::$document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_BASICWL, self::$document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_EXTENDED, self::$document->profileId);
        $this->assertNotEquals(ZugferdProfiles::PROFILE_XRECHNUNG, self::$document->profileId);
        $this->assertEquals(ZugferdProfiles::PROFILE_XRECHNUNG_2, self::$document->profileId);
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentReader::getDocumentInformation
     */
    public function testDocumentGenerals(): void
    {
        self::$document->getDocumentInformation($documentno, $documenttypecode, $documentdate, $invoiceCurrency, $taxCurrency, $documentname, $documentlanguage, $effectiveSpecifiedPeriod);
        $this->assertEquals('123456789', $documentno);
        $this->assertEquals(ZugferdInvoiceType::INVOICE, $documenttypecode);
        $this->assertNotNull($documentdate);
        $this->assertEquals((\DateTime::createFromFormat('Ymd', '20180605'))->format('Ymd'), $documentdate->format('Ymd'));
        $this->assertEquals("EUR", $invoiceCurrency);
        $this->assertEquals("", $taxCurrency);
        $this->assertEquals("", $documentname);
        $this->assertEquals("", $documentlanguage);
        $this->assertNull($effectiveSpecifiedPeriod);
        $this->assertNotNull(self::$document->getInvoiceObject());
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentReader::firstDocumentAdditionalReferencedDocument
     */
    public function testFirstDocumentAdditionalReferencedDocument(): void
    {
        $this->assertTrue((self::$document)->firstDocumentAdditionalReferencedDocument());
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentReader::setBinaryDataDirectory
     * @covers \horstoeko\zugferd\ZugferdDocumentReader::getDocumentAdditionalReferencedDocument
     */
    public function testGetDocumentAdditionalReferencedDocumentNoDirectorySet(): void
    {
        self::$document->getDocumentAdditionalReferencedDocument($issuerassignedid, $typecode, $uriid, $name, $reftypecode, $issueddate, $binarydatafilename);
        $this->assertEquals("01_15_Anhang_01.pdf", $issuerassignedid);
        $this->assertEquals("916", $typecode);
        $this->assertArrayHasKey(0, $name);
        $this->assertArrayNotHasKey(1, $name);
        $this->assertEquals("Aufschlüsselung der einzelnen Leistungspositionen", $name[0]);
        $this->assertEquals("", $binarydatafilename);
        $this->assertFalse(file_exists($binarydatafilename));
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentReader::setBinaryDataDirectory
     * @covers \horstoeko\zugferd\ZugferdDocumentReader::getDocumentAdditionalReferencedDocument
     */
    public function testGetDocumentAdditionalReferencedDocument(): void
    {
        self::$document->setBinaryDataDirectory(dirname(__FILE__));
        self::$document->getDocumentAdditionalReferencedDocument($issuerassignedid, $typecode, $uriid, $name, $reftypecode, $issueddate, $binarydatafilename);
        $this->assertEquals("01_15_Anhang_01.pdf", $issuerassignedid);
        $this->assertEquals("916", $typecode);
        $this->assertArrayHasKey(0, $name);
        $this->assertArrayNotHasKey(1, $name);
        $this->assertEquals("Aufschlüsselung der einzelnen Leistungspositionen", $name[0]);
        $this->assertNotEquals("", $binarydatafilename);
        $this->assertEquals(dirname(__FILE__) . DIRECTORY_SEPARATOR . "01_15_Anhang_01.pdf", $binarydatafilename);
        $this->assertTrue(file_exists($binarydatafilename));
        $this->assertEquals(150128, filesize($binarydatafilename));
        $this->assertEquals("%PDF", substr(file_get_contents($binarydatafilename), 0, 4));
        @unlink($binarydatafilename);
    }

    /**
     * @covers \horstoeko\zugferd\ZugferdDocumentReader::nextDocumentAdditionalReferencedDocument
     */
    public function testNextDocumentAdditionalReferencedDocument(): void
    {
        $this->assertFalse((self::$document)->nextDocumentAdditionalReferencedDocument());
    }
}
