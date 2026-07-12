<?php
/**
 * Settings helpers for the administration console.
 */
declare(strict_types=1);

function ensureSetting(string $key, string $default = ''): void
{
    $stmt = getDb()->prepare('SELECT id FROM settings WHERE key_name = ?');
    $stmt->execute([$key]);
    if ($stmt->fetch()) {
        return;
    }
    getDb()->prepare('INSERT INTO settings (key_name, value_text) VALUES (?, ?)')->execute([$key, $default]);
}

function getSetting(string $key, string $default = ''): string
{
    ensureSetting($key, $default);
    $stmt = getDb()->prepare('SELECT value_text FROM settings WHERE key_name = ?');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : (string)$value;
}

function setSetting(string $key, string $value): void
{
    ensureSetting($key);
    getDb()->prepare('UPDATE settings SET value_text = ? WHERE key_name = ?')->execute([$value, $key]);
}

function getCompanyProfile(): array
{
    $defaults = [
        'company_name' => 'TransitOps Logistics',
        'company_email' => 'hello@transitops.com',
        'company_phone' => '+91 9876543210',
        'company_website' => 'https://transitops.example',
        'company_gst' => '27ABCDE1234F1Z5',
        'company_address' => 'Ahmedabad, Gujarat, India',
        'timezone' => 'Asia/Kolkata',
        'currency' => 'INR',
        'language' => 'English',
    ];
    $profile = [];
    foreach ($defaults as $key => $default) {
        $profile[$key] = getSetting($key, $default);
    }
    return $profile;
}

function saveSettingGroup(array $input): void
{
    foreach ($input as $key => $value) {
        setSetting((string)$key, (string)$value);
    }
}
