<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

const props = defineProps({
    plans: Array,
    storageInfo: Object,
    history: Array,
    countries: Array,
    selectedCountry: String,
    churchName: String,
    isSandbox: Boolean,
});

const selectedPlan = ref(null);
const showPaymentModal = ref(false);

// Tracking state
const trackingState = ref('idle'); // idle | pending | completed | failed
const trackingUuid = ref(null);
const trackingMessage = ref('');
const failureReason = ref('');
let pollInterval = null;

const form = useForm({
    plan: '',
    phone_number: '',
    country_code: props.selectedCountry || 'DRC',
});

const selectedCountryObj = computed(() => {
    return props.countries?.find(c => c.code === form.country_code) ?? props.countries?.[0];
});

const phonePrefix = computed(() => selectedCountryObj.value?.phone_prefix ?? '+243');

const diskStatusColor = computed(() => {
    const s = props.storageInfo?.status;
    if (s === 'critical') return { bg: 'bg-red-500', text: 'text-red-600', light: 'bg-red-50', hex: '#ef4444', label: t('storageUpgrade.critical') };
    if (s === 'warning') return { bg: 'bg-amber-500', text: 'text-amber-600', light: 'bg-amber-50', hex: '#f59e0b', label: t('storageUpgrade.warning') };
    return { bg: 'bg-emerald-500', text: 'text-emerald-600', light: 'bg-emerald-50', hex: '#10b981', label: t('storageUpgrade.good') };
});

const diskArc = computed(() => {
    const pct = props.storageInfo?.used_percentage ?? 0;
    const circumference = 2 * Math.PI * 15.915;
    const used = (pct / 100) * circumference;
    return { used: used.toFixed(2), gap: (circumference - used).toFixed(2) };
});

const selectPlan = (plan) => {
    selectedPlan.value = plan;
    form.plan = plan.key;
    showPaymentModal.value = true;
};

const closeModal = () => {
    showPaymentModal.value = false;
    selectedPlan.value = null;
    form.reset('phone_number');
    form.clearErrors();
};

