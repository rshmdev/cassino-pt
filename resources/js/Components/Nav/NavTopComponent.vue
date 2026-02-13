<template>
    <nav class="fixed top-0 z-50 w-full bg-[#0c0d10] border-b border-white/5 h-[60px]">
        <div class="px-3 lg:px-5 w-full h-full">
            <div class="flex items-center justify-between h-full">
                
                <!-- Left Section: Mobile Menu & Logo -->
                <div class="flex items-center justify-start">
                    <button @click.prevent="toggleMenu" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600 mr-2">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>
                    
                    <a v-if="setting" href="/" class="flex items-center gap-2">
                        <div class="hidden sm:block">
                            <img :src="`/storage/`+setting.software_logo_white" alt="" class="h-8" />
                        </div>
                    </a>
                </div>

                <!-- Right Section -->
                <div class="flex items-center gap-4">
                    <div class="hidden lg:block mr-3 mt-1">
                        <LanguageSelector />
                    </div>

                    <!-- Non-Authenticated State -->
                    <div v-if="!isAuthenticated" class="flex items-center gap-3">
                        <button @click.prevent="loginToggle" class="text-white font-bold text-sm tracking-wide hover:text-primary transition-colors">
                            {{ $t('Log in') }}
                        </button>
                        <button @click.prevent="registerToggle" class="bg-primary hover:bg-primary/90 text-white text-sm font-black px-6 py-2 rounded-lg shadow-lg shadow-primary/20 transition-all uppercase tracking-wider">
                            {{ $t('Register') }}
                        </button>
                    </div>

                    <!-- Authenticated State -->
                    <div v-if="isAuthenticated" class="flex items-center gap-4">
                        
                        <!-- Wallet Pill -->
                        <div class="flex items-center bg-black/60 rounded-full border border-white/5 p-1 pl-4 gap-4 h-10">
                            <div class="flex items-center gap-1">
                                <span class="text-emerald-500 font-bold text-xs">R$</span>
                                <span class="text-white font-bold text-sm">{{ wallet ? stateCurrencyFormat(wallet.total_balance) : '0,00' }}</span>
                            </div>
                            <button @click.prevent="toggleDeposit(true)" class="bg-primary hover:bg-primary/90 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest transition-all">
                                {{ $t('Deposit') }}
                            </button>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button type="button" class="flex items-center gap-3 bg-[#1A1C20] hover:bg-[#25282e] rounded-full p-1 pl-4 pr-2 transition-colors border border-white/5" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                                <span class="text-white font-bold text-sm hidden md:block">{{ userData?.name }}</span>
                                <div class="size-8 rounded-full flex items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="z-50 hidden my-4 w-72 text-base list-none bg-[#0c0d10] rounded-2xl shadow-xl border border-white/10" id="dropdown-user">
                                <div class="p-5">
                                    <!-- Header -->
                                    <!-- Header -->
                                    <div class="flex items-center gap-4 mb-4 p-3 bg-white/5 rounded-xl border border-white/5">
                                        <div class="relative shrink-0">
                                            <div class="w-14 h-14 rounded-full bg-[#25282e] border-2 border-yellow-500/20 flex items-center justify-center overflow-hidden">
                                                <img v-if="userData?.avatar" :src="'/storage/'+userData.avatar" class="w-full h-full object-cover" alt="Avatar">
                                                <i v-else class="fa-duotone fa-user text-yellow-500 text-2xl"></i>
                                            </div>
                                            <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-[#1A1C20] rounded-full"></div>
                                        </div>
                                        <div class="flex flex-col">
                                            <p class="text-white font-bold text-lg leading-none">{{ userData?.name }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-gray-500 text-xs font-semibold tracking-wide">
                                                    Nível {{ userData?.level || 1 }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Menu Items -->
                                    <div class="space-y-2">
                                        <RouterLink :to="{ name: 'home' }" class="flex items-center gap-3 p-3 rounded-xl bg-[#131418] border border-white/5 hover:bg-[#1A1C20] hover:border-orange-500/30 group transition-all">
                                            <div class="size-8 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                                                <i class="fa-duotone fa-chart-simple"></i>
                                            </div>
                                            <span class="text-sm font-bold text-gray-400 group-hover:text-white transition-colors">{{ $t('Overview') }}</span> <!-- Visão Geral -->
                                        </RouterLink>

                                        <RouterLink :to="{ name: 'profileAffiliate' }" class="flex items-center gap-3 p-3 rounded-xl bg-[#131418] border border-white/5 hover:bg-[#1A1C20] hover:border-emerald-500/30 group transition-all">
                                            <div class="size-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                                                <i class="fa-duotone fa-users-medical"></i>
                                            </div>
                                            <span class="text-sm font-bold text-gray-400 group-hover:text-white transition-colors">{{ $t('Affiliate') }}</span>
                                        </RouterLink>

                                        <RouterLink :to="{ name: 'profileWallet' }" class="flex items-center gap-3 p-3 rounded-xl bg-[#131418] border border-white/5 hover:bg-[#1A1C20] hover:border-blue-500/30 group transition-all">
                                            <div class="size-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                                                <i class="fa-duotone fa-wallet"></i>
                                            </div>
                                            <span class="text-sm font-bold text-gray-400 group-hover:text-white transition-colors">{{ $t('My Wallet') }}</span>
                                        </RouterLink>

                                        <a href="#" @click.prevent="profileToggle" class="flex items-center gap-3 p-3 rounded-xl bg-[#131418] border border-white/5 hover:bg-[#1A1C20] hover:border-purple-500/30 group transition-all">
                                            <div class="size-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform">
                                                <i class="fa-duotone fa-user-gear"></i>
                                            </div>
                                            <span class="text-sm font-bold text-gray-400 group-hover:text-white transition-colors">{{ $t('My Profile') }}</span>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Footer / Logout -->
                                <div class="p-4 bg-[#08090b] border-t border-white/5 rounded-b-2xl">
                                    <button @click.prevent="logoutAccount" class="w-full flex items-center justify-center gap-2 p-3 rounded-xl bg-red-500/5 text-red-500 border border-red-500/10 hover:bg-red-500 hover:text-white transition-all font-bold text-xs uppercase tracking-widest">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        {{ $t('Sign out') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Toggle (Optional, assuming it exists based on image far right) -->
                        <!-- <div class="hidden lg:block">
                            <DropdownDarkLight />
                        </div> -->
                    </div>

                </div>

                <!-- Search Menu Overlay (Logic preserved) -->
                <transition name="fade">
                <transition name="fade">
                    <div v-if="showSearchMenu" class="fixed inset-0 z-[100] flex items-start justify-center pt-24">
                         <!-- Backdrop -->
                         <div @click="toggleSearch" class="absolute inset-0 bg-black/90 backdrop-blur-sm cursor-pointer transition-opacity"></div>
                         
                         <!-- Search Container -->
                          <div class="relative z-[110] w-full max-w-4xl px-4 animate-fade-in-down">
                                <div class="relative w-full group">
                                    <!-- Search Icon -->
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <i class="fa-regular fa-magnifying-glass text-gray-400 group-focus-within:text-primary transition-colors"></i>
                                    </div>
                                    
                                    <!-- Input -->
                                    <input type="search" v-model.lazy="searchTerm" 
                                           class="block w-full p-4 pl-12 text-sm text-white bg-[#1A1C20] border border-white/10 rounded-2xl focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-500 shadow-2xl transition-all" 
                                           :placeholder="$t('Search for game or provider')" required ref="searchInput">
                                    
                                    <!-- Close/Clear Button -->
                                    <button v-if="searchTerm.length > 0" @click.prevent="clearData" type="button" class="absolute inset-y-0 right-0 px-4 text-gray-500 hover:text-white transition-colors">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <!-- Results Grid -->
                                <div v-if="!isLoadingSearch && games?.data?.length" class="mt-6 bg-[#1A1C20]/50 border border-white/5 rounded-2xl p-6 backdrop-blur-md max-h-[70vh] overflow-y-auto custom-scrollbar">
                                    <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                                        <i class="fa-duotone fa-gamepad-modern text-primary"></i>
                                        {{ $t('Search results') }}
                                    </h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                        <CassinoGameCard v-for="(game, index) in games.data" :key="index" :index="index" :title="game.game_name" :cover="game.cover" :gamecode="game.game_code" :type="game.distribution" :game="game" />
                                    </div>
                                </div>
                                
                                <!-- No Results State -->
                                <div v-if="!isLoadingSearch && searchTerm.length > 0 && !games?.data?.length" class="mt-6 bg-[#1A1C20] border border-white/5 rounded-2xl p-8 text-center">
                                    <div class="size-16 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4 text-gray-500">
                                        <i class="fa-duotone fa-magnifying-glass-slash text-2xl"></i>
                                    </div>
                                    <p class="text-white font-bold">{{ $t('No games found') }}</p>
                                    <p class="text-gray-500 text-sm mt-1">{{ $t('Try searching for another term') }}</p>
                                </div>
                        </div>
                    </div>
                </transition>
                </transition>

            </div>
        </div>
    </nav>

    <div id="modalElAuth" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[90000] hidden w-full overflow-x-hidden overflow-y-auto md:inset-0 h-screen max-h-full transition-all duration-300">
        <div class="relative w-full max-w-lg mx-auto flex items-center justify-center min-h-full p-4">
            <div class="relative w-full bg-[#000000] min-h-[75dvh] sm:min-h-[70dvh] max-h-[90dvh] overflow-y-auto rounded-3xl shadow-2xl border border-white/10">
                <!-- Close Button -->
                <button @click.prevent="loginToggle" class="absolute top-4 right-4 z-[110] bg-black/50 hover:bg-black/80 text-white size-8 rounded-full flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

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
                    <button @click.prevent="modalAuth.show()" class="flex-1 py-3 rounded-xl font-bold text-sm transition-all duration-300" :class="true ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-500 hover:text-white'">
                        {{ $t('Log in') }}
                    </button>
                    <button @click.prevent="hideLoginShowRegisterToggle" class="flex-1 py-3 rounded-xl font-bold text-sm transition-all duration-300" :class="false ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-500 hover:text-white'">
                        {{ $t('Create a free account') }}
                    </button>
                </div>

                <div class="p-6 pt-0">
                    <p class="text-gray-400 text-xs text-center mb-6 px-4">
                        {{ $t('Access your account to continue enjoying the best offers') }}
                    </p>

                    <form @submit.prevent="loginSubmit" class="space-y-4">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input required type="text" v-model="loginForm.email" 
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-3.5 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all" 
                                   :placeholder="$t('Email placeholder')">
                        </div>

                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-lock"></i>
                            </div>
                            <input required :type="typeInputPassword" v-model="loginForm.password"
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-3.5 pl-11 pr-12 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all"
                                   :placeholder="$t('Password placeholder')">
                            <button type="button" @click.prevent="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-white transition-colors">
                                <i v-if="typeInputPassword === 'password'" class="fa-regular fa-eye"></i>
                                <i v-if="typeInputPassword === 'text'" class="fa-sharp fa-regular fa-eye-slash"></i>
                            </button>
                        </div>

                        <div class="flex justify-end">
                            <button @click.prevent="hideLoginShowForgotToggle" type="button" class="text-xs text-gray-500 hover:text-white transition-colors">{{ $t('Forgot password') }}</button>
                        </div>

                        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98] uppercase text-sm tracking-widest mt-4">
                            {{ $t('Log in') }}
                        </button>

                        <div class="text-center mt-8 pb-4">
                            <p class="text-gray-500 text-xs mb-2">{{ $t("Don't have an account yet?") }}</p>
                            <a href="" @click.prevent="hideLoginShowRegisterToggle" class="text-primary font-bold text-sm border-b border-primary/20 hover:border-primary transition-all pb-0.5">{{ $t('Create a free account') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="modalElRegister" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[100] hidden w-full overflow-x-hidden overflow-y-auto md:inset-0 h-screen max-h-full transition-all duration-300">
        <div class="relative w-full max-w-lg max-h-full mx-auto flex items-center justify-center min-h-full p-4">
            <div class="relative w-full bg-[#000000] min-h-[90dvh] max-h-[90dvh] overflow-y-auto rounded-3xl shadow-2xl border border-white/10">
                <!-- Close Button -->
                <button @click.prevent="registerToggle" class="absolute top-4 right-4 z-[110] bg-black/50 hover:bg-black/80 text-white size-8 rounded-full flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

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
                    <button @click.prevent="hideRegisterShowLoginToggle" class="flex-1 py-3 rounded-xl font-bold text-sm transition-all duration-300" :class="false ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-500 hover:text-white'">
                        {{ $t('Log in') }}
                    </button>
                    <button @click.prevent="modalRegister.show()" class="flex-1 py-3 rounded-xl font-bold text-sm transition-all duration-300" :class="true ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-500 hover:text-white'">
                        {{ $t('Create a free account') }}
                    </button>
                </div>

                <div class="p-6 pt-0">
                    <p class="text-gray-400 text-xs text-center mb-6 px-4">
                        {{ $t('Create an account to enjoy the best games and promotions') }}
                    </p>

                    <form @submit.prevent="registerSubmit" class="space-y-4">
                        <!-- Nome -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <input required type="text" v-model="registerForm.name" 
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-3.5 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all" 
                                   :placeholder="$t('Name placeholder')">
                        </div>

                        <!-- E-mail -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input required type="email" v-model="registerForm.email" 
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-3.5 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all" 
                                   :placeholder="$t('Email placeholder')">
                        </div>

                        <!-- Senha -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-lock"></i>
                            </div>
                            <input required :type="typeInputPassword" v-model="registerForm.password"
                                   class="w-full bg-[#09090b] border border-white/5 rounded-xl py-3.5 pl-11 pr-12 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all"
                                   :placeholder="$t('Password placeholder')">
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
                                   class="w-full bg-transparent border-none py-3.5 px-4 text-sm focus:ring-0 placeholder-gray-600"
                                   :placeholder="$t('Phone placeholder')">
                            </div>
                        </div>

                        <!-- Invite Code -->
                        <div class="text-center py-2">
                             <button @click.prevent="isReferral = !isReferral" type="button" class="text-white text-xs font-bold hover:text-primary transition-colors">
                                 {{ $t('Referral Code') }} <i class="fa-solid" :class="isReferral ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                             </button>
                             <div v-if="isReferral" class="mt-2 group relative">
                                 <input type="text" v-model="registerForm.reference_code" 
                                        class="w-full bg-[#09090b] border border-white/5 rounded-xl py-2 px-4 text-sm text-center text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-700"
                                        :placeholder="$t('CODE')">
                             </div>
                        </div>

                        <!-- Agreements -->
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <input id="register-term-1" v-model="registerForm.term_a" required type="checkbox" class="size-4 mt-0.5 rounded border-white/10 bg-white/5 text-primary focus:ring-primary focus:ring-offset-[#000]">
                                <label for="register-term-1" class="ml-3 text-[10px] leading-relaxed text-gray-500">
                                    {{ $t('I confirm I am at least 18 years old and agree with') }} <a href="#" class="text-primary font-bold hover:underline">{{ $t('terms and conditions') }}</a>.
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98] uppercase text-sm tracking-widest mt-4">
                            {{ $t('Create account') }}
                        </button>

                        <div class="text-center mt-6 pb-4">
                            <p class="text-gray-500 text-xs mb-2">{{ $t('Already have an account?') }}</p>
                            <a href="" @click.prevent="hideRegisterShowLoginToggle" class="text-primary font-bold text-sm border-b border-primary/20 hover:border-primary transition-all pb-0.5">{{ $t('Log in') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="modalElAge" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[200] hidden w-full overflow-x-hidden overflow-y-auto md:inset-0 h-screen max-h-full transition-all duration-300">
        <div class="relative w-full max-w-lg max-h-full mx-auto flex items-center justify-center min-h-full p-4">
            <div class="relative w-full bg-[#000000] min-h-[70dvh] sm:min-h-[60dvh] max-h-[90dvh] overflow-y-auto rounded-3xl shadow-2xl border border-white/10 p-10 flex flex-col items-center text-center">
                <!-- Logo -->
                <div v-if="setting" class="mb-10">
                    <img :src="`/storage/`+setting.software_logo_white" alt="" class="h-12 object-contain" />
                </div>

                <h2 class="text-white font-black text-2xl mb-4 uppercase tracking-tight italic">{{ $t('Are you over 18 years old?') }}</h2>

                <p class="text-gray-400 text-sm leading-relaxed mb-10 px-4">
                    {{ $t('Platform access restricted to adults') }}
                </p>

                <div class="w-full space-y-4">
                    <button @click.prevent="confirmAge" type="button" class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98] uppercase text-sm tracking-widest">
                        {{ $t('YES, I AM OVER 18 YEARS OLD') }}
                    </button>

                    <button @click.prevent="denyAge" type="button" class="w-full bg-transparent border border-white/10 hover:bg-white/5 text-white font-black py-4 rounded-xl transition-all uppercase text-sm tracking-widest">
                        {{ $t('NO') }}
                    </button>
                </div>

                <p class="mt-10 text-gray-600 text-[10px] leading-relaxed italic">
                    {{ $t('Responsible gaming is essential') }}
                </p>
            </div>
        </div>
    </div>

    <div id="modalElForgot" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[100] hidden w-full overflow-x-hidden overflow-y-auto md:inset-0 h-screen max-h-full transition-all duration-300">
        <div class="relative w-full max-w-lg max-h-full mx-auto flex items-center justify-center min-h-full p-4">
            <div class="relative w-full bg-[#000000] min-h-[43dvh] max-h-[90dvh] overflow-y-auto rounded-3xl shadow-2xl border border-white/10 p-8 flex flex-col items-center">
                <!-- Close Button -->
                <button @click.prevent="forgotToggle" class="absolute top-4 right-4 z-[110] bg-black/50 hover:bg-black/80 text-white size-8 rounded-full flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

                <!-- Logo -->  
                <!-- TODO: Colocar logo aqui -->
                <!-- <div v-if="setting" class="mb-8 mt-4">
                    <img :src="`/storage/`+setting.software_logo_white" alt="" class="h-12 object-contain" />
                </div> -->

                <h2 class="text-white font-black text-2xl mb-8 uppercase tracking-tight">{{ $t('Recover password') }}</h2>

                <form @submit.prevent="forgotPasswordSubmit" class="w-full space-y-6">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500 group-focus-within:text-primary transition-colors">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input required type="email" v-model="forgotForm.email" 
                               class="w-full bg-[#09090b] border border-white/5 rounded-xl py-4 pl-11 pr-4 text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-600 transition-all font-medium" 
                               :placeholder="$t('Email placeholder')">
                    </div>

                    <p class="text-gray-400 text-[11px] leading-relaxed text-center px-4">
                        {{ $t('Enter your verified email') }}
                    </p>

                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98] uppercase text-sm tracking-widest mt-2">
                        <span v-if="!isLoadingForgot">{{ $t('Send recovery email') }}</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ $t('Sending') }}
                        </span>
                    </button>

                    <div class="text-center pt-4">
                        <a href="" @click.prevent="hideForgotShowLoginToggle" class="text-primary font-bold text-sm border-b border-primary/20 hover:border-primary transition-all pb-0.5">{{ $t('Log in') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalProfileEl" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full overflow-x-hidden overflow-y-auto md:inset-0 h-screen max-h-full transition-all duration-300">
        <div class="relative w-full max-w-2xl mx-auto flex items-center justify-center min-h-full p-4">
            <div class="relative w-full bg-[#0c0d10] min-h-[50dvh] max-h-[90dvh] overflow-y-auto rounded-3xl shadow-2xl border border-white/5">
                
                <!-- Close Button (Absolute) -->
                <button @click.prevent="profileToggle" class="absolute top-4 right-4 z-20 size-8 rounded-full bg-black/50 hover:bg-black/80 flex items-center justify-center text-white transition-all backdrop-blur-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <!-- Body -->
                <div v-if="!isLoadingProfile && profileUser" class="flex flex-col relative">
                    
                    <!-- Top Banner / User Info -->
                    <div class="w-full h-40 bg-gradient-to-r from-primary/10 to-purple-500/10 relative">
                        <div class="absolute inset-0 bg-[url('/assets/images/pattern.png')] opacity-10"></div>
                    </div>

                    <div class="px-8 pb-8 -mt-16 relative z-10">
                        <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                            <!-- Avatar -->
                            <div class="relative group">
                                <div class="size-32 rounded-3xl p-1 bg-[#0c0d10] shadow-2xl">
                                    <img :src="avatarUrl" class="w-full h-full rounded-2xl object-cover" alt="Avatar">
                                </div>
                                <button @click="openFileInput" class="absolute -bottom-2 -right-2 size-10 bg-[#1A1C20] border border-white/10 rounded-xl flex items-center justify-center text-white hover:text-primary hover:border-primary/50 transition-all shadow-lg group-hover:scale-110 z-20">
                                    <i class="fa-solid fa-camera text-sm"></i>
                                </button>
                                <input ref="fileInput" type="file" class="hidden" @change="handleFileChange">
                            </div>

                            <!-- Name & Join Date -->
                            <div class="flex flex-col items-center md:items-start flex-1 mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="relative group flex items-center">
                                        <input @change.prevent="updateName" v-model="profileName" type="text" :readonly="!readonly"
                                            class="bg-transparent border-none text-white font-black text-3xl p-0 focus:ring-0 w-auto max-w-[250px] uppercase italic tracking-tighter"
                                            :class="readonly ? '' : 'border-b border-primary/50 bg-white/5 rounded px-2'">
                                        <button @click.prevent="readonly = !readonly" class="ml-3 text-gray-500 hover:text-white transition-colors">
                                            <i :class="!readonly ? 'fa-regular fa-pen text-sm' : 'fa-regular fa-check text-primary text-lg'"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide flex items-center gap-2 mt-1">
                                    <i class="fa-solid fa-calendar-days"></i>
                                    {{ $t('Member since') }} {{ profileUser.dateHumanReadable }}
                                </p>
                            </div>

                            <!-- Likes -->
                            <div class="mb-3">
                                <button @click.prevent="like(profileUser.id)" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 transition-all group">
                                    <i class="fa-solid fa-heart text-red-500 group-hover:scale-125 transition-transform"></i>
                                    <span class="text-white font-bold">{{ profileUser.totalLikes }}</span>
                                    <span class="text-gray-500 text-xs uppercase font-bold">{{ $t('Likes') }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="w-full h-px bg-white/5 my-8"></div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Winnings -->
                            <div class="bg-[#141519] p-5 rounded-2xl border border-white/5 flex flex-col items-center justify-center gap-2 hover:border-green-500/20 transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 size-20 rounded-full bg-green-500/5 group-hover:bg-green-500/10 transition-colors blur-xl"></div>
                                <div class="size-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500 mb-1 z-10 border border-green-500/10">
                                    <i class="fa-duotone fa-trophy text-xl"></i>
                                </div>
                                <div class="flex flex-col items-center z-10">
                                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">{{ $t('Total winnings') }}</span>
                                    <span class="text-white font-bold text-xl">{{ totalEarnings }}</span>
                                </div>
                            </div>

                            <!-- Bets -->
                            <div class="bg-[#141519] p-5 rounded-2xl border border-white/5 flex flex-col items-center justify-center gap-2 hover:border-blue-500/20 transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 size-20 rounded-full bg-blue-500/5 group-hover:bg-blue-500/10 transition-colors blur-xl"></div>
                                <div class="size-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 mb-1 z-10 border border-blue-500/10">
                                    <i class="fa-duotone fa-gamepad-modern text-xl"></i>
                                </div>
                                <div class="flex flex-col items-center z-10">
                                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">{{ $t('Total bets') }}</span>
                                    <span class="text-white font-bold text-xl">{{ totalBets }}</span>
                                </div>
                            </div>
                            
                            <!-- Total Bet Value -->
                            <div class="bg-[#141519] p-5 rounded-2xl border border-white/5 flex flex-col items-center justify-center gap-2 hover:border-purple-500/20 transition-all group relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 size-20 rounded-full bg-purple-500/5 group-hover:bg-purple-500/10 transition-colors blur-xl"></div>
                                <div class="size-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500 mb-1 z-10 border border-purple-500/10">
                                    <i class="fa-duotone fa-coins text-xl"></i>
                                </div>
                                <div class="flex flex-col items-center z-10">
                                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">{{ $t('Total bet') }}</span>
                                    <span class="text-white font-bold text-xl">{{ sumBets }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Loading State -->
                <div v-if="isLoadingProfile" class="w-full h-full min-h-[400px] flex items-center justify-center">
                    <div class="flex flex-col items-center gap-4">
                        <svg aria-hidden="true" class="w-10 h-10 text-gray-800 animate-spin fill-primary" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                        <span class="text-gray-400 text-sm font-medium">{{ $t('Loading') }}...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Deposit Modal -->
    <div id="modalElDeposit" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[90000] hidden w-full overflow-x-hidden overflow-y-auto md:inset-0 h-screen max-h-full transition-all duration-300">
        <div class="relative w-full max-w-5xl max-h-full mx-auto flex items-center justify-center min-h-full p-4">
            <div class="relative bg-white rounded-xl shadow dark:bg-[#1A1C20] border border-white/5 max-h-[90dvh] overflow-y-auto">
                <button @click.prevent="toggleDeposit(false)" type="button" class="absolute top-4 right-4 text-gray-400 bg-transparent hover:bg-white/10 hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center transition-all">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="p-6 w-full">
                    <h3 class="mb-5 text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="size-10 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                            <i class="fa-duotone fa-wallet text-xl"></i>
                        </div>
                        {{ $t('Deposit to Wallet') }}
                    </h3>
                    
                    <DepositWidget v-if="modalsStore.modals.deposit" :showMobile="false" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { RouterLink, useRoute, useRouter } from "vue-router";
import { sidebarStore } from "@/Stores/SideBarStore.js";
import { Modal } from 'flowbite';
import { useAuthStore } from "@/Stores/Auth.js";
import { useToast } from "vue-toastification";
import { useModalStore } from "@/Stores/ModalStore.js"; 

import DropdownDarkLight from "@/Components/UI/DropdownDarkLight.vue";
import LanguageSelector from "@/Components/UI/LanguageSelector.vue";
import WalletBalance from "@/Components/UI/WalletBalance.vue";
import HttpApi from "@/Services/HttpApi.js";
import MakeDeposit from "@/Components/UI/MakeDeposit.vue";
import {useSettingStore} from "@/Stores/SettingStore.js";
import {searchGameStore} from "@/Stores/SearchGameStore.js";
import CassinoGameCard from "@/Pages/Cassino/Components/CassinoGameCard.vue";
import DepositWidget from "@/Components/Widgets/DepositWidget.vue";

export default {
    props: ['simple'],
    components: {CassinoGameCard, MakeDeposit, WalletBalance, LanguageSelector, DropdownDarkLight, RouterLink, DepositWidget },
    data() {
        return {
            isLoadingLogin: false,
            isLoadingRegister: false,
            isReferral: false,
            modalAuth: null,
            modalRegister: null,
            modalProfile: null,
            modalForgot: null,
            modalAge: null,
            modalDeposit: null,
            typeInputPassword: 'password',
            readonly: false,
            profileUser: null,
            isLoadingForgot: false,
            loginForm: {
                email: '',
                password: '',
            },
            forgotForm: {
                email: '',
            },
            registerForm: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                reference_code: '',
                term_a: false,
                agreement: false,
                phone: '',
            },
            avatarUrl: '/assets/images/profile.jpg',
            isLoadingProfile: false,
            profileName: '',
            sumBets: 0,
            totalBets: 0,
            totalEarnings: 0,
            showSearchMenu: false,
            games: null,
            searchTerm: '',
            isLoadingSearch: true,
            wallet: null,
            intervalWallet: null,
        }
    },
    setup() {
        const router = useRouter();
        const modalsStore = useModalStore();

        return {
            router,
            modalsStore
        };
    },
    computed: {
        searchGameDataStore() {
            return searchGameStore();
        },
        searchGameMenu() {
            const search = searchGameStore();
            return search.getSearchGameStatus;
        },
        sidebarMenuStore() {
            return sidebarStore();
        },
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
        userData() {
            const authStore = useAuthStore();
            return authStore.user;
        },
        setting() {
            const authStore = useSettingStore();
            return authStore.setting;
        }
    },
    unmounted() {
        if(this.intervalWallet) clearInterval(this.intervalWallet);
        document.body.style.overflow = '';
    },
    watch: {
        'modalsStore.modals.deposit'(newVal) {
            if (newVal) {
                this.modalDeposit?.show();
            } else {
                this.modalDeposit?.hide();
            }
        },
        showSearchMenu(newVal) {
            if (newVal) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        },
        searchTerm(newValue, oldValue) {
            this.getSearch();
        },
        async searchGameMenu(newValue, oldValue) {
            await this.getSearch();
            this.showSearchMenu = !this.showSearchMenu;
        },
        isAuthenticated(newVal) {
            if(newVal) {
                this.getWallet();
                if(this.intervalWallet) clearInterval(this.intervalWallet);
                this.intervalWallet = setInterval(() => {
                    this.getWallet();
                }, 10000);
            } else {
                this.wallet = null;
                if(this.intervalWallet) clearInterval(this.intervalWallet);
            }
        }
    },
    mounted() {
        this.modalProfile = new Modal(document.getElementById('modalProfileEl'), {
            placement: 'center',
            backdrop: 'dynamic',
            backdropClasses: 'bg-black/80 fixed inset-0 z-40',
            closable: false,
        });

        this.modalAuth = new Modal(document.getElementById('modalElAuth'), {
            placement: 'center',
            backdrop: 'dynamic',
            backdropClasses: 'bg-black/80 fixed inset-0 z-40',
            closable: false,
        });

        this.modalForgot = new Modal(document.getElementById('modalElForgot'), {
            placement: 'center',
            backdrop: 'dynamic',
            backdropClasses: 'bg-black/80 fixed inset-0 z-40',
            closable: false,
        });

        this.modalAge = new Modal(document.getElementById('modalElAge'), {
            placement: 'center',
            backdrop: 'static',
            backdropClasses: 'bg-black/90 fixed inset-0 z-[190]',
            closable: false,
        });

        const isAgeConfirmed = localStorage.getItem('age_confirmed');
        if (!isAgeConfirmed && this.modalAge) {
            this.modalAge.show();
        }

        this.modalRegister = new Modal(document.getElementById('modalElRegister'), {
            placement: 'center',
            backdrop: 'dynamic',
            backdropClasses: 'bg-black/80 fixed inset-0 z-40',
            closable: false,
        });

        // Initialize Deposit Modal
        const depositModalEl = document.getElementById('modalElDeposit');
        if (depositModalEl) {
            this.modalDeposit = new Modal(depositModalEl, {
                placement: 'center',
                backdrop: 'dynamic',
                backdropClasses: 'bg-black/80 fixed inset-0 z-[60]',
                closable: true,
                onHide: () => {
                    this.modalsStore.toggleModal('deposit', false);
                },
            });
        }

        if(this.isAuthenticated) {
            this.getWallet();
             this.intervalWallet = setInterval(() => {
                this.getWallet();
            }, 10000);
        }
    },
    methods: {
        toggleDeposit(show) {
            this.modalsStore.toggleModal('deposit', show);
        },
        toggleSearch: function() {
            this.searchGameDataStore.setSearchGameToogle();
        },
        redirectSocialTo: function() {
            return '/auth/redirect/google'
        },
        like: async function(id) {
            const _this = this;
            const _toast = useToast();
            await HttpApi.post('/profile/like/' + id, {})
                .then(response => {
                    _this.getProfile();
                    _toast.success(_this.$t(response.data.message));
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                });
        },
        updateName: async function(event) {
            const _this = this;
            _this.isLoadingProfile = true;

            await HttpApi.post('/profile/updateName', { name: _this.profileName })
                .then(response => {
                    _this.isLoadingProfile = false;
                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {

                    });
                    _this.isLoadingProfile = false;
                });
        },
        togglePassword: function() {
            if(this.typeInputPassword === 'password') {
                this.typeInputPassword = 'text';
            }else{
                this.typeInputPassword = 'password';
            }
        },
        loginToggle: function() {
            this.modalAuth.toggle();
        },
        registerToggle: function() {
            this.modalRegister.toggle();
        },
        loginSubmit: async function(event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingLogin = true;
            const authStore = useAuthStore();

            await HttpApi.post('/auth/login', _this.loginForm)
                .then(async response => {
                    await new Promise(r => {
                         setTimeout(() => {
                             authStore.setToken(response.data.access_token);
                             authStore.setUser(response.data.user);
                             authStore.setIsAuth(true);

                             _this.isLoadingLogin = false;
                             _this.modalAuth.toggle();
                             _toast.success(_this.$t(response.data.message));
                             window.location.reload();
                         }, 1000)
                    });
                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingLogin = false;
                });
        },
        registerSubmit: async function(event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingRegister = true;

            _this.registerForm.password_confirmation = _this.registerForm.password;

            await HttpApi.post('/auth/register', _this.registerForm)
                .then(response => {
                    _this.isLoadingRegister = false;
                    _this.modalRegister.toggle();

                    if(response.data.access_token !== undefined) {
                        const authStore = useAuthStore();
                        authStore.setToken(response.data.access_token);
                        authStore.setUser(response.data.user);
                        authStore.setIsAuth(true);
                        _toast.success(_this.$t('Your account has been created successfully'));
                        _this.router.push({ name: 'home' });
                    } else {
                        _this.loginForm.email = _this.registerForm.email;
                        _this.loginForm.password = _this.registerForm.password;
                        _this.loginSubmit();
                    }
                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingRegister = false;
                });
        },
        logoutAccount: function() {
            const authStore = useAuthStore();
            const _toast = useToast();

            HttpApi.post('auth/logout', {})
                .then(response => {
                    authStore.logout();
                    this.router.push({ name: 'home' });

                    _toast.success(this.$t('You have been successfully disconnected'));
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        console.log(value);
                    });
                });
        },
        hideLoginShowRegisterToggle: function() {
            this.modalAuth.hide();
            this.modalRegister.show();
        },
        hideRegisterShowLoginToggle: function() {
            this.modalRegister.hide();
            this.modalAuth.show();
        },
        hideLoginShowForgotToggle: function() {
            this.modalAuth.hide();
            this.modalForgot.show();
        },
        hideForgotShowLoginToggle: function() {
            this.modalForgot.hide();
            this.modalAuth.show();
        },
        forgotPasswordSubmit: async function() {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingForgot = true;

            await HttpApi.post('auth/forget-password', _this.forgotForm)
                .then(async response =>  {
                    _this.isLoadingForgot = false;
                    _toast.success(_this.$t('A token has been sent to you in your email box!'));
                    _this.forgotForm.email = '';
                    _this.hideForgotShowLoginToggle();
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingForgot = false;
                });
        },
        toggleMenu: function() {
            this.sidebarMenuStore.setSidebarToogle();
        },
        profileToggle: function() {
            this.modalProfile.toggle();
        },
        forgotToggle: function() {
            this.modalForgot.toggle();
        },
        openFileInput() {
            this.$refs.fileInput.click();
        },
        async handleFileChange(event) {
            const file = event.target.files[0];
            const formData = new FormData();
            formData.append('avatar', file);

            const reader = new FileReader();
            reader.onload = () => {
                this.avatarUrl = reader.result;
            };
            reader.readAsDataURL(file);

            await HttpApi.post('/profile/upload-avatar', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }).then(response => {
                console.log('Avatar atualizado com sucesso', response.data);
            })
                .catch(error => {
                    console.error('Erro ao atualizar avatar', error);
                });
        },
        getProfile: async function() {
            const _this = this;
            _this.isLoadingProfile = true;

            await HttpApi.get('/profile/')
                .then(response => {
                    _this.sumBets = response.data.sumBets;
                    _this.totalBets = response.data.totalBets;
                    _this.totalEarnings = response.data.totalEarnings;

                    const user = response.data.user;

                    if(user?.avatar != null) {
                        _this.avatarUrl = '/storage/'+user.avatar;
                    }

                    _this.profileName = user.name;
                    _this.profileUser = user;
                    _this.isLoadingProfile = false;
                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {

                    });
                    _this.isLoadingProfile = false;
                });
        },
        confirmAge: function() {
            localStorage.setItem('age_confirmed', 'true');
            if (this.modalAge) {
                this.modalAge.hide();
            }
        },
        denyAge: function() {
            window.location.href = 'https://www.google.com';
        },
        getSearch: async function() {
            const _this = this;

            await HttpApi.get('/search/games?searchTerm='+this.searchTerm)
                .then(response => {
                    _this.games = response.data.games;
                    _this.isLoadingSearch = false;
                })
                .catch(error => {
                    const _this = this;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {

                    });
                    _this.isLoadingSearch = false;
                });
        },
        clearData: async function() {
            this.searchTerm = '';
            await this.getSearch();
        },
        stateCurrencyFormat(value) {
            if(value === undefined || value === null) return '0,00';
            return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
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
    async created() {
        if(this.isAuthenticated) {
            await this.getProfile();
        }
    },
};
</script>

<style scoped>
/* Hide default search clear button in WebKit browsers */
input[type="search"]::-webkit-search-decoration,
input[type="search"]::-webkit-search-cancel-button,
input[type="search"]::-webkit-search-results-button,
input[type="search"]::-webkit-search-results-decoration {
  display: none;
}
</style>
