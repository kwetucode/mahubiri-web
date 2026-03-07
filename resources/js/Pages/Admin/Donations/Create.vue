<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { useForm, router, Link, Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

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

// Tracking state
const trackingState = ref('idle'); // idle | pending | completed | failed
const trackingUuid = ref(null);
const trackingMessage = ref('');
const failureReason = ref('');
let pollInterval = null;

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

const startPolling = (uuid) => {
    stopPolling();
    let attempts = 0;
    const maxAttempts = 60; // Poll for ~5 minutes (every 5s)

    pollInterval = setInterval(async () => {
        attempts++;
        if (attempts > maxAttempts) {
            stopPolling();
            return;
        }

        try {
            const response = await fetch(`/admin/donations/${uuid}/status`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) return;

            const data = await response.json();

            if (data.status === 'completed') {
                trackingState.value = 'completed';
                trackingMessage.value = t('donationCreate.paymentCompleted');
                stopPolling();
            } else if (data.status === 'failed') {
                trackingState.value = 'failed';
                failureReason.value = data.failure_reason || t('donationCreate.paymentFailed');
                stopPolling();
            }
        } catch {
            // Silently retry on network errors
        }
    }, 5000);
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

onUnmounted(() => stopPolling());

const submit = () => {
    form.post('/admin/donations', {
        preserveScroll: true,
        onSuccess: () => {
            const flash = page.props.flash;
            const uuid = flash?.donation_uuid;
            const msg = flash?.success;

            trackingMessage.value = msg || t('donationCreate.waitingConfirmation');
            trackingState.value = 'pending';
            form.reset();

            if (uuid) {
                trackingUuid.value = uuid;
                startPolling(uuid);
            }
        },
    });
};

const resetTracking = () => {
    stopPolling();
    trackingState.value = 'idle';
    trackingUuid.value = null;
    trackingMessage.value = '';
    failureReason.value = '';
};
</script>

<template>
    <Head :title="t('donationCreate.title')" />
    <AdminLayout :title="t('donationCreate.title')">
        <div class="max-w-lg space-y-4">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: t('nav.dashboard'), href: '/admin/dashboard' },
                { label: t('donationCreate.title') },
            ]" />

            <!-- Sandbox banner -->
            <div v-if="isSandbox" class="flex items-center gap-2.5 px-3.5 py-2.5 bg-orange-50 border border-orange-200 rounded-xl text-xs font-medium text-orange-700">
                <svg class="w-4 h-4 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                {{ t('donationCreate.sandboxWarning') }}
            </div>

            <!-- Tracking status banner -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <!-- Pending state -->
                <div v-if="trackingState === 'pending'" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center space-y-3">
                    <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-amber-100">
                        <svg class="w-6 h-6 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-800">{{ t('donationCreate.paymentInitiated') }}</h3>
                        <p class="text-xs text-amber-700 mt-1">{{ trackingMessage }}</p>
                    </div>
                    <div class="flex items-center justify-center gap-1.5 text-xs text-amber-600">
                        <span class="inline-block w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                        {{ t('donationCreate.checkingStatus') }}
                    </div>
                </div>

                <!-- Completed state -->
                <div v-else-if="trackingState === 'completed'" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-center space-y-3">
                    <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-800">{{ t('donationCreate.successTitle') }}</h3>
                        <p class="text-xs text-emerald-700 mt-1">{{ trackingMessage }}</p>
                    </div>
                    <button @click="resetTracking" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-xl transition-colors">
                        {{ t('donationCreate.newDonation') }}
                    </button>
                </div>

                <!-- Failed state -->
                <div v-else-if="trackingState === 'failed'" class="bg-red-50 border border-red-200 rounded-2xl p-5 text-center space-y-3">
                    <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-red-100">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-800">{{ t('donationCreate.paymentFailed') }}</h3>
                        <p v-if="failureReason" class="text-xs text-red-700 mt-1">{{ failureReason }}</p>
                    </div>
                    <button @click="resetTracking" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 rounded-xl transition-colors">
                        {{ t('donationCreate.newDonation') }}
                    </button>
                </div>
            </Transition>

            <!-- Form Card (hidden when tracking) -->
            <form v-if="trackingState === 'idle'" @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200/60 shadow-sm">
                <!-- Recipient badge -->
                <div class="px-5 pt-5 pb-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary/5 rounded-lg border border-primary/10">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-xs font-semibold text-gray-700">{{ t('donationCreate.recipientPlatform') }}</span>
                    </div>
                </div>

                <div class="px-5 pb-5 space-y-4">
                    <!-- Country + Amount row -->
                    <div class="grid grid-cols-5 gap-3">
                        <div class="col-span-2 space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-600">{{ t('donationCreate.country') }}</label>
                            <select
                                v-model="form.country_code"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                            >
                                <option v-for="c in countries" :key="c.code" :value="c.code">
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-3 space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-600">{{ t('donationCreate.amount') }}</label>
                            <div class="relative">
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    :min="minAmount"
                                    :placeholder="`Min. ${minAmount}`"
                                    class="w-full px-3 py-2.5 pr-14 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                                    :class="{ 'border-red-300 focus:ring-red-200': form.errors.amount }"
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">{{ currency }}</span>
                            </div>
                            <p v-if="form.errors.amount" class="text-xs text-red-500">{{ form.errors.amount }}</p>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-600">{{ t('donationCreate.phoneNumber') }}</label>
                        <input
                            v-model="form.phone_number"
                            type="text"
                            :placeholder="`${phonePrefix}812345678`"
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                            :class="{ 'border-red-300 focus:ring-red-200': form.errors.phone_number }"
                        />
                        <p v-if="form.errors.phone_number" class="text-xs text-red-500">{{ form.errors.phone_number }}</p>
                        <p v-else class="text-[11px] text-gray-400">{{ t('donationCreate.phoneHint') }}</p>
                    </div>

                    <!-- Message -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-600">{{ t('donationCreate.message') }} <span class="text-gray-400 font-normal">({{ t('common.optional') }})</span></label>
                        <textarea
                            v-model="form.message"
                            rows="2"
                            :placeholder="t('donationCreate.messagePlaceholder')"
                            maxlength="500"
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none"
                        ></textarea>
                    </div>

                    <!-- Payment error -->
                    <div v-if="form.errors.payment" class="flex items-center gap-2.5 p-3 bg-red-50 border border-red-200 rounded-xl">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-red-700">{{ form.errors.payment }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-1">
                        <Link
                            href="/admin/dashboard"
                            class="px-4 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                        >
                            {{ t('common.cancel') }}
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? t('donationCreate.processing') : t('donationCreate.submit') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