const startPolling = (uuid) => {
    stopPolling();
    let attempts = 0;
    const maxAttempts = 60;

    pollInterval = setInterval(async () => {
        attempts++;
        if (attempts > maxAttempts) {
            stopPolling();
            return;
        }

        try {
            const response = await fetch(`/admin/storage-upgrade/${uuid}/status`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) return;

            const data = await response.json();

            if (data.status === 'completed') {
                trackingState.value = 'completed';
                trackingMessage.value = t('storageUpgrade.paymentCompleted');
                stopPolling();
            } else if (data.status === 'failed') {
                trackingState.value = 'failed';
                failureReason.value = data.failure_reason || t('storageUpgrade.paymentFailed');
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

const submitPurchase = () => {
    form.post('/admin/storage-upgrade', {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();

            const flash = page.props.flash;
            const uuid = flash?.upgrade_uuid;
            const msg = flash?.success;

            trackingMessage.value = msg || t('storageUpgrade.waitingConfirmation');
            trackingState.value = 'pending';

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

const statusStyles = {
    completed: { bg: 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 ring-emerald-600/10', dot: 'bg-emerald-500', label: 'Complété' },
    pending: { bg: 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 ring-amber-600/10', dot: 'bg-amber-500', label: 'En attente' },
    failed: { bg: 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 ring-red-600/10', dot: 'bg-red-500', label: 'Échoué' },
};

const getStatus = (status) => statusStyles[status] || statusStyles.pending;

const planColors = ['from-blue-500 to-blue-600', 'from-primary to-primary-dark', 'from-emerald-500 to-emerald-600'];
const planBgColors = ['bg-blue-50 border-blue-200', 'bg-primary/5 border-primary/20', 'bg-emerald-50 border-emerald-200'];
</script>

<template>
    <Head :title="t('storageUpgrade.title')" />
    <AdminLayout :title="t('storageUpgrade.title')">
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: t('nav.dashboard'), href: '/admin/dashboard' },
                { label: t('storageUpgrade.title') },
            ]" />

            <!-- Header -->
            <div class="relative overflow-hidden rounded-2xl bg-linear-to-r from-primary/8 via-primary/3 to-transparent border border-primary/10">
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-primary/5 rounded-full blur-2xl"></div>
                <div class="relative px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="/logo.png" alt="Mahubiri" class="w-12 h-12 rounded-xl shadow-md shadow-primary/20 object-contain" />
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ t('storageUpgrade.title') }}</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ t('storageUpgrade.subtitle', { church: churchName }) }}</p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold ring-1 shrink-0 self-start sm:self-auto"
                        :class="[diskStatusColor.light, diskStatusColor.text]"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="diskStatusColor.bg"></span>
                        {{ diskStatusColor.label }}
                    </span>
                </div>
            </div>

            <!-- Sandbox banner -->
            <div v-if="isSandbox" class="flex items-center gap-3 px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl">
                <svg class="w-5 h-5 text-orange-500 dark:text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="text-sm font-medium text-orange-700 dark:text-orange-400">{{ t('storageUpgrade.sandboxWarning') }}</p>
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
                <!-- Pending -->
                <div v-if="trackingState === 'pending'" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 text-center space-y-3">
                    <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/40">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300">{{ t('storageUpgrade.paymentInitiated') }}</h3>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">{{ trackingMessage }}</p>
                    </div>
                    <div class="flex items-center justify-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
                        <span class="inline-block w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                        {{ t('storageUpgrade.checkingStatus') }}
                    </div>
                </div>

                <!-- Completed -->
                <div v-else-if="trackingState === 'completed'" class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-5 text-center space-y-3">
                    <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">{{ t('storageUpgrade.paymentCompleted') }}</h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-1">{{ trackingMessage }}</p>
                    </div>
                    <button @click="resetTracking" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 rounded-xl transition-colors">
                        {{ t('storageUpgrade.backToPlans') }}
                    </button>
                </div>

                <!-- Failed -->
                <div v-else-if="trackingState === 'failed'" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5 text-center space-y-3">
                    <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/40">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-300">{{ t('storageUpgrade.paymentFailed') }}</h3>
                        <p v-if="failureReason" class="text-xs text-red-700 dark:text-red-400 mt-1">{{ failureReason }}</p>
                    </div>
                    <button @click="resetTracking" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-xl transition-colors">
                        {{ t('storageUpgrade.backToPlans') }}
                    </button>
                </div>
            </Transition>

            <!-- Current storage status -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-primary/10 text-primary">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('storageUpgrade.currentUsage') }}</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('storageUpgrade.quotaOf', { size: storageInfo.current_limit_gb }) }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="flex items-center gap-8">
                        <!-- Donut Chart -->
                        <div class="relative shrink-0" style="width: 120px; height: 120px;">
                            <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                                <circle cx="18" cy="18" r="15.915" fill="none" class="stroke-gray-100 dark:stroke-gray-700" stroke-width="3" />
                                <circle cx="18" cy="18" r="15.915" fill="none" :stroke="diskStatusColor.hex" stroke-width="3" stroke-linecap="round"
                                    :stroke-dasharray="diskArc.used + ' ' + diskArc.gap" stroke-dashoffset="0" class="transition-all duration-700 ease-out" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-lg font-bold text-gray-900 dark:text-white leading-none">{{ storageInfo.used_percentage }}%</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ t('storageUpgrade.used') }}</span>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex-1 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('storageUpgrade.used') }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ storageInfo.used_gb >= 1 ? storageInfo.used_gb + ' GB' : storageInfo.used_mb + ' MB' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('storageUpgrade.available') }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ storageInfo.remaining_gb }} GB</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('storageUpgrade.quota') }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ storageInfo.current_limit_gb }} GB</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('storageUpgrade.status') }}</p>
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold" :class="[diskStatusColor.light, diskStatusColor.text]">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="diskStatusColor.bg"></span>
                                    {{ diskStatusColor.label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plans -->
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ t('storageUpgrade.choosePlan') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                        v-for="(plan, i) in plans"
                        :key="plan.key"
                        class="relative rounded-2xl border-2 p-6 transition-all duration-200 hover:shadow-lg cursor-pointer group"
                        :class="planBgColors[i]"
                        @click="selectPlan(plan)"
                    >
                        <!-- Popular badge for middle plan -->
                        <span v-if="i === 1" class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-primary text-white text-[10px] font-bold rounded-full uppercase tracking-wide">
                            {{ t('storageUpgrade.popular') }}
                        </span>

                        <div class="text-center space-y-4">
                            <div class="flex items-center justify-center w-14 h-14 mx-auto rounded-xl bg-linear-to-br text-white" :class="planColors[i]">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">+{{ plan.extra_gb }} GB</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ plan.label }}</p>
                            </div>

                            <div>
                                <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ plan.price.toLocaleString() }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ plan.currency }}</p>
                            </div>

                            <button
                                class="w-full py-2.5 text-sm font-semibold text-white rounded-xl transition-all group-hover:shadow-md"
                                :class="[`bg-linear-to-r ${planColors[i]}`]"
                            >
                                {{ t('storageUpgrade.buyNow') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase History -->
            <div v-if="history && history.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('storageUpgrade.history') }}</h3>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ t('storageUpgrade.historyDescription') }}</p>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    <div v-for="item in history" :key="item.id" class="px-6 py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">+{{ item.extra_gb }} GB</p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ item.created_at_human }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0">
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ item.formatted_amount }}</span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[11px] font-semibold ring-1" :class="getStatus(item.status).bg">
                                <span class="w-1.5 h-1.5 rounded-full" :class="getStatus(item.status).dot"></span>
                                {{ getStatus(item.status).label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Modal -->
            <Teleport to="body">
                <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape="closeModal">
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="closeModal"></div>

                    <!-- Modal -->
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ t('storageUpgrade.confirmPurchase') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ t('storageUpgrade.paymentViaMobile') }}</p>
                            </div>
                            <button @click="closeModal" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Plan summary -->
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">+{{ selectedPlan?.extra_gb }} GB</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ selectedPlan?.label }}</p>
                                    </div>
                                </div>
                                <p class="text-lg font-extrabold text-gray-900 dark:text-white">{{ selectedPlan?.price?.toLocaleString() }} {{ selectedPlan?.currency }}</p>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitPurchase" class="px-6 py-5 space-y-4">
                            <!-- Country -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('storageUpgrade.country') }}</label>
                                <select
                                    v-model="form.country_code"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:text-gray-200 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                                >
                                    <option v-for="c in countries" :key="c.code" :value="c.code">
                                        {{ c.name }} ({{ c.currency }})
                                    </option>
                                </select>
                            </div>

                            <!-- Phone Number -->
                            <div class="space-y-1.5">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('storageUpgrade.phoneNumber') }}</label>
                                <input
                                    v-model="form.phone_number"
                                    type="text"
                                    :placeholder="`${phonePrefix}812345678`"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:text-gray-200 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                                    :class="{ 'border-red-300 focus:ring-red-200': form.errors.phone_number }"
                                />
                                <p v-if="form.errors.phone_number" class="text-xs text-red-500">{{ form.errors.phone_number }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('storageUpgrade.phoneHint') }}</p>
                            </div>

                            <!-- Payment error -->
                            <div v-if="form.errors.payment" class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-red-700 dark:text-red-400">{{ form.errors.payment }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">
                                    {{ t('common.cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg v-if="form.processing" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? t('storageUpgrade.processing') : t('storageUpgrade.pay') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>
        </div>
    </AdminLayout>
</template>
