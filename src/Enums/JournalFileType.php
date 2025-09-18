<?php
namespace Unav\SpxConnect\Enums;

use Unav\SpxConnect\Contracts\HasLabel;

enum JournalFileType: string implements HasLabel
{
    case PGP = 'PGP';
    case PASR = 'PASR';
    case PR = 'PR';
    case PP = 'PP';
    case PC = 'PC';
    case PCS = 'PCS';
    case PAD = 'PAD';
    case PNM = 'PNM';
    case PDM = 'PDM';
    case PFP = 'PFP';
    case PPCV = 'PPCV';
    case PVC = 'PVC';

    public function getLabel(): string
    {
        return match ($this) {
            self::PGP => 'Póliza de Gobierno',
            self::PASR => 'Reporte de estado de cuenta',
            self::PR => 'Recibo',
            self::PP => 'Pago',
            self::PC => 'Cheque',
            self::PCS => 'Corte de Caja',
            self::PAD => 'Reconocer Depósitos',
            self::PNM => 'Nota de Aviso',
            self::PDM => 'Mantenimiento Diario',
            self::PFP => 'Póliza Financiera',
            self::PPCV => 'Vale Caja Chica',
            self::PVC => 'Vale Cheque',
        };
    }
}