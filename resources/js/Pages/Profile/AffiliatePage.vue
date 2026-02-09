<template>
    <BaseLayout>
        <div class="container mx-auto p-4 flex flex-col items-center relative min-h-[calc(100vh-64px)]">
            
            <!-- Loading State -->
            <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center z-50 bg-[#0c0d10]/50 backdrop-blur-sm rounded-2xl">
                <LoadingComponent :isLoading="isLoading" />
            </div>

            <div v-if="wallet && !isLoading" class="w-full max-w-7xl grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left Column (Invite) -->
                <div v-if="isShowForm" class="lg:col-span-4 flex flex-col gap-6">
                    <div class="bg-[#141519] border border-white/5 min-h-[45dvh] rounded-3xl p-6 shadow-2xl relative overflow-hidden group">
                        
                        <!-- Header -->
                        <div class="relative z-10 mb-8">
                            <h1 class="text-white text-2xl font-black uppercase italic tracking-tighter flex items-center gap-3">
                                <i class="fa-duotone fa-gift text-primary text-3xl"></i>
                                {{  $t('INVITE A FRIEND') }}
                            </h1>
                            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-2 ml-1">
                                Compartilhe e Ganhe
                            </p>
                        </div>
                        
                        <!-- Inputs -->
                        <div class="relative z-10 flex flex-col gap-6">
                            
                            <!-- Ref Code -->
                            <div class="space-y-2">
                                <label for="referenceCode" class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">{{ $t('Reference Code') }}</label>
                                <div class="relative group/input">
                                    <input type="text"
                                           id="referenceCode"
                                           class="w-full bg-[#0c0d10] border border-white/5 rounded-xl py-4 pl-4 pr-14 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 font-mono tracking-widest font-bold transition-all shadow-inner"
                                           :placeholder="$t('Reference Code')"
                                           v-model="referencecode"
                                           required>
                                    <button @click.prevent="copyCode" type="button"
                                            class="absolute top-1/2 -translate-y-1/2 right-2 p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-all">
                                        <i class="fa-duotone fa-copy text-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Ref Link -->
                            <div class="space-y-2">
                                <label for="referenceLink" class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">{{ $t('Reference Link') }}</label>
                                <div class="relative group/input">
                                    <input type="text"
                                           id="referenceLink"
                                           class="w-full bg-[#0c0d10] border border-white/5 rounded-xl py-4 pl-4 pr-14 text-xs text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 font-mono transition-all shadow-inner truncate"
                                           :placeholder="$t('Reference Link')"
                                           v-model="referencelink"
                                           required>
                                    <button @click.prevent="copyLink" type="button"
                                            class="absolute top-1/2 -translate-y-1/2 right-2 p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-all">
                                        <i class="fa-duotone fa-copy text-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                         <!-- CTA -->
                        <div class="mt-8 relative z-10">
                            <a href="/affiliate/login" target="_blank" class="flex items-center justify-center w-full py-4 rounded-xl bg-primary hover:bg-primary/90 text-white font-black uppercase text-sm tracking-widest shadow-lg shadow-primary/20 transition-all transform active:scale-[0.98]">
                                <i class="fa-duotone fa-chart-line mr-2"></i>
                                Painel Avançado
                            </a>
                        </div>
                        
                        <!-- Decor -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                    </div>
                </div>

                 <!-- Init State -->
                 <div v-else class="lg:col-span-12 flex flex-col items-center justify-center p-12 bg-[#141519] border border-white/5 rounded-3xl shadow-2xl text-center min-h-[400px]">
                    <div v-if="!isLoadingGenerate" class="max-w-md w-full flex flex-col items-center">
                        <div class="size-24 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6">
                            <i class="fa-duotone fa-handshake text-5xl"></i>
                        </div>
                        <h2 class="text-white text-3xl font-black uppercase italic tracking-tighter mb-4">{{ $t('Become an Affiliate') }}</h2>
                        <p class="text-gray-400 text-sm leading-relaxed mb-8">
                            {{ $t('Generate the code Description') }}
                        </p>
                        <button @click.prevent="generateCode" type="button" class="w-full py-4 rounded-xl bg-primary hover:bg-primary/90 text-white font-black uppercase text-sm tracking-widest shadow-lg shadow-primary/20 transition-all transform active:scale-[0.98]">
                             {{ $t('Generate the code') }}
                        </button>
                    </div>

                    <div v-if="isLoadingGenerate" class="flex flex-col items-center">
                         <div class="size-16 mb-4">
                            <LoadingComponent :isLoading="true" />
                         </div>
                         <span class="text-gray-400 text-sm font-bold uppercase tracking-wider animate-pulse">{{ $t('Generating Code...') }}</span>
                    </div>
                </div>

                <!-- Right Column (Stats) -->
                <div v-if="isShowForm" class="lg:col-span-8 flex flex-col gap-6">
                    
                    <!-- Top Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <!-- Total Reward -->
                        <div class="bg-[#141519] border border-white/5 rounded-3xl p-6 relative overflow-hidden group hover:border-yellow-500/20 transition-all">
                             <div class="relative z-10 flex flex-col h-full justify-between">
                                 <div class="flex items-start justify-between mb-4">
                                     <div class="flex flex-col">
                                         <span class="text-gray-500 text-[10px] font-bold uppercase tracking-wider mb-1">{{ $t('TOTAL REWARD RECEIVED') }}</span>
                                         <h2 class="text-white text-3xl font-black font-mono tracking-tighter">{{ state.currencyFormat(parseFloat(wallet.refer_rewards), wallet.currency) }}</h2>
                                     </div>
                                     <div class="size-12 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 border border-yellow-500/10">
                                         <i class="fa-duotone fa-trophy text-2xl"></i>
                                     </div>
                                 </div>
                                 <button @click.prevent="opemModalWithdrawal" class="w-full py-3 rounded-xl bg-white/5 hover:bg-white/10 text-white font-bold text-xs uppercase tracking-widest border border-white/5 transition-all text-center">
                                     {{ $t('Withdraw') }}
                                 </button>
                             </div>
                             <div class="absolute -right-4 -top-4 size-32 bg-yellow-500/5 rounded-full blur-2xl group-hover:bg-yellow-500/10 transition-colors"></div>
                        </div>

                         <!-- Total Friends -->
                        <div class="bg-[#141519] border border-white/5 rounded-3xl p-6 relative overflow-hidden group hover:border-emerald-500/20 transition-all">
                             <div class="relative z-10 flex flex-col h-full justify-between">
                                 <div class="flex items-start justify-between mb-4">
                                     <div class="flex flex-col">
                                         <span class="text-gray-500 text-[10px] font-bold uppercase tracking-wider mb-1">{{ $t('TOTAL REFERRED FRIENDS') }}</span>
                                         <h2 class="text-white text-3xl font-black font-mono tracking-tighter">{{ indications }}</h2>
                                     </div>
                                     <div class="size-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 border border-emerald-500/10">
                                         <i class="fa-duotone fa-users text-2xl"></i>
                                     </div>
                                 </div>
                                 <div class="w-full py-3 rounded-xl bg-transparent text-emerald-500/50 font-bold text-xs uppercase tracking-widest text-center cursor-default">
                                    <i class="fa-duotone fa-check-circle mr-1"></i> Ativo
                                 </div>
                             </div>
                             <div class="absolute -right-4 -top-4 size-32 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Revshare -->
                        <div class="bg-[#141519] border border-white/5 rounded-3xl p-6 flex flex-col relative overflow-hidden group hover:border-purple-500/20 transition-all min-h-[160px]">
                            <div class="flex items-center justify-between mb-2 z-10">
                                <span class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">{{ $t('REFERRAL REVSHARE') }}</span>
                                <button @click.prevent="toggleCommissionRewards" class="size-8 flex items-center justify-center rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all">
                                    <i class="fa-regular fa-circle-info"></i>
                                </button>
                            </div>
                            <div class="flex-1 flex items-center justify-center z-10">
                                <h1 class="text-white font-black text-5xl tracking-tighter">
                                    {{ userData.affiliate_revenue_share_fake ? userData.affiliate_revenue_share_fake : userData.affiliate_revenue_share }}<span class="text-2xl text-purple-500">%</span>
                                </h1>
                            </div>
                             <div class="absolute -right-4 -top-4 size-32 bg-purple-500/5 rounded-full blur-2xl group-hover:bg-purple-500/10 transition-colors"></div>
                        </div>

                        <!-- CPA -->
                        <div class="bg-[#141519] border border-white/5 rounded-3xl p-6 flex flex-col relative overflow-hidden group hover:border-blue-500/20 transition-all min-h-[160px]">
                            <div class="flex items-center justify-between mb-2 z-10">
                                <span class="text-gray-500 text-[10px] font-bold uppercase tracking-wider">{{ $t('COMMISSION CPA') }}</span>
                                <button @click.prevent="toggleReferenceRewards" class="size-8 flex items-center justify-center rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all">
                                    <i class="fa-regular fa-circle-info"></i>
                                </button>
                            </div>
                            <div class="flex-1 flex items-center justify-center z-10">
                                <h1 class="text-white font-black text-4xl tracking-tighter">
                                    {{ state.currencyFormat(parseFloat(userData.affiliate_cpa), wallet.currency) }}
                                </h1>
                            </div>
                             <div class="absolute -right-4 -top-4 size-32 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors"></div>
                        </div>
                    </div>

                    <!-- Advanced Panel Banner -->
                    <div class="bg-gradient-to-r from-[#141519] to-[#0c0d10] border border-white/5 rounded-3xl p-8 relative overflow-hidden flex flex-col md:flex-row items-center gap-8">
                         <div class="relative z-10 shrink-0">
                            <div class="size-20 rounded-full bg-green-500/10 flex items-center justify-center text-green-500 border border-green-500/10 shadow-[0_0_30px_rgba(34,197,94,0.2)]">
                                <i class="fa-duotone fa-chart-network text-4xl"></i>
                            </div>
                         </div>
                         <div class="relative z-10 flex flex-col md:items-start items-center text-center md:text-left">
                             <h3 class="text-white font-black text-xl uppercase italic tracking-tighter mb-2">Painel Avançado</h3>
                             <p class="text-gray-400 text-sm leading-relaxed mb-6 max-w-lg">
                                 Nossa plataforma dispõe de um painel de afiliados avançado, permitindo que você visualize detalhes sobre ganhos e perdas. Além disso, oferece a capacidade de adicionar subafiliados.
                             </p>
                             <a href="/affiliate/login" class="inline-flex items-center gap-2 text-green-500 hover:text-green-400 font-bold text-sm uppercase tracking-widest transition-colors">
                                 Acessar Agora <i class="fa-regular fa-arrow-right"></i>
                             </a>
                         </div>
                         <!-- BG Pattern -->
                        <div class="absolute inset-0 opacity-30 bg-[url('/assets/images/pattern.png')] bg-repeat opacity-[0.03]"></div>
                    </div>

                </div>
            </div>
        </div>

        <!-- MODAL RECOMPENSAS DE REFERÊNCIA -->
        <div id="referenceRewardsEl" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-[100] hidden h-screen w-full overflow-y-auto overflow-x-hidden md:inset-0 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl mx-auto flex items-center justify-center h-full p-4">
                 <div class="relative w-full bg-[#141519] rounded-3xl shadow-2xl border border-white/5 overflow-hidden">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-white/5 bg-[#0c0d10]">
                        <h1 class="text-white font-bold text-lg">{{ $t('Referral Reward Rules') }}</h1>
                        <button @click.prevent="toggleReferenceRewards" class="size-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white transition-all">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6">
                        <div class="flex items-center justify-center gap-4 mb-6">
                            <div class="h-px bg-white/10 flex-1"></div>
                            <span class="text-gray-400 text-xs font-bold uppercase tracking-widest">Regras de Desbloqueio</span>
                            <div class="h-px bg-white/10 flex-1"></div>
                        </div>
                        
                        <!-- Content placeholder -->
                        <div class="text-gray-500 text-sm text-center italic">
                            Conteúdo das regras aqui...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL RECOMPENSAS POR COMISSÃO -->
        <div id="commissionRewardsEl" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-[100] hidden h-screen w-full overflow-y-auto overflow-x-hidden md:inset-0 backdrop-blur-sm">
             <div class="relative w-full max-w-2xl mx-auto flex items-center justify-center h-full p-4">
                <div class="relative w-full bg-[#141519] rounded-3xl shadow-2xl border border-white/5 overflow-hidden">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-white/5 bg-[#0c0d10]">
                        <h1 class="text-white font-bold text-lg">Regras de comissão</h1>
                        <button @click.prevent="toggleCommissionRewards" class="size-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white transition-all">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 md:p-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                         <div class="flex items-center justify-center gap-4 mb-8">
                            <div class="h-px bg-white/10 flex-1"></div>
                            <span class="text-gray-400 text-xs font-bold uppercase tracking-widest">Taxas de comissões</span>
                            <div class="h-px bg-white/10 flex-1"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <!-- Card 1 -->
                            <div class="bg-[#0c0d10] p-4 rounded-2xl border border-white/5 flex flex-col items-center gap-2">
                                <span class="text-3xl font-black text-white">7%</span>
                                <span class="text-gray-500 text-[10px] uppercase font-bold text-center">Jogos Originais</span>
                            </div>
                             <!-- Card 2 -->
                            <div class="bg-[#0c0d10] p-4 rounded-2xl border border-white/5 flex flex-col items-center gap-2">
                                <span class="text-3xl font-black text-white">7%</span>
                                <span class="text-gray-500 text-[10px] uppercase font-bold text-center">Slots de Terceiros</span>
                            </div>
                             <!-- Card 3 -->
                            <div class="bg-[#0c0d10] p-4 rounded-2xl border border-white/5 flex flex-col items-center gap-2">
                                <span class="text-3xl font-black text-white">25%</span>
                                <span class="text-gray-500 text-[10px] uppercase font-bold text-center">Esportes</span>
                            </div>
                        </div>

                        <div class="space-y-4 text-gray-400 text-sm px-2">
                            <p class="flex gap-3">
                                <span class="text-primary">•</span>
                                Em qualquer ambiente público (por exemplo, universidades, escolas...), apenas uma comissão pode ser paga para cada usuário único.
                            </p>
                            <p class="flex gap-3">
                                <span class="text-primary">•</span>
                                A decisão de validar uma aposta será baseada inteiramente em nosso critério após depósito e aposta confirmada.
                            </p>
                             <p class="flex gap-3">
                                <span class="text-primary">•</span>
                                As comissões podem ser retiradas para a carteira interna a qualquer momento.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL SAQUE -->
        <div id="withdrawalEl" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-[100] hidden h-screen w-full overflow-y-auto overflow-x-hidden md:inset-0 backdrop-blur-sm">
             <div class="relative w-full max-w-md mx-auto flex items-center justify-center h-full p-4">
                <div class="relative w-full bg-[#141519] rounded-3xl shadow-2xl border border-white/5 overflow-hidden">

                    <!-- Header -->
                     <div class="flex items-center justify-between p-6 border-b border-white/5 bg-[#0c0d10]">
                        <h1 class="text-white font-bold text-lg">{{ $t('Withdraw Rewards') }}</h1>
                        <button @click.prevent="opemModalWithdrawal" class="size-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white transition-all">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 md:p-8">
                        <form action="" @submit.prevent="makeWithdrawal" class="flex flex-col gap-5">
                            
                            <div class="bg-[#0c0d10] p-4 rounded-xl border border-white/5 mb-2">
                                <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-1">{{ $t('Available Balance') }}</p>
                                <p class="text-white font-mono text-xl font-bold">{{ state.currencyFormat(parseFloat(wallet?.refer_rewards), wallet?.currency) }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Valor do Saque</label>
                                <input v-model="withdrawalForm.amount" type="number" 
                                    class="w-full bg-[#0c0d10] border border-white/5 rounded-xl py-3 px-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all font-mono" 
                                    placeholder="0.00" required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Chave Pix</label>
                                <input v-model="withdrawalForm.pix_key" type="text" 
                                    class="w-full bg-[#0c0d10] border border-white/5 rounded-xl py-3 px-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all" 
                                    placeholder="Sua chave pix" required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Tipo de Chave</label>
                                <div class="relative group/select">
                                    <select v-model="withdrawalForm.pix_type" class="w-full bg-[#0c0d10] border border-white/5 rounded-xl py-3 px-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary appearance-none cursor-pointer transition-all" required>
                                        <option value="" disabled>Selecione o tipo</option>
                                        <option value="document">CPF/CNPJ</option>
                                        <option value="email">E-mail</option>
                                        <option value="phoneNumber">Telefone</option>
                                        <option value="randomKey">Chave Aleatória</option>
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="mt-4 w-full py-4 rounded-xl bg-green-500 hover:bg-green-600 text-white font-black uppercase text-sm tracking-widest shadow-lg shadow-green-500/20 transition-all transform active:scale-[0.98]">
                                Solicitar Saque
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>


<script>

import BaseLayout from "@/Layouts/BaseLayout.vue";
import { Modal } from 'flowbite';
import HttpApi from "@/Services/HttpApi.js";
import {useToast} from "vue-toastification";
import {useAuthStore} from "@/Stores/Auth.js";
import {useRouter} from "vue-router";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";

export default {
    props: [],
    components: { BaseLayout, Modal, LoadingComponent },
    data() {
        return {
            isLoading: false,
            referenceRewards: null,
            commissionRewards: null,
            isShowForm: false,
            isLoadingGenerate: false,
            code: '',
            urlCode: '',
            referencecode: '',
            referencelink: '',
            wallet: null,
            indications: 0,
            histories: null,
            withdrawalModal: null,
            withdrawalForm: {
                amount: 0,
                pix_key: '',
                pix_type: '',
            }
        }
    },
    setup(props) {
        const router = useRouter();
        return {
            router
        };
    },
    computed: {
        userData() {
            const authStore = useAuthStore();
            return authStore.user;
        }
    },
    mounted() {
        this.referenceRewards = new Modal(document.getElementById('referenceRewardsEl'), {
            placement: 'center',
            backdrop: 'dynamic',
            backdropClasses: 'bg-black/80 fixed inset-0 z-40',
            closable: true,
        });

        this.commissionRewards = new Modal(document.getElementById('commissionRewardsEl'), {
            placement: 'center',
            backdrop: 'dynamic',
            backdropClasses: 'bg-black/80 fixed inset-0 z-40',
            closable: true,
        });

        this.withdrawalModal = new Modal(document.getElementById('withdrawalEl'), {
            placement: 'center',
            backdrop: 'dynamic',
            backdropClasses: 'bg-black/80 fixed inset-0 z-40',
            closable: false,
        });
    },
    methods: {
        copyCode: function(event) {
            const _toast = useToast();
            var inputElement = document.getElementById("referenceCode");
            inputElement.select();
            inputElement.setSelectionRange(0, 99999);  // Para dispositivos móveis

            // Copia o conteúdo para a área de transferência
            document.execCommand("copy");
            _toast.success(this.$t('Code copied successfully'));
        },
        copyLink: function(event) {
            const _toast = useToast();
            var inputElement = document.getElementById("referenceLink");
            inputElement.select();
            inputElement.setSelectionRange(0, 99999);  // Para dispositivos móveis

            // Copia o conteúdo para a área de transferência
            document.execCommand("copy");
            _toast.success(this.$t('Link copied successfully'));
        },
        getCode: function() {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingGenerate = true;

            HttpApi.get('profile/affiliates/')
                .then(response => {
                    if( response.data.code !== '' && response.data.code !== undefined && response.data.code !== null) {
                        _this.isShowForm = true;
                        _this.code          = response.data.code;
                        _this.referencecode = response.data.code;
                        _this.urlCode       = response.data.url;
                    }

                    _this.indications   = response.data.indications;
                    _this.referencelink = response.data.url;
                    _this.wallet        = response.data.wallet;
                    _this.withdrawalForm.amount = response.data.wallet.refer_rewards;

                    _this.isLoadingGenerate = false;
                })
                .catch(error => {
                    _this.isShowForm = false;
                    _this.isLoadingGenerate = false;
                });
        },
        generateCode: function(event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingGenerate = true;

            HttpApi.get('profile/affiliates/generate')
                .then(response => {
                    if(response.data.status) {
                        _this.getCode();
                        _toast.success(_this.$t('Your code was generated successfully'));
                    }

                    _this.isLoadingGenerate = false;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingGenerate = false;
                });
        },
        toggleCommissionRewards: function(event) {
            // this.commissionRewards.toggle();
             const modal = new Modal(document.getElementById('commissionRewardsEl'), {
                placement: 'center',
                backdrop: 'dynamic',
                backdropClasses: 'bg-black/80 fixed inset-0 z-40',
                closable: true,
            });
            modal.show();
            this.commissionRewards = modal;
        },
        toggleReferenceRewards: function(event) {
            // this.referenceRewards.toggle();
             const modal = new Modal(document.getElementById('referenceRewardsEl'), {
                placement: 'center',
                backdrop: 'dynamic',
                backdropClasses: 'bg-black/80 fixed inset-0 z-40',
                closable: true,
            });
            modal.show();
            this.referenceRewards = modal;
        },
        opemModalWithdrawal: function() {
            // this.withdrawalModal.toggle();
             const modal = new Modal(document.getElementById('withdrawalEl'), {
                placement: 'center',
                backdrop: 'dynamic',
                backdropClasses: 'bg-black/80 fixed inset-0 z-40',
                closable: true,
            });
            modal.show();
            this.withdrawalModal = modal;
        },
        makeWithdrawal: async function() {
            const _this = this;
            const _toast = useToast();

            _this.isLoading = true;

            HttpApi.post('profile/affiliates/request', _this.withdrawalForm)
                .then(response => {
                    if(_this.withdrawalModal) {
                        _this.withdrawalModal.hide();
                    }

                    _toast.success(_this.$t(response.data.message));
                    _this.isLoading = false;
                    _this.router.push({ name: 'profileWallet' });
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoading = false;
                });
        }
    },
    created() {
      this.getCode();
    },
    watch: {

    },
};
</script>

<style scoped>

</style>
