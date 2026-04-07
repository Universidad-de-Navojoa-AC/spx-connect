<?php

namespace Unav\SpxConnect\Tests\Unit;

use Unav\SpxConnect\Enums\DimensionType;
use Unav\SpxConnect\Enums\JournalFileType;
use Unav\SpxConnect\Tests\TestCase;

class EnumsTest extends TestCase
{
    // DimensionType Tests
    public function testDimensionTypeValues(): void
    {
        $this->assertEquals('01', DimensionType::RESOURCE->value);
        $this->assertEquals('02', DimensionType::TFWW->value);
        $this->assertEquals('03', DimensionType::FUND->value);
        $this->assertEquals('04', DimensionType::FUNCTION->value);
        $this->assertEquals('05', DimensionType::RESTRICTION->value);
        $this->assertEquals('06', DimensionType::ORGID->value);
        $this->assertEquals('07', DimensionType::WHO->value);
        $this->assertEquals('08', DimensionType::FLAG->value);
        $this->assertEquals('09', DimensionType::PROJECT->value);
        $this->assertEquals('10', DimensionType::DETAIL->value);
    }

    public function testDimensionTypeLabelMethod(): void
    {
        $this->assertEquals('01', DimensionType::RESOURCE->label());
        $this->assertEquals('02', DimensionType::TFWW->label());
        $this->assertEquals('03', DimensionType::FUND->label());
        $this->assertEquals('04', DimensionType::FUNCTION->label());
        $this->assertEquals('05', DimensionType::RESTRICTION->label());
        $this->assertEquals('06', DimensionType::ORGID->label());
        $this->assertEquals('07', DimensionType::WHO->label());
        $this->assertEquals('08', DimensionType::FLAG->label());
        $this->assertEquals('09', DimensionType::PROJECT->label());
        $this->assertEquals('10', DimensionType::DETAIL->label());
    }

    public function testDimensionTypeFromValue(): void
    {
        $this->assertEquals(DimensionType::RESOURCE, DimensionType::from('01'));
        $this->assertEquals(DimensionType::PROJECT, DimensionType::from('09'));
        $this->assertEquals(DimensionType::DETAIL, DimensionType::from('10'));
    }

    public function testDimensionTypeTryFromValidValue(): void
    {
        $this->assertEquals(DimensionType::FUND, DimensionType::tryFrom('03'));
    }

    public function testDimensionTypeTryFromInvalidValue(): void
    {
        $this->assertNull(DimensionType::tryFrom('99'));
    }

    public function testDimensionTypeCases(): void
    {
        $cases = DimensionType::cases();

        $this->assertCount(10, $cases);
        $this->assertContains(DimensionType::RESOURCE, $cases);
        $this->assertContains(DimensionType::DETAIL, $cases);
    }

    // JournalFileType Tests
    public function testJournalFileTypeValues(): void
    {
        $this->assertEquals('PGP', JournalFileType::PGP->value);
        $this->assertEquals('PASR', JournalFileType::PASR->value);
        $this->assertEquals('PR', JournalFileType::PR->value);
        $this->assertEquals('PP', JournalFileType::PP->value);
        $this->assertEquals('PC', JournalFileType::PC->value);
        $this->assertEquals('PCS', JournalFileType::PCS->value);
        $this->assertEquals('PAD', JournalFileType::PAD->value);
        $this->assertEquals('PNM', JournalFileType::PNM->value);
        $this->assertEquals('PDM', JournalFileType::PDM->value);
        $this->assertEquals('PFP', JournalFileType::PFP->value);
        $this->assertEquals('PPCV', JournalFileType::PPCV->value);
        $this->assertEquals('PVC', JournalFileType::PVC->value);
    }

    public function testJournalFileTypeGetLabelMethod(): void
    {
        $this->assertEquals('Póliza de Gobierno', JournalFileType::PGP->getLabel());
        $this->assertEquals('Reporte de estado de cuenta', JournalFileType::PASR->getLabel());
        $this->assertEquals('Recibo', JournalFileType::PR->getLabel());
        $this->assertEquals('Pago', JournalFileType::PP->getLabel());
        $this->assertEquals('Cheque', JournalFileType::PC->getLabel());
        $this->assertEquals('Corte de Caja', JournalFileType::PCS->getLabel());
        $this->assertEquals('Reconocer Depósitos', JournalFileType::PAD->getLabel());
        $this->assertEquals('Nota de Aviso', JournalFileType::PNM->getLabel());
        $this->assertEquals('Mantenimiento Diario', JournalFileType::PDM->getLabel());
        $this->assertEquals('Póliza Financiera', JournalFileType::PFP->getLabel());
        $this->assertEquals('Vale Caja Chica', JournalFileType::PPCV->getLabel());
        $this->assertEquals('Vale Cheque', JournalFileType::PVC->getLabel());
    }

    public function testJournalFileTypeFromValue(): void
    {
        $this->assertEquals(JournalFileType::PGP, JournalFileType::from('PGP'));
        $this->assertEquals(JournalFileType::PR, JournalFileType::from('PR'));
        $this->assertEquals(JournalFileType::PVC, JournalFileType::from('PVC'));
    }

    public function testJournalFileTypeTryFromValidValue(): void
    {
        $this->assertEquals(JournalFileType::PC, JournalFileType::tryFrom('PC'));
    }

    public function testJournalFileTypeTryFromInvalidValue(): void
    {
        $this->assertNull(JournalFileType::tryFrom('INVALID'));
    }

    public function testJournalFileTypeCases(): void
    {
        $cases = JournalFileType::cases();

        $this->assertCount(12, $cases);
        $this->assertContains(JournalFileType::PGP, $cases);
        $this->assertContains(JournalFileType::PVC, $cases);
    }

    public function testJournalFileTypeImplementsHasLabel(): void
    {
        $this->assertInstanceOf(
            \Unav\SpxConnect\Contracts\HasLabel::class,
            JournalFileType::PGP
        );
    }
}
