<template>
    <BaseLayout>
        <div class="md:w-4/6 2xl:w-4/6 mx-auto p-4 mt-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Sidebar -->
                <div class="col-span-1 hidden md:block">
                    <WalletSideMenu/>
                </div>
                
                <!-- Main Content -->
                <div class="col-span-2 relative">
                    <div v-if="setting != null && wallet != null && isLoading === false" class="flex flex-col w-full bg-[#1A1C20] rounded-xl shadow-lg border border-white/5 p-6">
                        
                        <!-- Header / Title -->
                         <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white flex items-center gap-3">
                                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                                    <i class="fa-light fa-money-bill-transfer text-xl"></i>
                                </div>
                                {{ $t('Withdraw') }}
                            </h2>
                            
                            <!-- Currency Selector (ReadOnly usually but kept for logic) -->
                             <button @click.prevent="$router.push('/profile/wallet')" type="button" class="flex items-center gap-2 bg-[#0c0d10] px-3 py-1.5 rounded-lg border border-white/10 text-gray-300">
                                <img :src="`/assets/images/coin/`+wallet.currency+`.png`" alt="" class="w-5 h-5">
                                <span class="font-bold text-sm">{{ wallet.currency }}</span>
                            </button>
                        </div>

                        <!-- Bank Transfer Form (USD) -->
                        <form v-if="wallet.currency === 'USD'" action="" @submit.prevent="submitWithdrawBank" class="space-y-4">
                            
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Nome do titular</label>
                                <input v-model="withdraw_deposit.name" type="text" class="w-full bg-[#0c0d10] border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600" placeholder="Nome completo" required>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Banking information') }}</label>
                                <textarea v-model="withdraw_deposit.bank_info" cols="30" rows="6" class="w-full bg-[#0c0d10] border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600 resize-none" :placeholder="$t('Enter bank information')"></textarea>
                            </div>

                            <div class="space-y-2 pt-2">
                                <div class="flex justify-between items-end">
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Amount') }}</label>
                                    <span class="text-[10px] text-gray-500">Min: {{ setting.min_withdrawal }} ~ Max: {{ setting.max_withdrawal }}</span>
                                </div>
                                
                                <div class="relative">
                                     <input type="number" 
                                           class="w-full bg-[#0c0d10] border border-white/10 rounded-lg pl-4 pr-32 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-bold text-lg placeholder-gray-600"
                                           v-model="withdraw_deposit.amount"
                                           :min="setting.min_withdrawal"
                                           :max="setting.max_withdrawal"
                                           step="0.01"
                                           required>
                                    
                                     <div class="absolute inset-y-0 right-2 flex items-center space-x-1">
                                        <button @click.prevent="setPercentAmount(50)" type="button" class="px-2 py-1 text-xs font-bold text-gray-400 bg-white/5 hover:bg-white/10 rounded transition-colors">50%</button>
                                        <button @click.prevent="setMaxAmount" type="button" class="px-2 py-1 text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 rounded transition-colors">MAX</button>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between text-xs text-gray-500 px-1">
                                    <p>{{ $t('Available') }}: <span class="text-white font-bold">{{ state.currencyFormat(parseFloat(wallet.balance_withdrawal), wallet.currency) }}</span></p>
                                </div>
                            </div>

                            <div class="pt-4">
                                <div class="flex items-center gap-3 p-3 bg-[#0c0d10] rounded-lg border border-white/5">
                                    <input id="accept_terms_checkbox" v-model="withdraw_deposit.accept_terms" type="checkbox" class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-primary focus:ring-offset-gray-900">
                                    <label for="accept_terms_checkbox" class="text-sm text-gray-300 font-medium cursor-pointer">
                                        {{ $t('I accept the transfer terms') }}
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black uppercase tracking-wider py-4 rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-[0.98]">
                                {{ $t('Request withdrawal') }}
                            </button>
                        </form>

                        <!-- PIX Transfer Form (BRL) -->
                        <form v-if="wallet.currency === 'BRL'" action="" @submit.prevent="submitWithdraw" class="space-y-4">
                            
                            <!-- Payment Method Readonly -->
                             <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Withdraw with') }}</label>
                                <div class="w-full flex items-center justify-between bg-[#0c0d10] border border-white/10 rounded-lg p-3 opacity-80">
                                    <div class="flex items-center gap-3">
                                        <img :src="`/assets/images/pix.png`" alt="" class="h-6 w-auto">
                                        <span class="text-white font-bold">PIX</span>
                                    </div>
                                    <i class="fa-solid fa-lock text-gray-600 text-xs"></i>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Nome do titular</label>
                                <input v-model="withdraw.name" type="text" class="w-full bg-[#0c0d10] border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600" placeholder="Nome completo" required>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="col-span-1 space-y-1">
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Tipo de Chave</label>
                                    <div class="relative">
                                         <select v-model="withdraw.pix_type" class="w-full bg-[#0c0d10] border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none cursor-pointer" required>
                                            <option value="" disabled selected>Selecione</option>
                                            <option value="document">CPF/CNPJ</option>
                                            <option value="email">E-mail</option>
                                            <option value="phoneNumber">Telefone</option>
                                            <option value="randomKey">Chave Aleatória</option>
                                        </select>
                                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-500 pointer-events-none"></i>
                                    </div>
                                </div>
                                <div class="col-span-2 space-y-1">
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Chave Pix</label>
                                    <input v-model="withdraw.pix_key" type="text" class="w-full bg-[#0c0d10] border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-gray-600" placeholder="Sua chave pix" required>
                                </div>
                            </div>

                            <div class="space-y-2 pt-2">
                                <div class="flex justify-between items-end">
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $t('Amount') }}</label>
                                    <span class="text-[10px] text-gray-500">Min: {{ setting.min_withdrawal }} ~ Max: {{ setting.max_withdrawal }}</span>
                                </div>
                                
                                <div class="relative">
                                     <input type="number" 
                                           class="w-full bg-[#0c0d10] border border-white/10 rounded-lg pl-4 pr-32 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-bold text-lg placeholder-gray-600"
                                           v-model="withdraw.amount"
                                           :min="setting.min_withdrawal"
                                           :max="setting.max_withdrawal"
                                           step="0.01"
                                           required>
                                    
                                     <div class="absolute inset-y-0 right-2 flex items-center space-x-1">
                                        <button @click.prevent="setPercentAmount(50)" type="button" class="px-2 py-1 text-xs font-bold text-gray-400 bg-white/5 hover:bg-white/10 rounded transition-colors">50%</button>
                                        <button @click.prevent="setMaxAmount" type="button" class="px-2 py-1 text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 rounded transition-colors">MAX</button>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between text-xs text-gray-500 px-1">
                                    <p>{{ $t('Available') }}: <span class="text-white font-bold">{{ state.currencyFormat(parseFloat(wallet.balance_withdrawal), wallet.currency) }}</span></p>
                                </div>
                            </div>

                            <div class="pt-4">
                                <div class="flex items-center gap-3 p-3 bg-[#0c0d10] rounded-lg border border-white/5">
                                    <input id="accept_terms_checkbox_brl" v-model="withdraw.accept_terms" type="checkbox" class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-primary focus:ring-offset-gray-900">
                                    <label for="accept_terms_checkbox_brl" class="text-sm text-gray-300 font-medium cursor-pointer">
                                        {{ $t('I accept the transfer terms') }}
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black uppercase tracking-wider py-4 rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-[0.98]">
                                {{ $t('Request withdrawal') }}
                            </button>
                        </form>
                    </div>
                    
                    <!-- Loading -->
                    <div v-if="isLoading" role="status" class="absolute -translate-x-1/2 -translate-y-1/2 top-2/4 left-1/2">
                        <svg aria-hidden="true" class="w-10 h-10 text-gray-800 animate-spin fill-primary" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                        </svg>
                        <span class="sr-only">{{ $t('Loading') }}...</span>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>


