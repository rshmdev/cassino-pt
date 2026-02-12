<template>
    <aside :class="[
              sidebar === true ? 'translate-x-0' : '-translate-x-full',
            ]"
           class="fixed top-[60px] left-0 z-40 w-64 h-[calc(100vh-124px)] sm:h-[calc(100vh-60px)] transition-transform -translate-x-full sm:translate-x-0 bg-[#0c0d10] border-r border-white/5 flex flex-col"
           aria-label="Sidebar">
        
        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-6">
            
            <!-- Language Selector (Mobile Only) -->
            <div class="lg:hidden w-full flex justify-end mb-2">
                <LanguageSelector />
            </div>

            <!-- Home Button -->
            <RouterLink :to="{ name: 'home' }" 
                        active-class="bg-[#1A1C20] border-primary/50 text-white"
                        class="flex items-center w-full p-4 rounded-xl bg-[#131418] border border-white/5 text-gray-400 hover:text-white hover:border-primary/50 hover:bg-[#1A1C20] transition-all group">
                <div class="p-2 rounded-lg bg-white/5 group-active:scale-95 transition-transform mr-4">
                     <i class="fa-duotone fa-house text-xl text-primary"></i>
                </div>
                <span class="font-bold text-sm uppercase tracking-wide">{{ $t('Home') }}</span>
            </RouterLink>

            <!-- Action Buttons Grid -->
            <div class="grid gap-3" :class="isAuthenticated ? 'grid-cols-2' : 'grid-cols-1'">
                <button @click.prevent="toggleMissionModal" class="relative overflow-hidden rounded-xl bg-[#2e1065] border border-white/10 p-3 flex flex-col items-center justify-center group hover:border-[#8b5cf6] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <i class="fa-duotone fa-rocket-launch text-2xl text-[#a78bfa] mb-2 group-hover:-translate-y-1 transition-transform"></i>
                    <span class="text-[10px] font-black uppercase text-white/90 tracking-wider">{{ $t('Missions') }}</span>
                </button>

                <button v-if="isAuthenticated" @click.prevent="$router.push('/profile/affiliate')" class="relative overflow-hidden rounded-xl bg-[#451a03] border border-white/10 p-3 flex flex-col items-center justify-center group hover:border-[#f97316] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <i class="fa-duotone fa-gift text-2xl text-[#fdba74] mb-2 group-hover:-translate-y-1 transition-transform"></i>
                    <span class="text-[10px] font-black uppercase text-white/90 tracking-wider">{{ $t('Refer') }}</span>
                </button>
            </div>

            <!-- Categories -->
            <div class="space-y-2">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest px-2 mb-3">{{ $t('Categories') }}</h3>
                
                <ul class="space-y-1">
                    <!-- Favoritos -->
                    <li>
                         <a href="#" @click.prevent="isAuthenticated ? $router.push('/profile/favorites') : $router.push('/login')" class="flex items-center p-3 text-gray-400 rounded-lg hover:text-white hover:bg-white/5 group transition-colors">
                            <i class="fa-duotone fa-star w-6 text-center text-lg group-hover:text-yellow-400 transition-colors"></i>
                            <span class="ml-3 text-xs font-bold uppercase tracking-wide">{{ $t('Favorites') }}</span>
                         </a>
                    </li>
                    
                    <!-- Slots -->
                     <li>
                         <RouterLink :to="{ name: 'casinosAll', params: { provider: 'all', category: 'slots' }}" active-class="text-white bg-white/5" class="flex items-center p-3 text-gray-400 rounded-lg hover:text-white hover:bg-white/5 group transition-colors">
                            <i class="fa-duotone fa-dice w-6 text-center text-lg group-hover:text-primary transition-colors"></i>
                            <span class="ml-3 text-xs font-bold uppercase tracking-wide">{{ $t('Slots') }}</span>
                         </RouterLink>
                    </li>
                    
                    <!-- Live Casino -->
                     <li>
                         <RouterLink :to="{ name: 'casinosAll', params: { provider: 'all', category: 'live-casino' }}" active-class="text-white bg-white/5" class="flex items-center p-3 text-gray-400 rounded-lg hover:text-white hover:bg-white/5 group transition-colors">
                            <i class="fa-duotone fa-cards w-6 text-center text-lg group-hover:text-red-400 transition-colors"></i>
                            <span class="ml-3 text-xs font-bold uppercase tracking-wide">{{ $t('Live Casino') }}</span>
                         </RouterLink>
                    </li>

                    <!-- Aviator -->
                    <li>
                         <RouterLink :to="{ name: 'casinosAll', params: { provider: 'all', category: 'crashChoice' }}" active-class="text-white bg-white/5" class="flex items-center justify-between p-3 text-gray-400 rounded-lg hover:text-white hover:bg-white/5 group transition-colors">
                            <div class="flex items-center">
                                <i class="fa-duotone fa-plane-up w-6 text-center text-lg group-hover:text-red-500 transition-colors"></i>
                                <span class="ml-3 text-xs font-bold uppercase tracking-wide">{{ $t('Aviator') }}</span>
                            </div>
                            <span class="bg-[#be123c] text-white text-[9px] font-black px-1.5 py-0.5 rounded ml-2 animate-pulse">HOT</span>
                         </RouterLink>
                    </li>

                    <!-- Mines -->
                     <li>
                         <RouterLink :to="{ name: 'casinosAll', params: { provider: 'all', category: 'mines' }}" active-class="text-white bg-white/5" class="flex items-center p-3 text-gray-400 rounded-lg hover:text-white hover:bg-white/5 group transition-colors">
                            <i class="fa-duotone fa-bomb w-6 text-center text-lg group-hover:text-yellow-500 transition-colors"></i>
                            <span class="ml-3 text-xs font-bold uppercase tracking-wide">{{ $t('Mines') }}</span>
                         </RouterLink>
                    </li>

                    <!-- Spaceman -->
                     <li>
                         <RouterLink :to="{ name: 'casinosAll', params: { provider: 'all', category: 'spaceman' }}" active-class="text-white bg-white/5" class="flex items-center p-3 text-gray-400 rounded-lg hover:text-white hover:bg-white/5 group transition-colors">
                            <i class="fa-duotone fa-user-astronaut w-6 text-center text-lg group-hover:text-purple-500 transition-colors"></i>
                            <span class="ml-3 text-xs font-bold uppercase tracking-wide">{{ $t('Spaceman') }}</span>
                         </RouterLink>
                    </li>

                </ul>
            </div>
            
        </div>

        <!-- Wallet Widget (Fixed Bottom) -->
        <div class="p-4 bg-[#0c0d10] border-t border-white/5" v-if="isAuthenticated">
            <div class="relative overflow-hidden bg-[#131418] rounded-xl border border-white/5 p-4 group">
                <!-- Header -->
                <div class="flex justify-between items-center mb-1">
                    <div class="flex items-center gap-2">
                        <i class="fa-duotone fa-wallet text-gray-500 text-xs"></i>
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ $t('Wallet') }}</span>
                    </div>
                    <button @click.prevent="modalsStore.toggleModal('deposit', true)" class="text-primary hover:text-white transition-colors">
                        <i class="fa-solid fa-circle-plus text-base"></i>
                    </button>
                </div>

                <!-- Balance -->
                 <div class="flex items-baseline gap-1 mb-3">
                    <span class="text-xs text-primary font-bold">R$</span>
                    <span class="text-xl font-black text-white tracking-tight">
                        {{ wallet ? stateCurrencyFormat(wallet.total_balance) : '0,00' }}
                    </span>
                    <span class="text-[10px] text-gray-500 font-medium hidden">BRL</span>
                </div>

                <!-- Progress Bar Mockup -->

                
                <!-- Bottom Decorative Line -->
                <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gradient-to-r from-primary to-transparent opacity-50"></div>
            </div>
        </div>
        <!-- <div v-else class="p-4 bg-[#0c0d10] border-t border-white/5">
             <button @click.prevent="$router.push('/login')" class="w-full py-3 rounded-xl bg-primary hover:bg-primary/90 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary/20 transition-all">
                Entrar
            </button>
        </div> -->

    </aside>
