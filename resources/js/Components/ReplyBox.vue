<script setup>
import AiAssist from '@/Components/AiAssist.vue';
import { useForm } from '@inertiajs/vue3';

// A reply editor with an AI draft button and an "also email it" option, used
// for review responses and docs comment replies.
const props = defineProps({
    action: { type: String, required: true },
    field: { type: String, default: 'body' },
    initial: { type: String, default: '' },
    aiType: { type: String, required: true },
    aiId: { type: [Number, String], required: true },
    email: { type: String, default: null },
    placeholder: { type: String, default: 'Write a reply…' },
    submitLabel: { type: String, default: 'Post reply' },
    resetOnSuccess: { type: Boolean, default: true },
});

const emit = defineEmits(['done']);

const form = useForm({
    [props.field]: props.initial,
    notify: Boolean(props.email),
});

const submit = () => form.post(props.action, {
    preserveScroll: true,
    onSuccess: () => {
        if (props.resetOnSuccess) {
            form.reset(props.field);
        }
        emit('done');
    },
});
</script>

<template>
    <form class="space-y-3" @submit.prevent="submit">
        <textarea
            v-model="form[field]"
            rows="5"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
            :placeholder="placeholder"
        />
        <p v-if="form.errors[field]" class="text-sm text-red-600">{{ form.errors[field] }}</p>
        <div class="flex flex-wrap items-center gap-3">
            <AiAssist v-model="form[field]" :type="aiType" :id="aiId" />
            <label v-if="email" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input v-model="form.notify" type="checkbox" class="rounded border-slate-300 text-blue-600 dark:border-slate-700 dark:bg-slate-900" />
                Also email to {{ email }}
            </label>
            <button type="submit" :disabled="form.processing" class="ml-auto rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">{{ submitLabel }}</button>
        </div>
    </form>
</template>
