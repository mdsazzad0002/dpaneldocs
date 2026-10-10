<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    mail: Object,
    envMailer: String,
    ai: Object,
    models: Object,
});

const page = usePage();

const mailForm = useForm({
    enabled: props.mail.enabled,
    host: props.mail.host,
    port: props.mail.port,
    encryption: props.mail.encryption,
    username: props.mail.username,
    password: '',
    from_address: props.mail.from_address,
    from_name: props.mail.from_name,
});

const saveMail = () => mailForm.put(route('admin.settings.mail'), {
    preserveScroll: true,
    onSuccess: () => mailForm.reset('password'),
});

const testForm = useForm({ to: page.props.auth.user.email });
const sendTest = () => testForm.post(route('admin.settings.mail.test'), { preserveScroll: true });

const aiForm = useForm({
    enabled: props.ai.enabled,
    api_key: '',
    model: props.ai.model,
    instructions: props.ai.instructions,
    remove_key: false,
});

const saveAi = () => aiForm.put(route('admin.settings.ai'), {
    preserveScroll: true,
    onSuccess: () => aiForm.reset('api_key', 'remove_key'),
});

const input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950';
const presets = {
    Gmail: { host: 'smtp.gmail.com', port: 587, encryption: 'tls' },
    'Outlook / Office 365': { host: 'smtp.office365.com', port: 587, encryption: 'tls' },
    Zoho: { host: 'smtp.zoho.com', port: 465, encryption: 'ssl' },
    Mailgun: { host: 'smtp.mailgun.org', port: 587, encryption: 'tls' },
    'Amazon SES': { host: 'email-smtp.us-east-1.amazonaws.com', port: 587, encryption: 'tls' },
};
const applyPreset = (name) => Object.assign(mailForm, presets[name]);
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Settings</h2>
        </template>

        <div class="grid gap-6 xl:grid-cols-2">
            <!-- SMTP -->
            <section class="space-y-5">
                <form class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="saveMail">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold">Email (SMTP)</h3>
                            <p class="text-sm text-slate-500">Used for ticket replies, comment and review replies, thank-you and compose emails.</p>
                        </div>
                        <label class="flex items-center gap-2 text-sm font-medium">
                            <input v-model="mailForm.enabled" type="checkbox" class="rounded border-slate-300 text-blue-600 dark:border-slate-700 dark:bg-slate-900" />
                            Use this SMTP server
                        </label>
                    </div>
                    <p v-if="!mailForm.enabled" class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                        Off: mail uses the server's <code class="font-mono">.env</code> settings (mailer: <strong>{{ envMailer }}</strong>).
                    </p>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-500">Presets:</span>
                        <button v-for="(preset, name) in presets" :key="name" type="button" class="rounded-full border border-slate-300 px-2.5 py-1 hover:border-blue-400 hover:text-blue-600 dark:border-slate-700" @click="applyPreset(name)">{{ name }}</button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-[1fr_110px_130px]">
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Host</span>
                            <input v-model="mailForm.host" :class="input" placeholder="smtp.example.com" />
                            <span v-if="mailForm.errors.host" class="mt-1 block text-xs text-red-600">{{ mailForm.errors.host }}</span>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Port</span>
                            <input v-model="mailForm.port" type="number" :class="input" />
                            <span v-if="mailForm.errors.port" class="mt-1 block text-xs text-red-600">{{ mailForm.errors.port }}</span>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Encryption</span>
                            <select v-model="mailForm.encryption" :class="input">
                                <option value="tls">STARTTLS</option>
                                <option value="ssl">SSL/TLS</option>
                                <option value="none">None</option>
                            </select>
                        </label>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Username</span>
                            <input v-model="mailForm.username" autocomplete="off" :class="input" />
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Password</span>
                            <input v-model="mailForm.password" type="password" autocomplete="new-password" :class="input" :placeholder="mail.has_password ? '•••••••• (saved, leave blank to keep)' : ''" />
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">From address</span>
                            <input v-model="mailForm.from_address" type="email" required :class="input" />
                            <span v-if="mailForm.errors.from_address" class="mt-1 block text-xs text-red-600">{{ mailForm.errors.from_address }}</span>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">From name</span>
                            <input v-model="mailForm.from_name" required :class="input" />
                        </label>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="mailForm.processing" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Save mail settings</button>
                    </div>
                </form>

                <form class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="sendTest">
                    <label class="block min-w-56 flex-1 text-sm">
                        <span class="mb-1 block font-medium">Send a test email to</span>
                        <input v-model="testForm.to" type="email" required :class="input" />
                        <span v-if="testForm.errors.to" class="mt-1 block text-xs text-red-600">{{ testForm.errors.to }}</span>
                    </label>
                    <button type="submit" :disabled="testForm.processing" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:hover:bg-slate-800">{{ testForm.processing ? 'Sending…' : 'Send test' }}</button>
                    <p class="w-full text-xs text-slate-500">Uses the saved settings. Save first if you changed anything.</p>
                </form>
            </section>

            <!-- AI -->
            <form class="h-fit space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="saveAi">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold">AI reply assistant</h3>
                        <p class="text-sm text-slate-500">Drafts replies to tickets, comments, reviews and emails with Claude. Staff always review before sending.</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-medium">
                        <input v-model="aiForm.enabled" type="checkbox" class="rounded border-slate-300 text-blue-600 dark:border-slate-700 dark:bg-slate-900" />
                        Enabled
                    </label>
                </div>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Anthropic API key</span>
                    <input v-model="aiForm.api_key" type="password" autocomplete="off" :class="input" :placeholder="ai.has_key ? '•••••••• (saved, leave blank to keep)' : 'sk-ant-…'" />
                    <span v-if="aiForm.errors.api_key" class="mt-1 block text-xs text-red-600">{{ aiForm.errors.api_key }}</span>
                    <span class="mt-1 block text-xs text-slate-500">
                        <template v-if="ai.has_key">A key is saved (encrypted).</template>
                        <template v-else-if="ai.env_key">Using <code class="font-mono">ANTHROPIC_API_KEY</code> from <code class="font-mono">.env</code>.</template>
                        <template v-else>Create one at <a href="https://platform.claude.com/settings/keys" target="_blank" rel="noopener" class="text-blue-600 hover:underline dark:text-blue-400">platform.claude.com</a>.</template>
                    </span>
                </label>
                <label v-if="ai.has_key" class="flex items-center gap-2 text-sm text-red-600">
                    <input v-model="aiForm.remove_key" type="checkbox" class="rounded border-slate-300 text-red-600 dark:border-slate-700 dark:bg-slate-900" />
                    Remove the saved key
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Model</span>
                    <select v-model="aiForm.model" :class="input">
                        <option v-for="(label, key) in models" :key="key" :value="key">{{ label }}</option>
                    </select>
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium">House style &amp; facts <span class="font-normal text-slate-400">(optional)</span></span>
                    <textarea v-model="aiForm.instructions" rows="6" :class="input" placeholder="e.g. Sign off as “— Sazzad from dPanel”. Paid support costs $25/hour. Recommend Ubuntu 24.04. Reply in Bangla when the customer writes in Bangla." />
                    <span class="mt-1 block text-xs text-slate-500">Added to every request, so the assistant knows your tone, prices and policies.</span>
                </label>

                <div class="flex justify-end">
                    <button type="submit" :disabled="aiForm.processing" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Save AI settings</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
