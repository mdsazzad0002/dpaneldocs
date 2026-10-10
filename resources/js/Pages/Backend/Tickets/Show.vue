<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AiAssist from '@/Components/AiAssist.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ticket: Object,
    statuses: Object,
    categories: Object,
    priorities: Object,
    publicUrl: String,
});

const form = useForm({
    body: '',
    status: 'answered',
});

const send = () => form.post(route('admin.tickets.reply', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => form.reset('body'),
});

const update = (field, value) => router.patch(route('admin.tickets.update', props.ticket.id), { [field]: value }, { preserveScroll: true });

const destroy = () => {
    if (confirm(`Delete ticket ${props.ticket.reference} and all replies? This cannot be undone.`)) {
        router.delete(route('admin.tickets.destroy', props.ticket.id));
    }
};

const copied = ref(false);
const copyLink = async () => {
    await navigator.clipboard.writeText(props.publicUrl);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
};

const when = (date) => new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <Head :title="`Ticket ${ticket.reference}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.tickets.index')" class="text-sm text-slate-500 hover:text-slate-900 dark:hover:text-white">Tickets</Link>
                <span class="text-slate-400">/</span>
                <h2 class="font-mono text-sm font-semibold">{{ ticket.reference }}</h2>
            </div>
        </template>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0 space-y-5">
                <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-center gap-2">
                        <StatusBadge :status="ticket.status" />
                        <StatusBadge :status="ticket.priority">{{ ticket.priority }} priority</StatusBadge>
                    </div>
                    <h1 class="mt-3 text-xl font-semibold">{{ ticket.subject }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ ticket.name }} &lt;{{ ticket.email }}&gt; · {{ when(ticket.created_at) }}</p>
                    <div class="mt-5 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ ticket.message }}</div>
                </div>

                <div
                    v-for="reply in ticket.replies"
                    :key="reply.id"
                    class="rounded-xl border p-5"
                    :class="reply.is_staff ? 'border-blue-200 bg-blue-50/60 dark:border-blue-900 dark:bg-blue-950/30' : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900'"
                >
                    <p class="text-sm">
                        <span class="font-semibold">{{ reply.author_name }}</span>
                        <span v-if="reply.is_staff" class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-white">Staff</span>
                        <span class="text-slate-500"> · {{ when(reply.created_at) }}</span>
                    </p>
                    <div class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ reply.body }}</div>
                </div>

                <form class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="send">
                    <label for="reply" class="mb-2 block text-sm font-medium">Reply to {{ ticket.name }}</label>
                    <textarea
                        id="reply"
                        v-model="form.body"
                        rows="7"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                        placeholder="Your reply is emailed to the customer and shown on their ticket page."
                    />
                    <p v-if="form.errors.body" class="mt-1 text-sm text-red-600">{{ form.errors.body }}</p>
                    <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
                        <AiAssist v-model="form.body" type="ticket" :id="ticket.id" class="mr-auto" />
                        <label class="text-sm text-slate-500">
                            Then set status
                            <select v-model="form.status" class="ml-2 rounded-lg border border-slate-300 bg-white py-1.5 pl-3 pr-8 text-sm dark:border-slate-700 dark:bg-slate-950">
                                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </label>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Send reply</button>
                    </div>
                </form>
            </div>

            <aside class="space-y-5">
                <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 text-sm dark:border-slate-800 dark:bg-slate-900">
                    <label class="block">
                        <span class="mb-1 block font-medium">Status</span>
                        <select :value="ticket.status" class="w-full rounded-lg border border-slate-300 bg-white py-2 text-sm dark:border-slate-700 dark:bg-slate-950" @change="update('status', $event.target.value)">
                            <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block font-medium">Priority</span>
                        <select :value="ticket.priority" class="w-full rounded-lg border border-slate-300 bg-white py-2 text-sm dark:border-slate-700 dark:bg-slate-950" @change="update('priority', $event.target.value)">
                            <option v-for="(label, key) in priorities" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block font-medium">Topic</span>
                        <select :value="ticket.category" class="w-full rounded-lg border border-slate-300 bg-white py-2 text-sm dark:border-slate-700 dark:bg-slate-950" @change="update('category', $event.target.value)">
                            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </label>
                </div>

                <dl class="space-y-3 rounded-xl border border-slate-200 bg-white p-5 text-sm dark:border-slate-800 dark:bg-slate-900">
                    <div><dt class="text-xs uppercase tracking-wider text-slate-500">Customer</dt><dd>{{ ticket.name }}<br><a :href="`mailto:${ticket.email}`" class="text-blue-600 hover:underline dark:text-blue-400">{{ ticket.email }}</a></dd></div>
                    <div v-if="ticket.dpanel_version"><dt class="text-xs uppercase tracking-wider text-slate-500">dPanel version</dt><dd>{{ ticket.dpanel_version }}</dd></div>
                    <div v-if="ticket.server_os"><dt class="text-xs uppercase tracking-wider text-slate-500">Server OS</dt><dd>{{ ticket.server_os }}</dd></div>
                    <div v-if="ticket.ip_address"><dt class="text-xs uppercase tracking-wider text-slate-500">IP address</dt><dd class="font-mono text-xs">{{ ticket.ip_address }}</dd></div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-500">Customer link</dt>
                        <dd><button type="button" class="text-blue-600 hover:underline dark:text-blue-400" @click="copyLink">{{ copied ? 'Copied!' : 'Copy private link' }}</button></dd>
                    </div>
                    <div v-if="$page.props.auth.can['mail.send']">
                        <dt class="text-xs uppercase tracking-wider text-slate-500">Email</dt>
                        <dd><Link :href="route('admin.mail.index', { to: ticket.email, name: ticket.name, subject: `Re: [${ticket.reference}] ${ticket.subject}` })" class="text-blue-600 hover:underline dark:text-blue-400">Write a separate email</Link></dd>
                    </div>
                </dl>

                <button type="button" class="w-full rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40" @click="destroy">Delete ticket</button>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
