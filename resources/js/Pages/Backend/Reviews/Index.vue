<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Stars from '@/Components/Stars.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    reviews: Object,
    status: String,
    counts: Object,
    rating: Object,
});

const tabs = [
    { key: 'pending', label: 'Pending' },
    { key: 'approved', label: 'Published' },
    { key: 'rejected', label: 'Rejected' },
];

const setStatus = (review, status) => router.patch(route('admin.reviews.update', review.id), { status }, { preserveScroll: true });

const destroy = (review) => {
    if (confirm('Delete this review permanently?')) {
        router.delete(route('admin.reviews.destroy', review.id), { preserveScroll: true });
    }
};

const when = (date) => new Date(date).toLocaleDateString(undefined, { dateStyle: 'medium' });
</script>

<template>
    <Head title="Reviews" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Reviews</h2>
        </template>

        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex gap-2">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="route('admin.reviews.index', { status: tab.key })"
                        class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                        :class="status === tab.key ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                    >{{ tab.label }} <span class="opacity-70">{{ counts[tab.key] ?? 0 }}</span></Link>
                </div>
                <p class="text-sm text-slate-500">
                    Public rating: <strong class="text-slate-900 dark:text-white">{{ rating.count ? rating.average.toFixed(1) : '—' }}</strong> from {{ rating.count }} reviews
                    · <a :href="route('reviews.index')" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">View page</a>
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <article v-for="review in reviews.data" :key="review.id" class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                        <Stars :value="review.rating" />
                        <span class="text-xs text-slate-500">{{ when(review.created_at) }}</span>
                    </div>
                    <h3 class="mt-2 font-semibold">{{ review.title }}</h3>
                    <p class="mt-2 flex-1 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-400">{{ review.body }}</p>
                    <p class="mt-4 text-xs text-slate-500">
                        {{ review.name }}<template v-if="review.company"> · {{ review.company }}</template>
                        · <a :href="`mailto:${review.email}`" class="hover:underline">{{ review.email }}</a>
                        <template v-if="review.ip_address"> · {{ review.ip_address }}</template>
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <button v-if="review.status !== 'approved'" type="button" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700" @click="setStatus(review, 'approved')">Approve &amp; publish</button>
                        <button v-if="review.status !== 'rejected'" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800" @click="setStatus(review, 'rejected')">{{ review.status === 'approved' ? 'Unpublish' : 'Reject' }}</button>
                        <button type="button" class="ml-auto rounded-lg px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40" @click="destroy(review)">Delete</button>
                    </div>
                </article>
            </div>

            <p v-if="!reviews.data.length" class="rounded-xl border border-dashed border-slate-300 py-12 text-center text-sm text-slate-500 dark:border-slate-700">Nothing here.</p>

            <Pagination :links="reviews.links" />
        </div>
    </AuthenticatedLayout>
</template>