<script>

import {RouterLink, useRouter} from "vue-router";
import BaseLayout from "@/Layouts/BaseLayout.vue";
import WalletSideMenu from "@/Pages/Profile/Components/WalletSideMenu.vue";
import HttpApi from "@/Services/HttpApi.js";
import {useToast} from "vue-toastification";
import {useSettingStore} from "@/Stores/SettingStore.js";

export default {
    props: [],
    components: {WalletSideMenu, BaseLayout, RouterLink},
    data() {
        return {
            isLoading: false,
            setting: null,
            wallet: null,
            withdraw: {
                name: '',
                pix_key: '',
                pix_type: '',
                amount: '',
                type: 'pix',
                currency: '',
                symbol: '',
                accept_terms: false
            },
            withdraw_deposit: {
                name: '',
                bank_info: '',
                amount: '',
                type: 'bank',
                currency: '',
                symbol: '',
                accept_terms: false
            },
        }
    },
    setup(props) {
        const router = useRouter();
        return {
            router
        };
    },
    computed: {},
    mounted() {

    },
    methods: {
        setMinAmount: function() {
            this.withdraw.amount = this.setting.min_withdrawal;
        },
        setMaxAmount: function() {
            this.withdraw.amount = this.setting.max_withdrawal;
        },
        setPercentAmount: function(percent) {
            this.withdraw.amount = ( percent / 100 ) * this.wallet.balance_withdrawal;
        },
        getWallet: function() {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingWallet = true;

            HttpApi.get('profile/wallet')
                .then(response => {
                    _this.wallet = response.data.wallet;

                    _this.withdraw.currency = response.data.wallet.currency;
                    _this.withdraw.symbol = response.data.wallet.symbol;

                    _this.withdraw_deposit.currency = response.data.wallet.currency;
                    _this.withdraw_deposit.symbol = response.data.wallet.symbol;

                    _this.isLoadingWallet = false;
                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingWallet = false;
                });
        },
        getSetting: function() {
            const _this = this;
            const settingStore = useSettingStore();
            const settingData = settingStore.setting;

            if(settingData) {
                _this.setting                   = settingData;
                _this.withdraw.amount           = settingData.min_withdrawal;
                _this.withdraw_deposit.amount   = settingData.min_withdrawal;
            }

            _this.isLoading                 = false;
        },
        submitWithdrawBank: function(event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoading = true;

            HttpApi.post('wallet/withdraw/request', _this.withdraw_deposit).then(response => {
                _this.isLoading = false;
                _this.withdraw_deposit = {
                    name: '',
                    bank_info: '',
                    amount: '',
                    type: '',
                    accept_terms: false
                }

                _this.router.push({ name: 'profileTransactions' });
                _toast.success(response.data.message);
            }).catch(error => {
                Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                    _toast.error(`${value}`);
                });
                _this.isLoading = false;
            });
        },
        submitWithdraw: function(event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoading = true;

            HttpApi.post('wallet/withdraw/request', _this.withdraw).then(response => {
                _this.isLoading = false;
                _this.withdraw = {
                    name: '',
                    pix_key: '',
                    pix_type: '',
                    amount: '',
                    type: '',
                    accept_terms: false
                }

                _this.router.push({ name: 'profileTransactions' });
                _toast.success(response.data.message);
            }).catch(error => {
                Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                    _toast.error(`${value}`);
                });
                _this.isLoading = false;
            });
        }
    },
    created() {
        this.getWallet();
        this.getSetting();

    },
    watch: {},
};
</script>

<style scoped>

</style>
