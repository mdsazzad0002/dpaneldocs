<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { postJson } from '@/http';

// Fills a reply box with an AI-written draft. Staff can add notes ("say it is
// fixed in 1.4") and the current draft is improved rather than replaced.
const props = defineProps({
    type: { type: String, required: true },
    id: { type: [Number, String], default: null },
    modelValue: { type: String, default: '' },
    to: { type: String, default: '' },
    subject: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const page = usePage();
const allowed = computed(() => page.props.auth.can?.['ai.use']);

const open = ref(false);
const guidance = ref('');
const loading = ref(false);
const error = ref('');

const generate = async () => {
    loading.value = true;
    error.value = '';
    try {
        const { text } = await postJson(route('admin.ai.draft'), {
            type: props.type,
            id: props.id,
            guidance: guidance.value,
            draft: props.modelValue,
            to: props.to,
            subject: props.subject,
        });
        emit('update:modelValue', text);
        open.value = false;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div v-if="allowed" class="relative">
        <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg border border-violet-300 bg-violet-50 px-3 py-1.5 text-sm font-medium text-violet-700 transition hover:bg-violet-100 dark:border-violet-800 dark:bg-violet-950/40 dark:text-violet-300 dark:hover:bg-violet-950"
            @click="open = !open"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
            </svg>
            {{ modelValue ? 'Improve with AI' : 'Write with AI' }}
        </button>

        <div
            v-if="open"
            class="absolute bottom-full left-0 z-30 mb-2 w-80 max-w-[calc(100vw-2rem)] rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-700 dark:bg-slate-900"
        >
            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">What should the reply say? <span class="font-normal text-slate-400">(optional)</span></label>
            <textarea
                v-model="guidance"
                rows="3"
                maxlength="2000"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                placeholder="e.g. Thank them, explain the fix is in v1.4, link the update guide"
                @keydown.ctrl.enter="generate"
            />
            <p v-if="error" class="mt-2 text-xs text-red-600 dark:text-red-400">{{ error }}</p>
            <div class="mt-3 flex items-center justify-between gap-2">
                <p class="text-[11px] text-slate-400">Review the draft before sending.</p>
                <button
                    type="button"
                    :disabled="loading"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-violet-700 disabled:opacity-60"
                    @click="generate"
                >
                    <svg v-if="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25" /><path fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z" class="opacity-75" /></svg>
                    {{ loading ? 'Writing…' : 'Generate' }}
                </button>
            </div>
        </div>
    </div>
</template>
