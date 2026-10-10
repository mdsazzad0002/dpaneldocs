<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AiAssist from '@/Components/AiAssist.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    mails: Object,
    filters: Object,
    prefill: Object,
});

const form = useForm({
    to_email: props.prefill.to_email,
    to_name: props.prefill.to_name,
    subject: props.prefill.subject,
    body: '',
});

const send = () => form.post(route('admin.mail.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
});

const search = ref(props.filters.search);
const applySearch = () => router.get(route('admin.mail.index'), { search: search.value || undefined }, { preserveState: true, preserveScroll: true });

const open = ref(null);

const replyTo = (mail) => {
    form.to_email = mail.to_email;
    form.to_name = mail.to_name ?? '';
    form.subject = mail.subject.startsWith('Re:') ? mail.subject : `Re: ${mail.subject}`;
    form.body = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const destroy = (mail) => {
    if (confirm('Remove this email from the log?')) {
        router.delete(route('admin.mail.destroy', mail.id), { preserveScroll: true });
    }
};

const when = (date) => new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <Head title="Mail" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Mail</h2>
        </template>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <form class="h-fit space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="send">
                <h3 class="font-semibold">Compose</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium">To (email)</span>
                        <input v-model="form.to_email" type="email" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        <span v-if="form.errors.to_email" class="mt-1 block text-xs text-red-600">{{ form.errors.to_email }}</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium">Name <span class="font-normal text-slate-400">(optional)</span></span>
                        <input v-model="form.to_name" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </label>
                </div>
                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Subject</span>
                    <input v-model="form.subject" required maxlength="200" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    <span v-if="form.errors.subject" class="mt-1 block text-xs text-red-600">{{ form.errors.subject }}</span>
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Message</span>
                    <textarea v-model="form.body" rows="10" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" placeholder="A greeting with the recipient's name is added automatically." />
                    <span v-if="form.errors.body" class="mt-1 block text-xs text-red-600">{{ form.errors.body }}</span>
                </label>
                <div class="flex flex-wrap items-center gap-3">
                    <AiAssist v-model="form.body" type="mail" :to="form.to_name || form.to_email" :subject="form.subject" />
                    <button type="submit" :disabled="form.processing" class="ml-auto rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Send email</button>
                </div>
            </form>

            <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h3 class="font-semibold">Sent mail</h3>
                    <form @submit.prevent="applySearch">
                        <input v-model="search" type="search" placeholder="Search…" class="w-48 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </form>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li v-for="mail in mails.data" :key="mail.id" class="px-5 py-3">
                        <button type="button" class="flex w-full items-start gap-3 text-left" @click="open = open === mail.id ? null : mail.id">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ mail.subject }}</p>
                                <p class="truncate text-xs text-slate-500">To {{ mail.to_name ? `${mail.to_name} <${mail.to_email}>` : mail.to_email }} · {{ when(mail.created_at) }} · {{ mail.user?.name ?? 'system' }}</p>
                            </div>
                            <StatusBadge :status="mail.status === 'sent' ? 'approved' : 'rejected'">{{ mail.status }}</StatusBadge>
                        </button>
                        <div v-if="open === mail.id" class="mt-3 space-y-3">
                            <p v-if="mail.error" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 dark:bg-red-950/40 dark:text-red-300">{{ mail.error }}</p>
                            <p class="whitespace-pre-line rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:bg-slate-950 dark:text-slate-300">{{ mail.body }}</p>
                            <div class="flex gap-3 text-sm">
                                <button type="button" class="font-medium text-blue-600 hover:underline dark:text-blue-400" @click="replyTo(mail)">Write again / follow up</button>
                                <button type="button" class="ml-auto font-medium text-red-600 hover:underline" @click="destroy(mail)">Remove</button>
                            </div>
                        </div>
                    </li>
                    <li v-if="!mails.data.length" class="px-5 py-10 text-center text-sm text-slate-500">No emails sent from the panel yet.</li>
                </ul>
                <div class="px-5 py-3"><Pagination :links="mails.links" /></div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
