<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    donations: Object,
    status: String,
    counts: Object,
    goal: Object,
    settings: Object,
});

const tabs = [
    { key: 'pending', label: 'To check' },
    { key: 'verified', label: 'Verified' },
    { key: 'rejected', label: 'Rejected' },
];

const money = (amount, currency = props.goal.currency) => `${currency} ${Number(amount).toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
const when = (date) => new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });

const setStatus = (donation, status, notify = false) => router.patch(route('admin.donations.update', donation.id), { status, notify }, { preserveScroll: true });

const destroy = (donation) => {
    if (confirm('Delete this donation record?')) {
        router.delete(route('admin.donations.destroy', donation.id), { preserveScroll: true });
    }
};

// Goal & page settings
const settingsForm = useForm({
    enabled: props.settings.enabled === '1',
    title: props.settings.title,
    description: props.settings.description,
    goal_amount: props.settings.goal_amount,
    currency: props.settings.currency,
    raised_offset: props.settings.raised_offset,
    thank_you: props.settings.thank_you,
});
const saveSettings = () => settingsForm.put(route('admin.donations.settings'), { preserveScroll: true });

const input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950';
</script>

<template>
    <Head title="Donations" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Donations</h2>
        </template>

        <div class="space-y-6">
            <!-- Goal progress -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">{{ goal.title }}</p>
                        <p class="mt-1 text-3xl font-bold tracking-tight">{{ money(goal.raised) }} <span class="text-base font-normal text-slate-500">of {{ money(goal.amount) }}</span></p>
                    </div>
                    <div class="text-right text-sm text-slate-500">
                        <p>{{ goal.supporters }} verified supporters</p>
                        <a :href="route('donate.index')" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">{{ goal.enabled ? 'View donate page' : 'Donate page is hidden' }}</a>
                    </div>
                </div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-500" :style="{ width: `${goal.percent}%` }" />
                </div>
                <p class="mt-2 text-sm font-semibold">{{ goal.percent }}%</p>
            </div>

            <div class="grid gap-6 2xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                <!-- Donation reports -->
                <section class="space-y-4">
                    <div class="flex gap-2">
                        <Link
                            v-for="tab in tabs"
                            :key="tab.key"
                            :href="route('admin.donations.index', { status: tab.key })"
                            preserve-scroll
                            class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                            :class="status === tab.key ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                        >{{ tab.label }} <span class="opacity-70">{{ counts[tab.key] ?? 0 }}</span></Link>
                    </div>

                    <article v-for="donation in donations.data" :key="donation.id" class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xl font-bold">{{ money(donation.amount, donation.currency) }}</p>
                                <p class="text-sm">{{ donation.name }} <span v-if="donation.email" class="text-slate-500">· {{ donation.email }}</span></p>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <p>{{ when(donation.created_at) }}</p>
                                <p>{{ donation.is_public ? 'Show on supporters list' : 'Anonymous' }}</p>
                            </div>
                        </div>
                        <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                            <div><dt class="text-xs uppercase tracking-wider text-slate-500">Sent with</dt><dd>{{ donation.method?.label ?? 'Other' }}</dd></div>
                            <div><dt class="text-xs uppercase tracking-wider text-slate-500">Transaction ID</dt><dd class="font-mono">{{ donation.transaction_id }}</dd></div>
                        </dl>
                        <p v-if="donation.message" class="mt-3 whitespace-pre-line rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:bg-slate-950 dark:text-slate-300">{{ donation.message }}</p>
                        <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                            <template v-if="donation.status !== 'verified'">
                                <button type="button" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700" @click="setStatus(donation, 'verified', !!donation.email)">
                                    Verify{{ donation.email ? ' & send thanks' : '' }}
                                </button>
                                <button v-if="donation.email" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800" @click="setStatus(donation, 'verified')">Verify silently</button>
                            </template>
                            <button v-if="donation.status !== 'rejected'" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800" @click="setStatus(donation, 'rejected')">{{ donation.status === 'verified' ? 'Unverify' : 'Not received' }}</button>
                            <Link v-if="donation.email && $page.props.auth.can['mail.send']" :href="route('admin.mail.index', { to: donation.email, name: donation.name, subject: 'Your donation to dPanel' })" class="rounded-lg px-3 py-1.5 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/40">Email</Link>
                            <button type="button" class="ml-auto rounded-lg px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40" @click="destroy(donation)">Delete</button>
                        </div>
                    </article>

                    <p v-if="!donations.data.length" class="rounded-xl border border-dashed border-slate-300 py-12 text-center text-sm text-slate-500 dark:border-slate-700">Nothing here.</p>
                    <Pagination :links="donations.links" />
                </section>

                <div class="space-y-6">
                    <!-- Goal settings -->
                    <form class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="saveSettings">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold">Donate page &amp; goal</h3>
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="settingsForm.enabled" type="checkbox" class="rounded border-slate-300 text-blue-600 dark:border-slate-700 dark:bg-slate-900" />
                                Page visible
                            </label>
                        </div>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Goal title</span>
                            <input v-model="settingsForm.title" required maxlength="120" :class="input" />
                            <span v-if="settingsForm.errors.title" class="mt-1 block text-xs text-red-600">{{ settingsForm.errors.title }}</span>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Why you need it</span>
                            <textarea v-model="settingsForm.description" rows="4" :class="input" />
                        </label>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <label class="block text-sm">
                                <span class="mb-1 block font-medium">Goal amount</span>
                                <input v-model="settingsForm.goal_amount" type="number" min="0" step="any" required :class="input" />
                                <span v-if="settingsForm.errors.goal_amount" class="mt-1 block text-xs text-red-600">{{ settingsForm.errors.goal_amount }}</span>
                            </label>
                            <label class="block text-sm">
                                <span class="mb-1 block font-medium">Currency</span>
                                <input v-model="settingsForm.currency" required maxlength="3" class="uppercase" :class="input" placeholder="BDT" />
                                <span v-if="settingsForm.errors.currency" class="mt-1 block text-xs text-red-600">{{ settingsForm.errors.currency }}</span>
                            </label>
                            <label class="block text-sm">
                                <span class="mb-1 block font-medium">Received elsewhere</span>
                                <input v-model="settingsForm.raised_offset" type="number" min="0" step="any" :class="input" />
                            </label>
                        </div>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">Thank-you email</span>
                            <textarea v-model="settingsForm.thank_you" rows="3" :class="input" />
                        </label>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="settingsForm.processing" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
