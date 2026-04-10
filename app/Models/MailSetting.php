<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class MailSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'port' => 'integer',
        ];
    }

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Map encryption choice to Symfony mailer SMTP scheme.
     */
    public function smtpScheme(): string
    {
        return match ($this->encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => ($this->port === 465) ? 'smtps' : 'smtp',
        };
    }

    /**
     * Apply stored settings to runtime mail configuration when a row exists with a host.
     */
    public static function applyToConfig(): void
    {
        $settings = static::query()->first();

        if (! $settings || ! $settings->host) {
            return;
        }

        $base = config('mail.mailers.smtp', []);

        $smtp = array_merge($base, [
            'transport' => 'smtp',
            'host' => $settings->host,
            'port' => $settings->port ?? ($base['port'] ?? 587),
            'username' => $settings->username,
            'scheme' => $settings->smtpScheme(),
        ]);

        if ($settings->password !== null && $settings->password !== '') {
            $smtp['password'] = $settings->password;
        }

        Config::set('mail.default', $settings->mailer);
        Config::set('mail.mailers.smtp', $smtp);
        Config::set('mail.from', [
            'address' => $settings->from_address ?? config('mail.from.address'),
            'name' => $settings->from_name ?? config('mail.from.name'),
        ]);
    }
}
