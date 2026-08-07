<?php

namespace App\Enums;

/**
 * UPGRADE-01: Premium Feature Catalog.
 *
 * Features are granular capabilities that are NOT full modules.
 * They can be:
 *   - included in a plan
 *   - purchased as add-on (with optional quota)
 *   - disabled completely
 *
 * Access levels per feature: none | read_only | full | quota
 */
enum SubscriptionFeature: string
{
    case Api           = 'api';
    case Ai            = 'ai';
    case WhatsApp      = 'whatsapp';
    case Automation    = 'automation';
    case CustomFields  = 'custom_fields';
    case TwoFactorAuth = 'two_factor_auth';
    case EmailVerify   = 'email_verification';
    case GoogleDrive   = 'google_drive';
    case Analytics     = 'analytics';

    public function label(): string
    {
        return match ($this) {
            self::Api           => 'API Akses',
            self::Ai            => 'AI Assistant',
            self::WhatsApp      => 'WhatsApp Notifikasi',
            self::Automation    => 'Automation Rules',
            self::CustomFields  => 'Kolom Kustom',
            self::TwoFactorAuth => '2FA Authentication',
            self::EmailVerify   => 'Email Verification',
            self::GoogleDrive   => 'Google Drive Integration',
            self::Analytics     => 'Advanced Analytics',
        };
    }

    public function accessLevels(): array
    {
        return ['none', 'read_only', 'full', 'quota'];
    }
}
