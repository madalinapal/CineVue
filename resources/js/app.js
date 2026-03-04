import { createApp } from "vue";
import App from "./App.vue";
import router from "./router.js";
import { createPinia } from "pinia";
import { useAuthStore } from "@/stores/auth";

const app = createApp(App);

const pinia = createPinia();
app.use(pinia);

const auth = useAuthStore();
auth.hydrate();

app.use(router);
app.mount("#app");
