<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <section class="space-y-6">
            <div class="space-y-2">
                <p class="font-mono text-xs uppercase tracking-[0.32em] text-emerald-300/80">
                    Admin portal
                </p>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-50 sm:text-3xl">
                    Sign in to dPanel
                </h2>
                <p class="text-sm leading-6 text-slate-300">
                    Use your administrative credentials to continue.
                </p>
            </div>

            <div
                v-if="status"
                class="rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100"
            >
                {{ status }}
            </div>

            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-200">Admin Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="root@host.local"
                        class="mt-2 block w-full rounded-xl border border-white/10 bg-black px-4 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 transition focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-200">Access Key</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter secure credential"
                        class="mt-2 block w-full rounded-xl border border-white/10 bg-black px-4 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 transition focus:border-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex cursor-pointer items-center">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 rounded border-white/20 bg-black text-emerald-400 focus:ring-emerald-400 focus:ring-offset-0"
                        />
                        <span class="ms-2 text-sm text-slate-300">Persist session</span>
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-emerald-300 transition hover:text-emerald-200"
                    >
                        Forgot password?
                    </Link>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="{ 'opacity-25': form.processing }"
                    class="w-full rounded-xl bg-gradient-to-r from-emerald-400 to-cyan-400 py-3 text-center text-sm font-semibold uppercase tracking-[0.14em] text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-cyan-300"
                >
                    {{ form.processing ? 'Authenticating...' : 'Login to Server' }}
                </button>
            </form>
        </section>
    </GuestLayout>
</template>
