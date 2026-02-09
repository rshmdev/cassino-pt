<template>
    <GameLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="text-center">
                <span>{{ $t('Loading game information') }}</span>
            </div>
        </LoadingComponent>

        <div v-if="!isLoading && game" class="w-full relative bg-[#0c0d10]">
            <div class="game-screen w-full h-[calc(100dvh-60px-64px)] md:h-[calc(100dvh-60px)]" id="game-screen">
                <fullscreen v-model="fullscreen" :page-only="pageOnly" class="w-full h-full">
                    <div v-if="showButton && game.game_type === 'live' && game.distribution === 'evergame'" class="game-full fullscreen-wrapper flex items-center justify-center w-full h-full">
                        <button @click.prevent="openModal(gameUrl)" type="button" class="py-2.5 px-5 me-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                            Clique para começar
                        </button>
                    </div>
                    <iframe v-else :src="gameUrl" class="game-full fullscreen-wrapper w-full h-full border-none"></iframe>
                </fullscreen>
            </div>

        </div>

        <div v-if="undermaintenance" class="flex flex-col items-center justify-center text-center py-24">
            <h1 class="text-2xl mb-4">JOGO EM MANUTENÇÃO</h1>
            <img :src="`/assets/images/work-in-progress.gif`" alt="" width="400">
        </div>
    </GameLayout>
</template>

<script>
import { initFlowbite, Modal } from 'flowbite';
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/Stores/Auth.js";
import { component } from 'vue-fullscreen';
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import GameLayout from "@/Layouts/GameLayout.vue";
import HttpApi from "@/Services/HttpApi.js";

import {
    defineComponent,
    toRefs,
    reactive,
} from 'vue';

export default {
    props: [],
    components: {
        GameLayout,
        LoadingComponent,
        RouterLink,
        component
    },
    data() {
        return {
            isLoading: true,
            game: null,
            modeMovie: false,
            gameUrl: null,
            token: null,
            gameId: null,
            undermaintenance: false,
        }
    },
    setup() {
        const router = useRouter();
        const state = reactive({
            fullscreen: false,
            pageOnly: false,
        })
        function togglefullscreen() {
            console.log("CLICOU");
            state.fullscreen = !state.fullscreen
        }

        return {
            ...toRefs(state),
            togglefullscreen,
            router
        }
    },
    computed: {
        userData() {
            const authStore = useAuthStore();
            return authStore.user;
        },
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
    },
    mounted() {

        const userAgent = navigator.userAgent.toLowerCase();
        const isSafari = userAgent.includes('safari') && !userAgent.includes('chrome');
        const isSamsungInternet = userAgent.includes('samsung') && userAgent.includes('safari') && !userAgent.includes('chrome');
        const isIOS = userAgent.includes('iphone') || userAgent.includes('ipad');

        if (isSafari || isSamsungInternet || isIOS) {
            this.showButton = true;
        }
    },
    methods: {

        openModal(gameUrl) {
            console.log(gameUrl)
            window.open(gameUrl);
        },

        getGame: async function() {
            const _this = this;

            return await HttpApi.get('games/single/'+ _this.gameId)
                .then(async response =>  {
                    if(response.data?.action === 'deposit') {
                        _this.$nextTick(() => {
                            _this.router.push({ name: 'profileDeposit' });
                        });

                    }

                    const game = response.data.game;
                    _this.game = game;

                    // if(game.distribution == 'evergame') {
                    //     window.open(response.data.gameUrl)
                    // }

                    _this.gameUrl = response.data.gameUrl;
                    _this.token = response.data.token;
                    _this.isLoading = false;


                })
                .catch(error => {

                    _this.isLoading = false;
                    _this.undermaintenance = true;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {

                    });
                });
        },
        toggleFavorite: function() {
            const _this = this;
            return HttpApi.post('games/favorite/'+ _this.game.id, {})
                .then(response =>  {
                    _this.getGame();
                    _this.isLoading = false;
                })
                .catch(error => {
                    _this.isLoading = false;
                });
        },
        toggleLike: async function() {
            const _this = this;
            return await HttpApi.post('games/like/'+ _this.game.id, {})
                .then(async response =>  {
                    await _this.getGame();
                    _this.isLoading = false;
                })
                .catch(error => {
                    _this.isLoading = false;
                });
        }
    },
    async created() {
        if(this.isAuthenticated) {
            const route = useRoute();
            this.gameId = route.params.id;


            await this.getGame();
        }else{
            this.router.push({ name: 'login', params: { action: 'openlogin' } });
        }
    },
    watch: {


    },
};
</script>

<style>
.game-screen {
    width: 100%;
}
.game-full {
    width: 100%;
    height: 100%;
}
</style>
