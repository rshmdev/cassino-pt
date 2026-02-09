<template>
    <BaseLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="text-center">
                <span>Carregando os jogos</span>
            </div>
        </LoadingComponent>

        <div v-if="!isLoading" class="md:w-4/6 2xl:w-4/6 mx-auto">
            <div class="px-4 py-5">
                <HeaderComponent>
                    <template #header>
                        {{ $t('List of') }} <span class=" bg-blue-100 text-blue-800 text-2xl font-semibold me-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ms-2">{{ $t('Games') }}</span>
                    </template>

                    <p class="text-2xl flex items-center justify-center">{{ $t('Total') }} <strong>({{ games?.total ?? 0 }})</strong></p>
                </HeaderComponent>

                <form class="mb-5 mt-5">
                    <label for="search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">
                        {{ $t('Search') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input v-model="searchTerm" @input="searchGames" type="search" id="search"
                               class="block w-full p-4 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/20 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               :placeholder="$t('Search')"
                               required>
                    </div>
                </form>

                <div v-if="games && games?.total > 0">
                    <div class="relative w-full">
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-5">
                            <CassinoGameCard
                                v-for="(game, index) in games.data"
                                :index="index"
                                :title="game.game_name"
                                :cover="game.cover"
                                :gamecode="game.game_code"
                                :type="game.distribution"
                                :game="game"
                            />
                        </div>
                    </div>

                    <div class="mt-8 relative flex justify-center" v-if="games.current_page < games.last_page">
                        <button @click="loadMoreGames" v-if="!isLoadingMore" type="button" class="py-2.5 px-5 me-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 transition-all">
                            {{ $t('Load More') }}
                        </button>
                         <button v-else disabled type="button" class="py-2.5 px-5 me-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 inline-flex items-center">
                            <svg aria-hidden="true" role="status" class="inline w-4 h-4 me-3 text-gray-200 animate-spin dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#1C64F2"/>
                            </svg>
                            {{ $t('Loading...') }}
                        </button>
                    </div>
                </div>
                <div v-else class="empty-data flex flex-col justify-center items-center text-center my-36">
                    <img :src="`/assets/images/no-results.png`" alt="" class="w-auto h-auto max-h-[300px]">
                    <h3>{{ $t('No data to show') }}</h3>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>


<script>

import BaseLayout from "@/Layouts/BaseLayout.vue";
import HttpApi from "@/Services/HttpApi.js";
import CassinoGameCard from "@/Pages/Cassino/Components/CassinoGameCard.vue";
import {useRoute, useRouter} from "vue-router";
import {computed, ref, watch} from "vue";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import HeaderComponent from "@/Components/UI/HeaderComponent.vue";

export default {
    props: [],
    components: {HeaderComponent, LoadingComponent, CassinoGameCard, BaseLayout},
    data() {
        return {
            isLoading: false,
            games: null,
            searchTerm: '',
            provider: null,
            category: null,
            isLoadingMore: false,
        }
    },
    setup(props) {
        const route = useRoute();

        watch(() => route.params.provider, (newProvider, oldProvider) => {

        });

        return {
            route
        };
    },
    computed: {},
    mounted() {},
    beforeUnmount() {},
    methods: {
        searchGames: async function () {
            const _this = this;
            if (_this.searchTerm.length > 2) {
                await _this.getGameData(1,  true);
            }else{
                await _this.getGameData(1,  true);
            }
        },
        getGameData: async function (page = 1, loading = true) {
            const _this = this;
            if (page === 1) {
                _this.isLoading = loading;
            } else {
                _this.isLoadingMore = true;
            }

            const provider = _this.route.params.provider;
            const category = _this.route.params.category;

            this.provider = provider;
            this.category = category;

            await HttpApi.get('/casinos/games?page=' + page + '&searchTerm=' + _this.searchTerm+'&category='+_this.category+'&provider='+_this.provider)
                .then(response => {
                    if (page > 1) {
                        _this.games = {
                            ...response.data.games,
                            data: [..._this.games.data, ...response.data.games.data]
                        };
                    } else {
                        _this.games = response.data.games;
                    }
                    _this.isLoading = false;
                    _this.isLoadingMore = false;
                })
                .catch(error => {
                    _this.isLoading = false;
                    _this.isLoadingMore = false;
                });
        },
        loadMoreGames: async function () {
            const next_page = this.games.current_page + 1;
            await this.getGameData(next_page, false);
        },
    },
   async created() {

        await this.getGameData(1,  false);
    },
    watch: {
        'route.params.provider'(newGame, oldGame) {
            this.getGameData(1,  true);
        },
        'route.params.category'(newGame, oldGame) {
            this.getGameData(1,  true);
        }
    },
};
</script>

<style scoped>

</style>
