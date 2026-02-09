<template>
    <RouterLink :to="getRouterLink()">
        <div class="flex text-gray-700 w-full h-auto cursor-pointer relative overflow-hidden rounded-xl border border-white/5 transition-all duration-300 hover:border-primary/50" @mouseover="showGameInfo = true" @mouseleave="showGameInfo = false">
            <div class="relative w-full aspect-[3/4]">
                <RouterLink :to="getRouterLink()" class="block w-full h-full">
                    <img v-if="game.distribution === 'kagaming'" :src="game.cover" alt="" class="w-full h-full object-cover transition-transform duration-500" :class="{ 'scale-110 blur-sm': showGameInfo }">
                    <img v-else :src="/storage/+game.cover" alt="" class="w-full h-full object-cover transition-transform duration-500" :class="{ 'scale-110 blur-sm': showGameInfo }">
                </RouterLink>
                
                <div v-if="showGameInfo" class="absolute inset-0 flex flex-col justify-center items-center bg-black/40 backdrop-blur-sm p-4 transition-opacity duration-300">
                    <div class="text-center text-white w-full">
                        <span class="block font-bold text-sm mb-1 drop-shadow-md">{{ game.game_name }}</span>
                        <small class="block text-xs text-gray-300 mb-3">{{ game?.provider?.name }}</small>
                        <button type="button" class="flex items-center justify-center gap-2 bg-primary hover:bg-primary/80 transition-colors w-full py-2 rounded-lg font-bold text-xs uppercase tracking-wider shadow-lg shadow-primary/20">
                            <i class="fas fa-play-circle text-lg"></i>
                            {{ $t('Play') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </RouterLink>
</template>


<script>
import { RouterLink } from "vue-router";

export default {
    props: [ 'index', 'game'],
    components: { RouterLink },
    data() {
        return {
            isLoading: false,
            modalGame: null,
            showGameInfo: false // Adicionando a variável showGameInfo
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
        getRouterLink() {
            if (this.game.distribution === 'kagaming') {
                return { name: 'casinoPlayPage', params: { id: this.game.id, slug: this.game.game_code }};
            } else {
                return { name: 'casinoPlayPage', params: { id: this.game.id, slug: this.game.game_code }};
            }
        }
    }
};
</script>

<style scoped>

</style>
