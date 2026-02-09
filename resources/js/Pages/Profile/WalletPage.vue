<template>
    <BaseLayout>
        <div v-if="setting != null" class="md:w-4/6 2xl:w-4/6 mx-auto mt-20 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Sidebar -->
                <div class="col-span-1 hidden md:block">
                    <WalletSideMenu />
                </div>
                
                <!-- Main Content -->
                <div class="relative col-span-2">
                    <div v-if="!isLoadingWallet" class="flex flex-col w-full bg-[#1A1C20] p-6 rounded-xl shadow-lg border border-white/5">
                        
                        <!-- Top Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                            <!-- Total Balance -->
                            <div class="flex items-center w-full bg-[#0c0d10] p-4 rounded-xl border border-white/5">
                                <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20 shadow-[0_0_10px_rgba(var(--primary),0.2)]">
                                    <i class="fa-regular fa-wallet text-xl"></i>
                                </div>
                                <div class="flex flex-col ml-4">
                                    <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Saldo Total</span>
                                    <h1 class="text-xl font-bold text-white">{{ state.currencyFormat(parseFloat(wallet.balance), wallet.currency) }}</h1>
                                </div>
                            </div>

                            <!-- Bonus Balance -->
                            <div class="flex items-center w-full bg-[#0c0d10] p-4 rounded-xl border border-white/5">
                                <div class="size-12 rounded-full bg-yellow-500/10 flex items-center justify-center text-yellow-500 border border-yellow-500/20">
                                    <i class="fa-regular fa-gift text-xl"></i>
                                </div>
                                <div class="flex flex-col ml-4">
                                    <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Saldo de Bônus</span>
                                    <h1 class="text-xl font-bold text-white">{{ state.currencyFormat(parseFloat(wallet.balance_bonus), wallet.currency) }}</h1>
                                </div>
                            </div>

                            <!-- Withdrawal Balance -->
                            <div class="flex items-center w-full bg-[#0c0d10] p-4 rounded-xl border border-white/5 md:col-span-2">
                                <div class="size-12 rounded-full bg-green-500/10 flex items-center justify-center text-green-500 border border-green-500/20">
                                    <i class="fa-regular fa-sack-dollar text-xl"></i>
                                </div>
                                <div class="flex flex-col ml-4">
                                    <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">{{ $t('Withdrawal Balance') }}</span>
                                    <h1 class="text-xl font-bold text-white">{{ state.currencyFormat(parseFloat(wallet.balance_withdrawal), wallet.currency) }}</h1>
                                </div>
                            </div>

                            <div class="border-t border-white/5 col-span-1 md:col-span-2 my-2"></div>
                            
                            <!-- Rollovers -->
                            <div v-if="setting.disable_rollover === false || setting.rollover_deposit > 0" class="flex flex-col space-y-3 w-full col-span-1 md:col-span-2 bg-[#0c0d10] p-4 rounded-xl border border-white/5">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-500">
                                            <i class="fa-regular fa-chart-pie text-sm"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-400 font-bold uppercase">Rollover de Deposito</span>
                                            <span class="text-sm font-bold text-white">{{ state.currencyFormat(parseFloat(wallet.balance_deposit_rollover), wallet.currency) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-800 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" :style="{ width: rolloverPercentage(parseFloat(wallet.balance_deposit_rollover)) + '%' }"></div>
                                </div>
                                <div class="text-right text-xs text-gray-500">{{ rolloverPercentage(parseFloat(wallet.balance_deposit_rollover)) }}%</div>
                            </div>

                            <div v-if="setting.disable_rollover === false || setting.rollover > 0" class="flex flex-col space-y-3 w-full col-span-1 md:col-span-2 bg-[#0c0d10] p-4 rounded-xl border border-white/5">
                                <div class="flex justify-between items-center">
                                     <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-500">
                                            <i class="fa-regular fa-chart-line text-sm"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-400 font-bold uppercase">Rollover de Bônus</span>
                                            <span class="text-sm font-bold text-white">{{ state.currencyFormat(parseFloat(wallet.balance_bonus_rollover), wallet.currency) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="w-full bg-gray-800 rounded-full h-2">
                                        <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" :style="{ width: rolloverPercentage(parseFloat(wallet.balance_bonus_rollover)) + '%' }"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>Bônus</span>
                                        <span>{{ rolloverPercentage(parseFloat(wallet.balance_bonus_rollover)) }}%</span>
                                    </div>
                                    
                                    <div class="w-full bg-gray-800 rounded-full h-2">
                                        <div class="bg-pink-600 h-2 rounded-full transition-all duration-500" :style="{ width: rolloverPercentage(parseFloat(setting.rollover_protection)) + '%' }"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>Proteção</span>
                                        <span>{{ rolloverPercentage(parseFloat(setting.rollover_protection)) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                            <a @click.prevent="$router.push('/profile/deposit')" href="" class="flex items-center p-4 rounded-xl bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20 hover:border-primary/50 hover:from-primary/20 hover:to-primary/10 transition-all group">
                                <div class="size-12 rounded-full bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/30 group-hover:scale-110 transition-transform">
                                    <i class="fa-regular fa-arrow-down text-xl"></i>
                                </div>
                                <div class="flex flex-col ml-4">
                                    <h1 class="text-lg font-bold text-white">{{ $t('Deposit') }}</h1>
                                    <p class="text-xs text-gray-400 group-hover:text-gray-300">{{ $t('Click here to deposit') }}</p>
                                </div>
                            </a>
                            <a @click.prevent="$router.push('/profile/withdraw')" href="" class="flex items-center p-4 rounded-xl bg-[#0c0d10] border border-white/10 hover:border-white/20 hover:bg-white/5 transition-all group">
                                <div class="size-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-300 group-hover:bg-gray-700 transition-colors">
                                    <i class="fa-regular fa-arrow-up text-xl"></i>
                                </div>
                                <div class="flex flex-col ml-4">
                                    <h1 class="text-lg font-bold text-white">{{ $t('Withdraw') }}</h1>
                                    <p class="text-xs text-gray-400 group-hover:text-gray-300">{{ $t('Click here to withdraw') }}</p>
                                </div>
                            </a>
                        </div>

                        <!-- My Wallets -->
                        <div class="mt-8 flex flex-col">
                            <h1 class="mb-4 text-xl font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-cards-blank opacity-50"></i>
                                {{ $t('My Wallets') }}
                            </h1>
                            <div class="flex flex-col w-full bg-[#0c0d10] rounded-xl border border-white/5 overflow-hidden">
                                <button v-for="(wallet, index) in mywallets" :key="index" @click.prevent="setWallet(wallet.id)" type="button" class="relative flex justify-between items-center w-full px-5 py-4 border-b border-white/5 hover:bg-white/5 transition-colors group">
                                   <div class="flex items-center gap-4">
                                       <div class="size-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 group-hover:text-white transition-colors">
                                            <i class="fa-light fa-wallet text-xl"></i>
                                       </div>
                                       <div class="flex flex-col items-start">
                                           <p class="text-white font-bold">{{ wallet.symbol }} {{ wallet.total_balance }}</p>
                                           <p class="text-xs text-gray-500 group-hover:text-gray-400">{{ wallet.symbol }} {{ wallet.balance_bonus }} (Bonus)</p>
                                       </div>
                                   </div>

                                    <span v-if="wallet.active === 1" class="bg-green-500/20 text-green-500 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border border-green-500/20">Ativo</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading State -->
                    <div v-if="isLoadingWallet" role="status" class="absolute -translate-x-1/2 -translate-y-1/2 top-2/4 left-1/2">
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

import { RouterLink } from "vue-router";
import BaseLayout from "@/Layouts/BaseLayout.vue";
import WalletSideMenu from "@/Pages/Profile/Components/WalletSideMenu.vue";
import {useToast} from "vue-toastification";
import {useAuthStore} from "@/Stores/Auth.js";
import HttpApi from "@/Services/HttpApi.js";
import {useSettingStore} from "@/Stores/SettingStore.js";

export default {
    props: [],
    components: {WalletSideMenu, BaseLayout, RouterLink },
    data() {
        return {
            isLoading: false,
            isLoadingWallet: true,
            wallet: null,
            mywallets: null,
            setting: null,
        }
    },
    setup(props) {


        return {};
    },
    computed: {

    },
    mounted() {

    },
    methods: {
        setWallet: function(id) {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingWallet = true;

            HttpApi.post('profile/mywallet/'+ id, {})
                .then(response => {
                   _this.getMyWallet();
                    _this.isLoadingWallet = false;
                    window.location.reload();

                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingWallet = false;
                });
        },
        getWallet: function() {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingWallet = true;

            HttpApi.get('profile/wallet')
                .then(response => {
                    _this.wallet = response.data.wallet;
                    _this.isLoadingWallet = false;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingWallet = false;
                });
        },
        getMyWallet: function() {
            const _this = this;
            const _toast = useToast();

            HttpApi.get('profile/mywallet')
                .then(response => {
                    _this.mywallets = response.data.wallets;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                });
        },
        getSetting: function() {
            const _this = this;
            const settingStore = useSettingStore();
            const settingData = settingStore.setting;

            if(settingData) {
                _this.setting = settingData;
            }

            _this.isLoading = false;
        },
        rolloverPercentage(balance) {
            return Math.max(0, ((balance / 100) * 100).toFixed(2));
        },
    },
    created() {
        this.getWallet();
        this.getMyWallet();
        this.getSetting();
    },
    watch: {

    },
};
</script>

<style scoped>

</style>
