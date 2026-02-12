<template>
    <div class="footer pb-32 md:pb-5 mt-5 glass-box rounded-none border-x-0 border-b-0 p-4 md:p-8">
        <div class="md:w-4/6 2xl:w-4/6 m-auto">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                <div class="col-span-2 md:col-span-1 flex flex-col">
                    <div class="flex justify-between mb-6">
                        <div class="mr-5">
                            <a v-if="setting" href="/" class="flex">
                                <img :src="`/storage/`+setting.software_logo_white" alt=""  class="h-10 block" />
                            </a>
                        </div>
                        <div>
                            <img :src="`/assets/images/+18.png`" width="42" alt="" class="grayscale opacity-50">
                        </div>
                    </div>
                    <div v-if="setting">
                        <div class="text-sm text-gray-500 leading-relaxed">
                            {{ setting.software_description }}
                        </div>

                        <div class="flex gap-4 mt-8">
                            <a href="" class="text-2xl text-gray-600 hover:text-primary transition-colors">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a href="" class="text-2xl text-gray-600 hover:text-primary transition-colors">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="footer-list">
                    <h3 class="text-base md:text-lg text-gray-600 dark:text-white font-bold">{{ $t('USEFUL LINKS') }}</h3>
                    <ul class="list-none mt-5">
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]"><a href="">{{ $t('Payment methods') }}</a></li>
                    </ul>
                </div>
                <div class="footer-list">
                    <h3 class="text-base md:text-lg text-gray-600 dark:text-white font-bold">{{ $t('ABOUT US') }}</h3>
                    <ul class="list-none mt-5">
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]"><a @click="$router.push('/terms/service')" href="">{{ $t('Service Terms') }}</a></li>
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]"><a @click="$router.push('/terms/privacy-policy')" href="">{{ $t('Privacy Policy') }}</a></li>
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]"><a @click="$router.push('/terms/bonus')" href="">{{ $t('Bonus Terms') }}</a></li>
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]"><a @click="$router.push('/terms/bonus-welcome')" href="">{{ $t('Welcome Bonus') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <hr class="border-t border-gray-200 dark:border-gray-600 mt-5">

        <div v-if="setting" class="mt-5 flex flex-col justify-start ">
            <p class="text-[12px] w-full">© {{ year }} {{ state.platformName() }} {{ $t('All rights reserved') }}.</p>
            <p class="flex text-[10px] md:text-[12px]  w-full">
<!--                <strong>❤️ Developed by</strong>-->
<!--                <a href="https://www.instagram.com/venixplataformas/" target="_blank" class="ml-2 dark:text-gray-500 dark:hover:text-white transition duration-700 cursor-pointer">-->
<!--                    Victor Salatiel-->
<!--                </a>-->
            </p>
        </div>
    </div>
</template>

<script>
    import HttpApi from "@/Services/HttpApi.js";
    import {useSettingStore} from "@/Stores/SettingStore.js";

    export default {
        props: [],
        components: {},
        data() {
            return {
                isLoading: false,
                year: new Date().getFullYear(),
                setting: null,
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
            getSetting: function() {
                const _this = this;
                const settingStore = useSettingStore();
                const settingData = settingStore.setting;

                if(settingData) {
                    _this.setting = settingData;
                }
            },
        },
        created() {
            this.getSetting();
        },
        watch: {

        },
    };
</script>

<style scoped>

</style>
