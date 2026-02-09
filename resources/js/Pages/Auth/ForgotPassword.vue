<template>
    <AuthLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="text-center">
                <span>{{ $t('Loading') }}</span>
            </div>
        </LoadingComponent>
        <div v-if="!isLoading" class="min-h-[calc(100vh-64px)] flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-[#000000] min-h-[53dvh] rounded-3xl shadow-2xl overflow-hidden border border-white/10 p-8 flex flex-col items-center">
                
                <!-- Logo -->
                <div v-if="setting" class="mb-8 mt-4">
                    <img :src="`/storage/`+setting.software_logo_white" alt="" class="h-12 object-contain" />
                </div>

                <h2 class="text-white font-black text-2xl mb-8 uppercase tracking-tight italic">Recuperar senha</h2>

                <p class="text-gray-400 text-sm leading-relaxed text-center mb-8 px-4">
                    Digite o endereço de e-mail verificado da sua conta e lhe enviaremos um link de redefinição de senha.
                </p>

                <form @submit.prevent="forgotPasswordSubmit" class="w-full space-y-6">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input required type="email" v-model="forgotForm.email" 
                               class="w-full bg-[#09090b] border border-white/5 rounded-xl py-4 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all font-medium" 
                               placeholder="E-mail:">
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98] uppercase text-sm tracking-widest mt-2">
                        <span v-if="!isLoadingForgot">Enviar e-mail de recuperação</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Enviando...
                        </span>
                    </button>

                    <div class="text-center pt-4">
                        <button @click.prevent="$router.push({ name: 'login' })" class="text-primary font-bold text-sm border-b border-primary/20 hover:border-primary transition-all pb-0.5">Fazer login</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthLayout>
</template>

<script>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import HttpApi from "@/Services/HttpApi.js";
import {useToast} from "vue-toastification";
import {useSettingStore} from "@/Stores/SettingStore.js";

export default {
    components: { LoadingComponent, AuthLayout },
    data() {
        return {
            isLoading: false,
            isLoadingForgot: false,
            forgotForm: {
                email: '',
            },
        }
    },
    computed: {
        setting() {
            const settingStore = useSettingStore();
            return settingStore.setting;
        }
    },
    methods: {
        forgotPasswordSubmit: async function() {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingForgot = true;

            await HttpApi.post('auth/forget-password', _this.forgotForm)
                .then(async response =>  {
                    _this.isLoadingForgot = false;
                    _toast.success(_this.$t('A token has been sent to you in your email box!'));
                    _this.forgotForm.email = '';
                    setTimeout(() => {
                        _this.$router.push({ name: 'login' });
                    }, 2000);
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingForgot = false;
                });
        }
    }
}
</script>

<style scoped>
</style>
