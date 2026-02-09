<template>
    <div class="flex sm:hidden">
        <div class="fixed z-40 w-full h-16 bottom-0 left-0 bg-black/60 backdrop-blur-xl border-t border-white/10">
            <div class="grid h-full grid-cols-5 mx-auto">
                <button @click="$router.push('/')" type="button" class="inline-flex flex-col items-center justify-center text-gray-400 hover:text-white transition-colors">
                    <i class="fa-duotone fa-house-chimney mb-1 text-xl"></i>
                    <span class="text-[10px] uppercase font-bold tracking-tighter">{{ $t('Home') }}</span>
                </button>
                <button @click="$router.push('/casinos')" type="button" class="inline-flex flex-col items-center justify-center text-gray-400 hover:text-white transition-colors">
                    <i class="fa-duotone fa-dice mb-1 text-xl"></i>
                    <span class="text-[10px] uppercase font-bold tracking-tighter">{{ $t('Casino') }}</span>
                </button>
                <div class="flex items-center justify-center">
                    <button @click="$router.push('/profile/deposit')" type="button" class="inline-flex items-center justify-center w-12 h-12 bg-primary text-white rounded-2xl shadow-lg shadow-primary/40 -translate-y-4 border-4 border-black transition-transform hover:scale-110">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </button>
                </div>
                <div id="tooltip-new" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                    {{ $t('New Deposit') }}
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
                <button @click="$router.push('/profile/affiliate')" type="button" class="inline-flex flex-col items-center justify-center text-gray-400 hover:text-white transition-colors">
                    <i class="fa-solid fa-users mb-1 text-xl"></i>
                    <span class="text-[10px] uppercase font-bold tracking-tighter">Convidar</span>
                </button>
                <button @click="$router.push('/profile/wallet')" type="button" class="inline-flex flex-col items-center justify-center text-gray-400 hover:text-white transition-colors">
                    <i class="fa-duotone fa-wallet mb-1 text-xl"></i>
                    <span class="text-[10px] uppercase font-bold tracking-tighter">{{ $t('Wallet') }}</span>
                </button>
            </div>
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
