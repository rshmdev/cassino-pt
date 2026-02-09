<template>
    <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-bold text-white mb-2">{{ $t('Wallet') }}</h1>
        <div class="bg-[#1A1C20] w-full rounded-xl shadow-lg border border-white/5 overflow-hidden">
            <ul class="flex flex-col">
                <li>
                    <RouterLink :to="{ name: 'profileWallet' }"
                                active-class="!bg-[#0c0d10] !text-primary border-l-4 border-primary"
                                class="flex items-center w-full px-5 py-4 text-gray-400 hover:bg-white/5 hover:text-white transition-all duration-300 border-l-4 border-transparent">
                        <span class="w-8 text-xl"><i class="fa-light fa-wallet"></i></span>
                        <span class="font-medium">{{ $t('Balance') }}</span>
                    </RouterLink>
                </li>

                <li>
                    <RouterLink :to="{ name: 'profileDeposit' }"
                                active-class="!bg-[#0c0d10] !text-primary border-l-4 border-primary"
                                class="flex items-center w-full px-5 py-4 text-gray-400 hover:bg-white/5 hover:text-white transition-all duration-300 border-l-4 border-transparent">
                        <span class="w-8 text-xl"><i class="fa-sharp fa-light fa-piggy-bank"></i></span>
                        <span class="font-medium">{{ $t('Deposit') }}</span>
                    </RouterLink>
                </li>

                <li>
                    <RouterLink :to="{ name: 'profileWithdraw' }"
                                active-class="!bg-[#0c0d10] !text-primary border-l-4 border-primary"
                                class="flex items-center w-full px-5 py-4 text-gray-400 hover:bg-white/5 hover:text-white transition-all duration-300 border-l-4 border-transparent">
                        <span class="w-8 text-xl"><i class="fa-light fa-money-simple-from-bracket"></i></span>
                        <span class="font-medium">{{ $t('Withdraw') }}</span>
                    </RouterLink>
                </li>

                <li>
                    <RouterLink :to="{ name: 'profileTransactions' }"
                                active-class="!bg-[#0c0d10] !text-primary border-l-4 border-primary"
                                class="flex items-center w-full px-5 py-4 text-gray-400 hover:bg-white/5 hover:text-white transition-all duration-300 border-l-4 border-transparent">
                        <span class="w-8 text-xl"><i class="fa-solid fa-chart-mixed"></i></span>
                        <span class="font-medium">{{ $t('Transactions') }}</span>
                    </RouterLink>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>

export default {
    props: [],
    components: {},
    data() {
        return {
            isLoading: false,
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

    },
    watch: {

    },
};
</script>

<style scoped>

</style>
