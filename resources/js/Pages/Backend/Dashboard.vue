<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Stars from '@/Components/Stars.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    lastSync: Object,
    recentTickets: Array,
    pendingReviews: Array,
    pendingComments: Array,
});

const page = usePage();
const can = page.props.auth.can;
const goal = props.stats.goal;
const money = (amount) => `${goal.currency} ${Number(amount).toLocaleString(undefined, { maximumFractionDigits: 2 })}`;

const cards = [
    { label: 'Open tickets', value: props.stats.open_tickets, hint: `${props.stats.tickets_total} total`, href: 'admin.tickets.index', params: { status: 'open' }, permission: 'tickets.manage' },
    { label: 'Comments waiting', value: props.stats.pending_comments, hint: 'On documentation pages', href: 'admin.comments.index', permission: 'comments.manage' },
    { label: 'Reviews to moderate', value: props.stats.pending_reviews, hint: 'Waiting for approval', href: 'admin.reviews.index', permission: 'reviews.manage' },
    { label: 'Average rating', value: props.stats.rating.count ? props.stats.rating.average.toFixed(1) : '—', hint: `${props.stats.rating.count} published reviews`, href: 'admin.reviews.index', params: { status: 'approved' }, permission: 'reviews.manage' },
    { label: 'Docs found helpful', value: props.stats.feedback_helpful === null ? '—' : `${props.stats.feedback_helpful}%`, hint: `${props.stats.feedback_total} votes`, href: 'admin.feedback.index', permission: 'feedback.view' },
    { label: 'Docs last synced', value: props.lastSync ? new Date(props.lastSync.created_at).toLocaleDateString(undefined, { dateStyle: 'medium' }) : 'Never', hint: props.lastSync ? `${props.lastSync.status} · ${props.lastSync.changed?.length ?? 0} pages updated` : 'Pull the docs from GitHub', href: 'admin.docs.index', permission: 'docs.sync' },
    { label: 'Donations raised', value: money(goal.raised), hint: `${goal.percent}% of ${money(goal.amount)} · ${props.stats.pending_donations} to check`, href: 'admin.donations.index', permission: 'donations.manage' },
].filter((card) => can[card.permission]);

const when = (date) => new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Help desk</h2>
        </template>

        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-semibold">Welcome back, {{ page.props.auth.user.name }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tickets, comments, reviews, documentation and donations at a glance.</p>
            </div>

            <div v-if="cards.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <Link
                    v-for="card in cards"
                    :key="card.label"
                    :href="route(card.href, card.params ?? {})"
                    class="rounded-xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</div>
                    <div class="mt-2 text-3xl font-bold tracking-tight">{{ card.value }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ card.hint }}</div>
                </Link>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
                <section v-if="can['tickets.manage']" class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h3 class="font-semibold">Recent tickets</h3>
                        <Link :href="route('admin.tickets.index')" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">View all</Link>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="ticket in recentTickets" :key="ticket.id">
                            <Link :href="route('admin.tickets.show', ticket.id)" class="flex items-center gap-4 px-5 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ ticket.subject }}</p>
                                    <p class="text-xs text-slate-500">{{ ticket.reference }} · {{ ticket.name }} · {{ when(ticket.last_activity_at) }}</p>
                                </div>
                                <StatusBadge v-if="ticket.priority === 'high'" status="high">urgent</StatusBadge>
                                <StatusBadge :status="ticket.status" />
                            </Link>
                        </li>
                        <li v-if="!recentTickets.length" class="px-5 py-10 text-center text-sm text-slate-500">No tickets yet.</li>
                    </ul>
                </section>

                <div class="space-y-6">
                <section v-if="can['comments.manage']" class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h3 class="font-semibold">Comments to answer</h3>
                        <Link :href="route('admin.comments.index')" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">Answer</Link>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="comment in pendingComments" :key="comment.id" class="px-5 py-3">
                            <p class="line-clamp-2 text-sm">{{ comment.body }}</p>
                            <p class="text-xs text-slate-500">{{ comment.name }} · {{ comment.page }}</p>
                        </li>
                        <li v-if="!pendingComments.length" class="px-5 py-8 text-center text-sm text-slate-500">No comments waiting.</li>
                    </ul>
                </section>

                <section v-if="can['reviews.manage']" class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h3 class="font-semibold">Reviews awaiting approval</h3>
                        <Link :href="route('admin.reviews.index')" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">Moderate</Link>
                    </div>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="review in pendingReviews" :key="review.id" class="px-5 py-3">
                            <Stars :value="review.rating" />
                            <p class="truncate text-sm font-medium">{{ review.title }}</p>
                            <p class="text-xs text-slate-500">{{ review.name }}</p>
                        </li>
                        <li v-if="!pendingReviews.length" class="px-5 py-10 text-center text-sm text-slate-500">All caught up.</li>
                    </ul>
                </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