</template>

<script>
import { onMounted, ref } from "vue";
import { sidebarStore } from "@/Stores/SideBarStore.js";
import { RouterLink, useRouter } from "vue-router";
import { useModalStore } from "@/Stores/ModalStore.js";
import HttpApi from "@/Services/HttpApi.js";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/Stores/Auth.js";
import { useSettingStore } from "@/Stores/SettingStore.js";
import { missionStore } from "@/Stores/MissionStore.js";
import LanguageSelector from "@/Components/UI/LanguageSelector.vue";

export default {
    props: [],
    components: { RouterLink, LanguageSelector },
    setup() {
        const modalsStore = useModalStore();
        return { modalsStore };
    },
    data() {
        return {
            sidebar: false,
            isLoading: true,
            categories: [],
            wallet: null,
            intervalWallet: null,
        }
    },
    computed: {
        sidebarMenuStore() {
            return sidebarStore()
        },
        sidebarMenu() {
            const sidebar = sidebarStore()
            return sidebar.getSidebarStatus;
        },
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
        settingStore() {
            return useSettingStore();
        },
        setting() {
            return this.settingStore.setting;
        },
    },
    mounted() {
        if(this.isAuthenticated) {
            this.getWallet();
             this.intervalWallet = setInterval(() => {
                this.getWallet();
            }, 10000);
        }
    },
    beforeUnmount() {
        if(this.intervalWallet) clearInterval(this.intervalWallet);
    },
    methods: {
        stateCurrencyFormat(value) {
            if(value === undefined || value === null) return '0,00';
            return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
        },
        toggleMenu() {
            this.sidebarMenuStore.setSidebarToogle();
        },
        toggleMissionModal: function() {
            const missionDataStore = missionStore();
            missionDataStore.setMissionToogle();
        },
        getWallet: function() {
            const _this = this;
            HttpApi.get('profile/wallet')
                .then(response => {
                    _this.wallet = response.data.wallet;
                })
                .catch(error => {
                    console.error('Error fetching wallet:', error);
                });
        },
    },
    watch: {
        sidebarMenu(newVal, oldVal) {
            this.sidebar = newVal;
        },
        isAuthenticated(newVal) {
            if(newVal) {
                this.getWallet();
            } else {
                this.wallet = null;
            }
        }
    },
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #0c0d10; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #333; 
  border-radius: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #555; 
}
</style>
