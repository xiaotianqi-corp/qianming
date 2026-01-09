<?php

namespace App\Enums;

enum TicketCategory: string
{
    case TECHNICAL = 'technical';
    case BILLING = 'billing';
    case RENEWAL = 'renewal';
    case REVOCATION = 'revocation';
    case INSTALLATION = 'installation';
    case VERIFICATION = 'verification';
    case GENERAL = 'general';

    public function label(): string
    {
        return match($this) {
            self::TECHNICAL => 'Soporte Técnico',
            self::BILLING => 'Facturación',
            self::RENEWAL => 'Renovación',
            self::REVOCATION => 'Revocación',
            self::INSTALLATION => 'Instalación',
            self::VERIFICATION => 'Verificación de Identidad',
            self::GENERAL => 'Consulta General',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::TECHNICAL => 'Problemas técnicos con certificados digitales',
            self::BILLING => 'Consultas sobre pagos, facturas y suscripciones',
            self::RENEWAL => 'Renovación de certificados digitales',
            self::REVOCATION => 'Solicitudes de revocación de certificados',
            self::INSTALLATION => 'Ayuda con la instalación del certificado',
            self::VERIFICATION => 'Problemas con el proceso de verificación KYC',
            self::GENERAL => 'Consultas generales y otros temas',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::TECHNICAL => 'wrench',
            self::BILLING => 'credit-card',
            self::RENEWAL => 'refresh-cw',
            self::REVOCATION => 'x-circle',
            self::INSTALLATION => 'download',
            self::VERIFICATION => 'shield-check',
            self::GENERAL => 'help-circle',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::TECHNICAL => 'blue',
            self::BILLING => 'green',
            self::RENEWAL => 'purple',
            self::REVOCATION => 'red',
            self::INSTALLATION => 'orange',
            self::VERIFICATION => 'yellow',
            self::GENERAL => 'gray',
        };
    }

    // Helper para validaciones
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
            'icon' => $case->icon(),
            'color' => $case->color(),
        ])->all();
    }
}