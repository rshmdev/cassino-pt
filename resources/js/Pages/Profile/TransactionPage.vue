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
                    <div v-if="isLoading === false && wallet" class="flex flex-col w-full bg-[#1A1C20] rounded-xl shadow-lg border border-white/5 p-6 space-y-8">
                        
                        <!-- Withdrawals Section -->
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                                    <i class="fa-light fa-money-bill-transfer text-xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold text-white">{{ $t('Withdrawal List') }}</h1>
                                    <p class="text-gray-500 text-xs">{{ $t('Below is the list of all requested withdrawals') }}</p>
                                </div>
                            </div>

                            <div v-if="withdraws && wallet" class="rounded-xl overflow-hidden border border-white/5">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs text-gray-500 uppercase bg-[#0c0d10] border-b border-white/5">
                                            <tr>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Proof') }}</th>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Type') }}</th>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Value') }}</th>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Status') }}</th>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <tr v-for="(withdraw, index) in withdraws.data" :key="index" class="bg-[#1A1C20] hover:bg-white/5 transition-colors">
                                                <td class="px-6 py-4">
                                                    <a v-if="withdraw.proof" href="" class="flex items-center text-primary hover:text-primary/80 transition-colors">
                                                        <i class="fa-regular fa-file-export mr-2"></i>
                                                        {{ $t('Click here') }}
                                                    </a>
                                                    <span v-else class="text-gray-600 italic">{{ $t('Processing') }}</span>
                                                </td>
                                                <th scope="row" class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                                    <span class="uppercase bg-white/5 px-2 py-1 rounded text-xs border border-white/10">{{ withdraw.type }}</span>
                                                </th>
                                                <td class="px-6 py-4 font-bold text-gray-300">
                                                    {{ state.currencyFormat(parseFloat(withdraw.amount), withdraw.currency) }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span v-if="withdraw.status === 1" class="bg-green-500/10 text-green-500 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border border-green-500/20">{{ $t('Concluded') }}</span>
                                                    <span v-if="withdraw.status === 0" class="bg-yellow-500/10 text-yellow-500 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border border-yellow-500/20">{{ $t('Pending') }}</span>
                                                </td>
                                                <td class="px-6 py-4 text-gray-500 text-xs">
                                                    {{ withdraw.dateHumanReadable }}
                                                </td>
                                            </tr>
                                            <tr v-if="withdraws.data.length === 0">
                                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                                    {{ $t('No withdrawals found') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Deposits Section -->
                         <div>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="size-10 rounded-full bg-green-500/10 flex items-center justify-center text-green-500 border border-green-500/20">
                                    <i class="fa-light fa-money-bill-transfer text-xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold text-white">{{ $t('Deposits List') }}</h1>
                                    <p class="text-gray-500 text-xs">{{ $t('List of deposits made') }}</p>
                                </div>
                            </div>

                            <div v-if="deposits && wallet" class="rounded-xl overflow-hidden border border-white/5">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs text-gray-500 uppercase bg-[#0c0d10] border-b border-white/5">
                                            <tr>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Type') }}</th>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Value') }}</th>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Status') }}</th>
                                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">{{ $t('Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <tr v-for="(deposit, index) in deposits.data" :key="index" class="bg-[#1A1C20] hover:bg-white/5 transition-colors">
                                                <th scope="row" class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                                    <span class="uppercase bg-white/5 px-2 py-1 rounded text-xs border border-white/10">{{ deposit.type }}</span>
                                                </th>
                                                <td class="px-6 py-4 font-bold text-gray-300">
                                                    {{ state.currencyFormat(parseFloat(deposit.amount), deposit.currency) }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span v-if="deposit.status === 1" class="bg-green-500/10 text-green-500 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border border-green-500/20">{{ $t('Concluded') }}</span>
                                                    <span v-if="deposit.status === 0" class="bg-yellow-500/10 text-yellow-500 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border border-yellow-500/20">{{ $t('Pending') }}</span>
                                                </td>
                                                <td class="px-6 py-4 text-gray-500 text-xs">
                                                    {{ deposit.dateHumanReadable }}
                                                </td>
                                            </tr>
                                             <tr v-if="deposits.data.length === 0">
                                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                                    {{ $t('No deposits found') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

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

import {RouterLink} from "vue-router";
import BaseLayout from "@/Layouts/BaseLayout.vue";
import WalletSideMenu from "@/Pages/Profile/Components/WalletSideMenu.vue";
import {useToast} from "vue-toastification";
import HttpApi from "@/Services/HttpApi.js";
import CustomPagination from "@/Components/UI/CustomPagination.vue";
import {useAuthStore} from "@/Stores/Auth.js";

export default {
    props: [],
    components: {CustomPagination, WalletSideMenu, BaseLayout, RouterLink},
    data() {
        return {
            isLoading: false,
            wallet: {},
            withdraws: null,
            deposits: null,
        }
    },
    setup(props) {


        return {};
    },
    computed: {
        userData() {
            const authStore = useAuthStore();
            return authStore.user;
        },
    },
    mounted() {

    },
    methods: {
        getWallet: function() {
            const _this = this;
            const _toast = useToast();

            HttpApi.get('profile/wallet')
                .then(response => {
                    _this.wallet = response.data.wallet;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                });
        },
        getWithdraws: function() {
            const _this = this;
            _this.isLoading = true;

            HttpApi.get('wallet/withdraw')
                .then(response => {
                    _this.withdraws = response.data.withdraws;
                    _this.isLoading = false;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        console.log(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
        getDeposits: function() {
            const _this = this;
            _this.isLoading = true;

            HttpApi.get('wallet/deposit')
                .then(response => {
                    _this.deposits = response.data.deposits;
                    _this.isLoading = false;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        console.log(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
    },
    created() {
        this.getWallet();
        this.getWithdraws();
        this.getDeposits();
    },
    watch: {},
};
</script>

<style scoped>

</style>
