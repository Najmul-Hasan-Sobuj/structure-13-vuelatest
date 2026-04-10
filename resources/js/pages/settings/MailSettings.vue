<script setup lang="ts">
import MailSettingsController from '@/actions/App/Http/Controllers/Settings/MailSettingsController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/mail-settings';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Settings = {
    mailer: string;
    host: string;
    port: number | '';
    encryption: string;
    username: string;
    from_address: string;
    from_name: string;
};

const props = defineProps<{
    settings: Settings;
    passwordIsSet: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Mail',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const userEmail = computed(() => page.props.auth.user?.email ?? '');
</script>

<template>
    <Head title="Mail settings" />

    <h1 class="sr-only">Mail settings</h1>

    <div class="space-y-10">
        <Heading
            variant="small"
            title="Mail"
            description="Configure SMTP delivery for this application. When saved, these settings override mail values from the environment for the running app."
        />

        <Form
            v-bind="MailSettingsController.update.form.patch()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="mailer" value="smtp" />

            <div class="grid gap-2">
                <Label for="mail_host">SMTP host</Label>
                <Input
                    id="mail_host"
                    name="host"
                    type="text"
                    class="mt-1 block w-full font-mono text-sm"
                    :default-value="settings.host"
                    autocomplete="off"
                    placeholder="smtp.mailtrap.io"
                />
                <p class="text-xs text-muted-foreground">
                    Leave empty to use only your .env mail configuration (database row will be removed).
                </p>
                <InputError class="mt-1" :message="errors.host" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="mail_port">Port</Label>
                    <Input
                        id="mail_port"
                        name="port"
                        type="number"
                        min="1"
                        max="65535"
                        class="mt-1 block w-full font-mono text-sm"
                        :default-value="settings.port === '' ? '' : settings.port"
                        autocomplete="off"
                        placeholder="587"
                    />
                    <InputError class="mt-1" :message="errors.port" />
                </div>
                <div class="grid gap-2">
                    <Label for="mail_encryption">Encryption</Label>
                    <select
                        id="mail_encryption"
                        name="encryption"
                        class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] focus-visible:outline-1 disabled:cursor-not-allowed disabled:opacity-50"
                        :default-value="settings.encryption || ''"
                    >
                        <option value="">None (STARTTLS as needed)</option>
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL / SMTPS</option>
                    </select>
                    <InputError class="mt-1" :message="errors.encryption" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="mail_username">Username</Label>
                <Input
                    id="mail_username"
                    name="username"
                    type="text"
                    class="mt-1 block w-full font-mono text-sm"
                    :default-value="settings.username"
                    autocomplete="username"
                />
                <InputError class="mt-1" :message="errors.username" />
            </div>

            <div class="grid gap-2">
                <Label for="mail_password">Password</Label>
                <Input
                    id="mail_password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full font-mono text-sm"
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <p
                    v-if="passwordIsSet"
                    class="text-xs text-muted-foreground"
                >
                    Leave blank to keep the current password.
                </p>
                <InputError class="mt-1" :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="mail_from_address">From address</Label>
                <Input
                    id="mail_from_address"
                    name="from_address"
                    type="email"
                    required
                    class="mt-1 block w-full font-mono text-sm"
                    :default-value="settings.from_address"
                    autocomplete="off"
                />
                <InputError class="mt-1" :message="errors.from_address" />
            </div>

            <div class="grid gap-2">
                <Label for="mail_from_name">From name</Label>
                <Input
                    id="mail_from_name"
                    name="from_name"
                    type="text"
                    class="mt-1 block w-full font-mono text-sm"
                    :default-value="settings.from_name"
                    autocomplete="off"
                />
                <InputError class="mt-1" :message="errors.from_name" />
            </div>

            <div class="flex flex-wrap gap-3">
                <Button type="submit" :disabled="processing">
                    Save mail settings
                </Button>
            </div>
        </Form>

        <div
            class="border-border space-y-3 rounded-lg border border-dashed p-4"
        >
            <div class="space-y-1">
                <h3 class="text-base font-medium">Send test email</h3>
                <p class="text-sm text-muted-foreground">
                    Sends a message to {{ userEmail }} using the active
                    configuration.
                </p>
            </div>
            <Form
                v-bind="MailSettingsController.sendTest.form.post()"
                v-slot="{ processing: sending }"
            >
                <Button type="submit" variant="outline" :disabled="sending">
                    Send test email
                </Button>
            </Form>
        </div>
    </div>
</template>
