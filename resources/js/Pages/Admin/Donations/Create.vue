<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    countries: Array,
    defaultCountry: String,
    isSandbox: Boolean,
});

const form = useForm({
    amount: '',
    phone_number: '',
    country_code: props.defaultCountry || 'DRC',
    message: '',
});

const showSuccessModal = ref(false);
const successMessage = ref('');

const selectedCountry = computed(() => {
    return props.countries?.find(c => c.code === form.country_code) ?? props.countries?.[0];
});

const minAmount = computed(() => {
    const code = form.country_code;
    const mins = { DRC: 2900, CD: 2900, KE: 100, UG: 100 };
    return mins[code] ?? 100;
});

const currency = computed(() => selectedCountry.value?.currency ?? 'CDF');
const phonePrefix = computed(() => selectedCountry.value?.phone_prefix ?? '+243');

const submit = () => {
    form.post('/admin/donations', {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props?.flash;
            if (flash?.success) {
                successMessage.value = flash.success;
                showSuccessModal.value = true;
                form.reset();
            }
        },
    });
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
    successMessage.value = '';
};
</script>

<template>
    <Head :title="t('donationCreate.title')" />
    <AdminLayout :title="t('donationCreate.title')">
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: t('nav.dashboard'), href: '/admin/dashboard' },
                { label: t('donationCreate.title') },
            ]" />

            <!-- Header -->
            <div class="relative overflow-hidden rounded-2xl bg-linear-to-r from-emerald-500/8 via-emerald-500/3 to-transparent border border-emerald-500/10">
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-emerald-500/5 rounded-full blur-2xl"></div>
                <div class="relative px-6 py-5 flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ t('donationCreate.title') }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ t('donationCreate.subtitle') }}</p>
                    </div>
                </div>
            </div>

            <!-- Sandbox banner -->
            <div v-if="isSandbox" class="flex items-center gap-3 px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl">
                <svg class="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="text-sm font-medium text-orange-700">{{ t('donationCreate.sandboxWarning') }}</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200/60 shadow-sm divide-y divide-gray-100">
                <!-- Recipient info -->
                <div class="px-6 py-5">
                    <div class="flex items-center gap-3 p-4 bg-primary/5 rounded-xl border border-primary/10">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ t('donationCreate.recipientPlatform') }}</p>
                            <p class="text-xs text-gray-500">{{ t('donationCreate.recipientDescription') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Country -->
                <div class="px-6 py-5 space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">{{ t('donationCreate.country') }}</label>
                    <select
                        v-model="form.country_code"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                    >
                        <option v-for="c in countries" :key="c.code" :value="c.code">
                            {{ c.name }} ({{ c.currency }})
                        </option>
                    </select>
                </div>

                <!-- Amount -->
                <div class="px-6 py-5 space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">{{ t('donationCreate.amount') }}</label>
                    <div class="relative">
                        <input
                            v-model="form.amount"
                            type="number"
                            :min="minAmount"
                            :placeholder="`${t('donationCreate.minAmount')} ${minAmount} ${currency}`"
                            class="w-full px-4 py-2.5 pr-16 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                            :class="{ 'border-red-300 focus:ring-red-200': form.errors.amount }"
                        />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">{{ currency }}</span>
                    </div>
                    <p v-if="form.errors.amount" class="text-xs text-red-500 mt-1">{{ form.errors.amount }}</p>
                </div>

                <!-- Phone Number -->
                <div class="px-6 py-5 space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">{{ t('donationCreate.phoneNumber') }}</label>
                    <input
                        v-model="form.phone_number"
                        type="text"
                        :placeholder="`${phonePrefix}812345678`"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                        :class="{ 'border-red-300 focus:ring-red-200': form.errors.phone_number }"
                    />
                    <p v-if="form.errors.phone_number" class="text-xs text-red-500 mt-1">{{ form.errors.phone_number }}</p>
                    <p class="text-xs text-gray-400">{{ t('donationCreate.phoneHint') }}</p>
                </div>

                <!-- Message -->
                <div class="px-6 py-5 space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">{{ t('donationCreate.message') }} <span class="text-gray-400 font-normal">({{ t('common.optional') }})</span></label>
                    <textarea
                        v-model="form.message"
                        rows="3"
                        :placeholder="t('donationCreate.messagePlaceholder')"
                        maxlength="500"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-400 text-right">{{ form.message?.length || 0 }} / 500</p>
                </div>

                <!-- Payment error -->
                <div v-if="form.errors.payment" class="px-6 py-4">
                    <div class="flex items-center gap-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-red-700">{{ form.errors.payment }}</p>
                    </div>
                </div>

                <!-- Submit -->
                <div class="px-6 py-5 flex items-center justify-end gap-3">
                    <Link
                        href="/admin/dashboard"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                    >
                        {{ t('common.cancel') }}
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ form.processing ? t('donationCreate.processing') : t('donationCreate.submit') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Success Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showSuccessModal" class="fixed inset-0 z-[9998] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="closeSuccessModal">
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                        appear
                    >
                        <div v-if="showSuccessModal" class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden">
                            <div class="p-6 text-center">
                                <div class="mx-auto flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 mb-4">
                                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ t('donationCreate.successTitle') }}</h3>
                                <p class="text-sm text-gray-600 leading-relaxed">{{ successMessage }}</p>
                            </div>
                            <div class="px-6 pb-5 flex gap-3">
                                <button
                                    @click="closeSuccessModal"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors"
                                >
                                    {{ t('common.close') }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
