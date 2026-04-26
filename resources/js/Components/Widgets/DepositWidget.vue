<template>
    <div class="flex flex-col w-full h-full bg-[#1A1C20] text-gray-300 font-sans p-4 rounded-xl">
        <!-- Loading State -->
        <div v-if="isLoadingWallet || !serverSetting" class="flex flex-col items-center justify-center h-full min-h-[400px]">
            <svg aria-hidden="true" class="w-10 h-10 text-gray-700 animate-spin fill-primary" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
            </svg>
        </div>

        <!-- Main Content -->
        <div v-else class="space-y-4">
            <!-- Payment Success / QR Code View (PIX) -->
            <div v-if="showPixQRCode" class="flex flex-col items-center animate-fade-in space-y-4">
                <div class="text-center space-y-2">
                    <div class="mx-auto w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-regular fa-qrcode text-3xl text-green-500"></i>
                    </div>
                    <h3 class="text-white font-bold text-lg">{{ $t('Payment Generated!') }}</h3>
                    <p class="text-sm text-gray-400">{{ $t('Scan the QR Code or use copy and paste to finalize.') }}</p>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-lg border-4 border-white/10">
                    <QRCodeVue3 :value="qrcodecopypast" :width="200" :height="200"/>
                </div>

                <div class="w-full space-y-3">
                    <div class="relative">
                        <input type="text" readonly :value="qrcodecopypast" class="w-full bg-[#0c0d10] border border-white/5 rounded-lg pl-3 pr-10 py-3 text-xs text-gray-400 focus:outline-none focus:border-primary/50 text-ellipsis">
                        <button @click="copyQRCode" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 hover:text-white transition-colors">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    
                    <button @click="copyQRCode" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                        <i class="fa-regular fa-copy"></i>
                        <span>{{ $t('Copy code') }}</span>
                    </button>
                    
                     <button @click="resetForm" class="w-full bg-transparent border border-white/10 hover:bg-white/5 text-gray-400 font-bold py-3 px-4 rounded-lg transition-all">
                        {{ $t('New Deposit') }}
                    </button>
                </div>
                
                <div class="text-center pt-2">
                    <div class="text-xs text-gray-500 flex items-center justify-center gap-2">
                         <div v-if="isLoading" class="animate-spin h-3 w-3 border-2 border-primary border-t-transparent rounded-full"></div>
                        <span>{{ $t('Waiting for payment...') }}</span>
                    </div>
                </div>
            </div>

            <!-- Deposit Form -->
            <form v-else @submit.prevent="submitDeposit" class="space-y-4">
                
                 <!-- Currency Selection -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Deposit Currency') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <img :src="`/assets/images/coin/${wallet?.currency || 'BRL'}.png`" class="w-5 h-5" alt="Flag">
                        </div>
                        <select disabled class="block w-full pl-10 pr-4 py-3 bg-[#0c0d10] border border-white/10 rounded-lg text-white appearance-none focus:outline-none focus:border-primary/50 text-sm font-medium cursor-not-allowed opacity-80">
                            <option>{{ wallet?.currency || 'BRL' }}</option>
                        </select>
                         <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div v-if="isStripeGateway" class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Payment methods') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fa-brands fa-cc-stripe text-xl text-indigo-400"></i>
                        </div>
                        <select v-model="paymentType" class="block w-full pl-10 pr-4 py-3 bg-[#0c0d10] border border-white/10 rounded-lg text-white appearance-none focus:outline-none focus:border-primary/50 text-sm font-medium">
                            <option value="card">Stripe (Card)</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                        </div>
                    </div>
                </div>
                <div v-else class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Payment methods') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <img src="/assets/images/pix.png" class="w-5 h-auto" alt="Pix">
                        </div>
                        <select v-model="paymentType" class="block w-full pl-10 pr-4 py-3 bg-[#0c0d10] border border-white/10 rounded-lg text-white appearance-none focus:outline-none focus:border-primary/50 text-sm font-medium">
                            <option value="pix">PIX</option>
                        </select>
                         <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Amount Input -->
                <div class="space-y-1">
                     <div class="flex justify-between items-end">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Deposit Amount') }}</label>
                        <span class="text-[10px] text-gray-600">{{ stateCurrencyFormat(serverSetting?.min_deposit) }} - {{ stateCurrencyFormat(serverSetting?.max_deposit) }}</span>
                    </div>
                    <div class="relative group">
                         <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <span class="text-gray-400 font-bold text-sm">{{ wallet?.symbol }}</span>
                        </div>
                        <input 
                            type="number" 
                            v-model="deposit.amount" 
                            class="block w-full pl-10 pr-4 py-3 bg-[#0c0d10] border border-white/10 rounded-lg text-white placeholder-gray-600 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all font-bold text-lg"
                            :placeholder="$t('0.00')"
                            step="0.01"
                            required
                        >
                    </div>
                </div>

                <!-- Bonus Toggle (hidden for Stripe if needed, or shown for both) -->
                <div class="p-3 bg-gradient-to-r from-[#0c0d10] to-[#131418] border border-white/10 rounded-lg flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-white mb-0.5">
                            {{ $t('Get extra bonus') }} <span class="text-primary">{{ serverSetting?.initial_bonus }}%</span>
                        </p>
                        <p class="text-[10px] text-gray-500 leading-tight">
                            {{ $t('On deposits above') }} <span class="text-gray-300">{{ stateCurrencyFormat(serverSetting?.min_deposit) }}</span>
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="deposit.accept_bonus" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                 <!-- Preset Amounts -->
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        v-for="amount in [20, 50, 200]" 
                        :key="amount"
                        @click.prevent="setAmount(amount)"
                        type="button"
                        class="relative py-2 px-3 rounded-lg border border-white/10 bg-[#0c0d10] hover:bg-[#1A1C20] hover:border-primary/50 transition-all group"
                        :class="{'border-primary bg-primary/10 text-primary': parseFloat(deposit.amount) === amount}"
                    >
                        <span class="font-bold text-sm">{{ wallet?.symbol }} {{ amount }}</span>
                         <div v-if="parseFloat(deposit.amount) === amount" class="absolute -top-2 -right-2 bg-primary text-white text-[8px] font-black px-1.5 py-0.5 rounded-full shadow-lg">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </button>
                </div>

                <!-- CPF Input (only for PIX) -->
                <div v-if="!isStripeGateway" class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('CPF') }}</label>
                    <div class="relative">
                         <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fa-regular fa-id-card text-gray-500"></i>
                        </div>
                        <input 
                            type="text" 
                            v-model="deposit.cpf" 
                            v-maska
                            data-maska="['###.###.###-##', '##.###.###/####-##']"
                            class="block w-full pl-10 pr-4 py-3 bg-[#0c0d10] border border-white/10 rounded-lg text-white placeholder-gray-600 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-sm font-medium"
                            :placeholder="$t('Enter CPF')"
                            required
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    :disabled="isLoading || !serverSetting"
                    class="w-full bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary text-white font-black uppercase text-sm tracking-wider py-4 rounded-xl shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed mt-2"
                >
                    <span v-if="!isLoading">{{ isStripeGateway ? $t('Pay with Stripe') : $t('Deposit') }}</span>
                    <div v-else class="flex items-center justify-center">
                         <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ $t('Processing...') }}
                    </div>
                </button>
                
                 <!-- Security Footer -->
                <div class="flex items-center justify-center gap-2 mt-4 opacity-50">
                    <i class="fa-solid fa-shield-check text-xs text-gray-400"></i>
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">{{ $t('24/7 Secure Support') }}</span>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
    import { useToast } from "vue-toastification";
    import HttpApi from "@/Services/HttpApi.js";
    import QRCodeVue3 from "qrcode-vue3";
    import { useAuthStore } from "@/Stores/Auth.js";
    import { useSettingStore } from "@/Stores/SettingStore.js";
    import { Modal } from 'flowbite';

    export default {
        props: ['showMobile', 'title', 'isFull'],
        components: { QRCodeVue3 },
        data() {
            return {
                isLoading: false,
                isLoadingWallet: false,
                serverSetting: null,
                wallet: null,
                deposit: {
                    amount: '',
                    cpf: '',
                    paymentType: 'pix',
                    gateway: '',
                    accept_bonus: true
                },
                showPixQRCode: false,
                qrcodecopypast: '',
                idTransaction: '',
                intervalId: null,
                paymentType: 'pix',
            }
        },
        computed: {
            isAuthenticated() {
                const authStore = useAuthStore();
                return authStore.isAuth;
            },
            isStripeGateway() {
                return this.determineGateway() === 'stripe';
            },
        },
        methods: {
             stateCurrencyFormat(value) {
                if(value === undefined || value === null) return '0,00';
                 const currency = this.wallet?.currency || 'USD';
                const locale = currency === 'BRL' ? 'pt-BR' : 'en-US';
                return new Intl.NumberFormat(locale, { style: 'currency', currency: currency }).format(value);
            },
            setAmount(amount) {
                this.deposit.amount = amount;
            },
            resetForm() {
                this.showPixQRCode = false;
                this.qrcodecopypast = '';
                this.idTransaction = '';
                clearInterval(this.intervalId);
                this.deposit.amount = '';
            },
            determineGateway() {
                if (this.serverSetting?.stripe_is_enable) {
                    return 'stripe';
                }

                if (this.serverSetting?.blackpearlpay_is_enable) {
                    return 'blackpearlpay';
                }

                if (this.serverSetting?.tribopay_is_enable) {
                    return 'tribopay';
                }

                return 'blackpearlpay';
            },
            submitDeposit() {
                const _toast = useToast();
                
                if (!this.deposit.amount) {
                    _toast.error(this.$t('Please enter an amount'));
                    return;
                }
                
                const amount = parseFloat(this.deposit.amount);
                const min = parseFloat(this.serverSetting.min_deposit);
                const max = parseFloat(this.serverSetting.max_deposit);

                if (amount < min) {
                    _toast.error(this.$t('Minimum value is') + ' ' + this.stateCurrencyFormat(min));
                    return;
                }
                
                if (amount > max) {
                    _toast.error(this.$t('Maximum value is') + ' ' + this.stateCurrencyFormat(max));
                    return;
                }

                const gateway = this.determineGateway();

                if (gateway !== 'stripe' && !this.deposit.cpf) {
                    _toast.error(this.$t('Enter your CPF'));
                    return;
                }

                this.isLoading = true;
                this.deposit.gateway = gateway;
                this.deposit.paymentType = gateway === 'stripe' ? 'card' : 'pix';

                HttpApi.post('wallet/deposit/payment', this.deposit).then(response => {
                    this.isLoading = false;

                    if (gateway === 'stripe' && response.data.type === 'stripe_checkout' && response.data.url) {
                        window.location.href = response.data.url;
                        return;
                    }

                    this.showPixQRCode = true;
                    this.idTransaction = response.data.idTransaction;
                    this.qrcodecopypast = response.data.qrcode;

                    this.intervalId = setInterval(() => {
                        this.checkTransactions(this.idTransaction);
                    }, 5000);

                }).catch(error => {
                    this.isLoading = false;
                    if (error.response && error.response.data) {
                        if (typeof error.response.data === 'object') {
                            Object.values(error.response.data).forEach(msg => _toast.error(msg));
                        } else {
                            _toast.error(this.$t('Error processing deposit'));
                        }
                    } else {
                        _toast.error(this.$t('Error processing deposit'));
                    }
                });
            },
            checkTransactions(idTransaction) {
                const gateway = this.deposit.gateway || this.determineGateway();
                HttpApi.post(`${gateway}/consult-status-transaction`, { idTransaction: idTransaction })
                .then(response => {
                     // Assuming 200/success means paid
                    if(response.data.status === 'PAID' || response.data.status === 'paid') {
                        const _toast = useToast();
                        _toast.success(this.$t('Deposit confirmed successfully!'));
                        clearInterval(this.intervalId);
                        this.resetForm();
                        this.getWallet();
                        this.$emit('close-modal');
                    }
                }).catch(error => {
                    console.error('Check Status Error:', error);
                });
            },
            copyQRCode() {
                const _toast = useToast();
                navigator.clipboard.writeText(this.qrcodecopypast).then(() => {
                     _toast.success(this.$t('Pix Code copied!'));
                });
            },
            async fetchData() {
                this.isLoadingWallet = true;
                try {
                     const [walletRes] = await Promise.all([
                        HttpApi.get('profile/wallet'),
                    ]);
                    
                    this.wallet = walletRes.data.wallet;
                    
                    const settingStore = useSettingStore();
                    this.serverSetting = settingStore.setting;
                    
                    this.isLoadingWallet = false;
                } catch (e) {
                    console.error(e);
                    this.isLoadingWallet = false;
                }
            },
               getWallet: function() {
                const _this = this;
                _this.isLoadingWallet = true;

                HttpApi.get('profile/wallet')
                    .then(response => {
                        _this.wallet = response.data.wallet;
                         _this.isLoadingWallet = false;
                    })
                    .catch(error => {
                         _this.isLoadingWallet = false;
                    });
            },
            handleUrlStatus() {
                const urlParams = new URLSearchParams(window.location.search);
                const status = urlParams.get('status');

                if (status === 'paid') {
                    const _toast = useToast();
                    _toast.success(this.$t('Deposit confirmed successfully!'));
                    this.getWallet();
                    window.history.replaceState({}, document.title, window.location.pathname);
                } else if (status === 'cancelled') {
                    const _toast = useToast();
                    _toast.error(this.$t('Payment was cancelled.'));
                    window.history.replaceState({}, document.title, window.location.pathname);
                } else if (status === 'pending') {
                    const _toast = useToast();
                    _toast.info(this.$t('Payment is being processed...'));

                    const sessionId = urlParams.get('session_id') || urlParams.get('idTransaction');
                    if (sessionId) {
                        this.idTransaction = sessionId;
                        this.intervalId = setInterval(() => {
                            this.checkTransactions(sessionId);
                        }, 5000);
                    }

                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            },
        },
        created() {
            if(this.isAuthenticated) {
                const settingStore = useSettingStore();
                this.serverSetting = settingStore.setting;
                
                this.getWallet();
                this.handleUrlStatus();
            }
        },
        beforeUnmount() {
            if(this.intervalId) clearInterval(this.intervalId);
        }
    };
</script>

<style scoped>
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>