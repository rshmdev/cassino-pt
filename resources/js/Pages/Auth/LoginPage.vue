<template>
    <AuthLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="text-center">
                <span>{{ $t('Loading') }}</span>
            </div>
        </LoadingComponent>

        <div v-if="!isLoading" class="min-h-[calc(100vh-64px)] flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-[#000000] min-h-[70dvh] rounded-3xl shadow-2xl overflow-hidden border border-white/10">
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
                    <button class="flex-1 py-3 rounded-xl font-bold text-sm bg-primary text-white shadow-lg shadow-primary/20 transition-all">
                        Entrar
                    </button>
                    <button @click.prevent="$router.push({ name: 'register' })" class="flex-1 py-3 rounded-xl font-bold text-sm text-gray-500 hover:text-white transition-all">
                        Criar uma conta grátis
                    </button>
                </div>

                <div class="p-6 pt-0">
                    <p class="text-gray-400 text-xs text-center mb-6 px-4">
                        Acesse sua conta para continuar aproveitando as melhores ofertas.
                    </p>

                    <form @submit.prevent="loginSubmit" class="space-y-4">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input required type="text" v-model="loginForm.email" 
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-3.5 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all font-medium" 
                                   placeholder="E-mail:">
                        </div>

                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-lock"></i>
                            </div>
                            <input required :type="typeInputPassword" v-model="loginForm.password"
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-3.5 pl-11 pr-12 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all font-medium"
                                   placeholder="Senha:">
                            <button type="button" @click.prevent="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-white transition-colors">
                                <i v-if="typeInputPassword === 'password'" class="fa-regular fa-eye"></i>
                                <i v-if="typeInputPassword === 'text'" class="fa-sharp fa-regular fa-eye-slash"></i>
                            </button>
                        </div>

                        <div class="flex justify-end">
                            <button @click.prevent="$router.push('/forgot-password')" type="button" class="text-xs text-gray-500 hover:text-white transition-colors">Esqueceu a senha?</button>
                        </div>

                        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98] uppercase text-sm tracking-widest mt-4">
                            Entrar
                        </button>

                        <div class="text-center mt-8 pb-4">
                            <p class="text-gray-500 text-xs mb-2">Ainda não tem uma conta?</p>
                            <a href="" @click.prevent="$router.push({ name: 'register' })" class="text-primary font-bold text-sm border-b border-primary/20 hover:border-primary transition-all pb-0.5">Criar uma conta grátis</a>
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
import {useRouter} from "vue-router";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";

export default {
    props: [],
    components: { LoadingComponent, AuthLayout },
    data() {
        return {
            isLoading: false,
            typeInputPassword: 'password',
            isReferral: false,
            loginForm: {
                email: '',
                password: '',
            },
        }
    },
    setup(props) {
        const router = useRouter();
        return {
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
    },
    methods: {
        redirectSocialTo: function() {
            return '/auth/redirect/google'
        },
        loginSubmit: async function(event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoading = true;
            const authStore = useAuthStore();

            await HttpApi.post('auth/login', _this.loginForm)
                .then(async response =>  {
                    await new Promise(r => {
                        setTimeout(() => {
                            authStore.setToken(response.data.access_token);
                            authStore.setUser(response.data.user);
                            authStore.setIsAuth(true);

                            _this.loginForm = {
                                email: '',
                                password: '',
                            }

                            _this.router.push({ name: 'home' });
                            _toast.success(_this.$t('You have been authenticated, welcome!'));

                            _this.isLoading = false;
                        }, 1000)
                    });

                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
        togglePassword: function() {
            if(this.typeInputPassword === 'password') {
                this.typeInputPassword = 'text';
            }else{
                this.typeInputPassword = 'password';
            }
        },
    },
};
</script>

<style scoped>
</style>
