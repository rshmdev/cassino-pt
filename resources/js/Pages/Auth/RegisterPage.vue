<template>
    <AuthLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="text-center">
                <span>{{ $t('Loading') }}</span>
            </div>
        </LoadingComponent>
        <div v-if="!isLoading" class="min-h-[calc(100vh-64px)] flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-[#000000] min-h-[90dvh] rounded-3xl shadow-2xl overflow-hidden border border-white/10 mt-16 mb-16">
                <!-- Top Banner -->
                <div class="w-full aspect-[21/9] relative overflow-hidden bg-gradient-to-br from-primary/20 to-black">
                     <img src="/assets/images/quests.png" class="absolute right-0 top-0 h-full opacity-30 object-contain pointer-events-none" alt="">
                     <div class="absolute inset-0 p-6 flex flex-col justify-center">
                         <h3 class="text-white font-black text-2xl uppercase italic tracking-tighter leading-tight">
                            Até 25% de<br><span class="text-primary">CASHBACK</span>
                         </h3>
                         <p class="text-white/60 text-[10px] mt-1 uppercase font-bold tracking-widest">Diário e Semanal!</p>
                     </div>
                </div>

                <!-- Tabs -->
                <div class="flex p-4 gap-2">
                    <button @click.prevent="$router.push({ name: 'login' })" class="flex-1 py-3 rounded-xl font-bold text-sm text-gray-500 hover:text-white transition-all">
                        Entrar
                    </button>
                    <button class="flex-1 py-3 rounded-xl font-bold text-sm bg-primary text-white shadow-lg shadow-primary/20 transition-all">
                        Criar uma conta grátis
                    </button>
                </div>

                <div class="p-8 pt-0">
                    <p class="text-gray-400 text-xs text-center mb-8 px-4">
                        Crie uma conta para aproveitar os melhores jogos e promoções.
                    </p>

                    <form @submit.prevent="registerSubmit" class="space-y-4">
                        <!-- Nome -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <input required type="text" v-model="registerForm.name" 
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-4 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all" 
                                   placeholder="Nome:">
                        </div>

                        <!-- E-mail -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input required type="email" v-model="registerForm.email" 
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-4 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all" 
                                   placeholder="E-mail:">
                        </div>

                        <!-- Senha -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-lock"></i>
                            </div>
                            <input required :type="typeInputPassword" v-model="registerForm.password"
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-4 pl-11 pr-12 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all"
                                   placeholder="Senha:">
                            <button type="button" @click.prevent="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-white transition-colors">
                                <i v-if="typeInputPassword === 'password'" class="fa-regular fa-eye"></i>
                                <i v-if="typeInputPassword === 'text'" class="fa-sharp fa-regular fa-eye-slash"></i>
                            </button>
                        </div>

                        <!-- Telefone -->
                        <div class="relative group text-white">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-phone"></i>
                            </div>
                            <div class="flex bg-[#09090b] border border-white/5 rounded-xl">
                                <div class="flex items-center pl-10 pr-2 border-r border-white/5 text-xs font-bold">
                                    🇧🇷 +55
                                </div>
                                <input required type="text" v-maska data-maska="(##) #####-####" v-model="registerForm.phone"
                                   class="w-full bg-transparent border-none py-4 px-4 text-sm focus:ring-0 placeholder-gray-600"
                                   placeholder="Digite seu telefone">
                            </div>
                        </div>

                        <!-- Invite Code -->
                        <div class="text-center py-2">
                             <button @click.prevent="isReferral = !isReferral" type="button" class="text-white text-xs font-bold hover:text-primary transition-colors">
                                 Código de referência <i class="fa-solid" :class="isReferral ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                             </button>
                             <div v-if="isReferral" class="mt-2 group relative">
                                 <input type="text" v-model="registerForm.reference_code" 
                                        class="w-full bg-[#09090b] border border-white/5 rounded-xl py-2 px-4 text-sm text-center text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-700"
                                        placeholder="CÓDIGO">
                             </div>
                        </div>

                        <!-- Agreements -->
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <input id="register-term-1" v-model="registerForm.term_a" required type="checkbox" class="size-4 mt-0.5 rounded border-white/10 bg-white/5 text-primary focus:ring-primary focus:ring-offset-[#000]">
                                <label for="register-term-1" class="ml-3 text-[10px] leading-relaxed text-gray-500">
                                    Eu confirmo que tenho pelo menos 18 anos e concordo com os <a href="#" class="text-primary font-bold hover:underline">termos e condições</a>.
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98] uppercase text-sm tracking-widest mt-4">
                            Criar conta
                        </button>

                        <div class="text-center mt-6 pb-4">
                            <p class="text-gray-500 text-xs mb-2">Já tem uma conta?</p>
                            <a href="" @click.prevent="$router.push({ name: 'login' })" class="text-primary font-bold text-sm border-b border-primary/20 hover:border-primary transition-all pb-0.5">Entrar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>


<script>

import {useToast} from "vue-toastification";
import {useAuthStore} from "@/Stores/Auth.js";
import HttpApi from "@/Services/HttpApi.js";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import {useRoute, useRouter} from "vue-router";
import {onMounted, reactive} from "vue";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";

export default {
    props: [],
    components: {LoadingComponent, AuthLayout },
    data() {
        return {
            isLoading: false,
            typeInputPassword: 'password',
            isReferral: false,
            registerForm: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                reference_code: '',
                term_a: false,
                agreement: false,
                phone: ''
            },
        }
    },
    setup() {
        const router = useRouter();
        const routeParams = reactive({
            code: null,
        });

        onMounted(() => {
            const params = new URLSearchParams(window.location.search);
            if (params.has('code')) {
                routeParams.code = params.get('code');
            }
        });

        return {
            routeParams,
            router
        };
    },
    computed: {
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
    },
    mounted() {
        if(this.isAuthenticated) {
            this.router.push({ name: 'home' });
        }

        if (this.router.currentRoute.value.params.code) {
            this.registerForm.reference_code = this.router.currentRoute.value.params.code;
            this.isReferral = true;
        }else if(this.routeParams.code) {
            this.registerForm.reference_code = this.routeParams.code;
            this.isReferral = true;
        }
    },
    methods: {
        togglePassword: function() {
            if(this.typeInputPassword === 'password') {
                this.typeInputPassword = 'text';
            }else{
                this.typeInputPassword = 'password';
            }
        },
        registerSubmit: async function(event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoading = true;

            const authStore = useAuthStore();
            _this.registerForm.password_confirmation = _this.registerForm.password;
            
            await HttpApi.post('auth/register', _this.registerForm)
                .then(response => {
                    if(response.data.access_token !== undefined) {
                        authStore.setToken(response.data.access_token);
                        authStore.setUser(response.data.user);
                        authStore.setIsAuth(true);

                        _this.registerForm = {
                            name: '',
                            email: '',
                            password: '',
                            password_confirmation: '',
                            reference_code: '',
                            term_a: false,
                            agreement: false,
                            phone: ''
                        };

                        _this.router.push({ name: 'profileDeposit' });
                        _toast.success(_this.$t('Your account has been created successfully'));
                    }

                    _this.isLoading = false;
                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
    },
};
</script>

<style scoped>
</style>
