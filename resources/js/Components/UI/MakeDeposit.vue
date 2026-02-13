<template>
    <button @click.prevent="toggleModalDeposit" type="button" :class="[showMobile === false ? 'hidden md:block' : '', isFull ? 'w-full' : '']" class="ui-button-blue mr-3 rounded">
        {{ title }}
    </button>

    <div id="modalElDeposit" tabindex="-1" aria-hidden="true" class="fixed  top-0 left-0 right-0 z-50 hidden w-full overflow-x-hidden overflow-y-auto md:inset-0 min-h-full">
        <div class="relative w-full max-w-2xl min-h-full flex items-center justify-center dark:bg-[#17181b] p-4">
            <div class="flex flex-col md:justify-between px-6 pb-8 my-auto">
                <div class="flex justify-between modal-header mb-6 mt-6">
                    <div>
                        <h1 class="font-bold text-xl">{{ $t('Deposit') }}</h1>
                        <p><small>{{ $t('Choose your payment method')}}</small></p>
                    </div>
                </div>

                <DepositWidget />
            </div>
        </div>
    </div>
</template>

<script>
    import {useAuthStore} from "@/Stores/Auth.js";
    import DepositWidget from "@/Components/Widgets/DepositWidget.vue";
    import {onMounted} from "vue";
    import {initFlowbite} from "flowbite";

    export default {
        props: ['showMobile', 'title', 'isFull'],
        components: { DepositWidget },
        data() {
            return {
                isLoading: false,
                modalDeposit: null,
            }
        },
        setup(props) {
            onMounted(() => {
                initFlowbite();
            });

            return {};
        },
        computed: {
            isAuthenticated() {
                const authStore = useAuthStore();
                return authStore.isAuth;
            },
        },
        mounted() {
            this.modalDeposit = new Modal(document.getElementById('modalElDeposit'), {
                placement: 'center',
                backdrop: 'dynamic',
                backdropClasses: 'bg-black/80 fixed inset-0 z-40',
                closable: true,
                onHide: () => {
                    this.paymentType = null;
                },
                onShow: () => {

                },
                onToggle: () => {

                },
            });
        },
        beforeUnmount() {

        },
        methods: {
            toggleModalDeposit: function() {
                this.modalDeposit.toggle();
            },
        },
        created() {

        },
        watch: {

        },
    };
</script>

<style scoped>

</style>
