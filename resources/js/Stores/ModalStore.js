import { defineStore } from 'pinia';

export const useModalStore = defineStore('modalStore', {
    state: () => ({
        modals: {
            deposit: false,
            login: false,
            register: false,
            profile: false
        }
    }),
    actions: {
        toggleModal(modalName, show = true) {
            if (this.modals.hasOwnProperty(modalName)) {
                this.modals[modalName] = show;
            }
        },
        closeAll() {
            Object.keys(this.modals).forEach(key => {
                this.modals[key] = false;
            });
        }
    }
});
