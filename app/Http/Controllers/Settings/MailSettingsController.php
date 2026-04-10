<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MailSettingsUpdateRequest;
use App\Models\MailSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;

class MailSettingsController extends Controller
{
    /**
     * Show the mail / SMTP settings page.
     */
    public function edit(): Response
    {
        $saved = MailSetting::query()->first();
        $smtp = config('mail.mailers.smtp', []);

        return Inertia::render('settings/MailSettings', [
            'settings' => [
                'mailer' => $saved?->mailer ?? config('mail.default'),
                'host' => $saved?->host ?? ($smtp['host'] ?? ''),
                'port' => $saved?->port ?? ($smtp['port'] ?? 587),
                'encryption' => $saved?->encryption ?? '',
                'username' => $saved?->username ?? '',
                'from_address' => $saved?->from_address ?? config('mail.from.address'),
                'from_name' => $saved?->from_name ?? config('mail.from.name'),
            ],
            'passwordIsSet' => $saved !== null
                && $saved->password !== null
                && $saved->password !== '',
        ]);
    }

    /**
     * Update SMTP settings stored in the database.
     */
    public function update(MailSettingsUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        if (! filled($validated['host'] ?? null)) {
            MailSetting::query()->delete();
            Mail::purge();

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => __('Mail settings cleared. The application will use your environment configuration.'),
            ]);

            return to_route('mail-settings.edit');
        }

        $setting = MailSetting::query()->first() ?? new MailSetting;
        $setting->fill($validated);
        $setting->save();

        Mail::purge();
        MailSetting::applyToConfig();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Mail settings saved.'),
        ]);

        return to_route('mail-settings.edit');
    }

    /**
     * Send a test email to the authenticated user's address.
     */
    public function sendTest(): RedirectResponse
    {
        $user = request()->user();
        abort_if($user === null, 403);

        Mail::purge();
        MailSetting::applyToConfig();

        try {
            Mail::raw(
                __('This is a test email from :app.', ['app' => config('app.name')]),
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject(__('Mail configuration test'));
                }
            );
        } catch (MailerException $e) {
            report($e);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __(
                    'Could not send the test email. Check the host and port, and ensure your mail service is running (for example Mailpit on port 2525 in Laravel Herd).',
                ),
            ]);

            return back();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Test email sent to :email.', ['email' => $user->email]),
        ]);

        return back();
    }
}
