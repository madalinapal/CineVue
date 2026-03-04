import { createRouter, createWebHistory } from "vue-router";

import Homepage from "@/components/Homepage.vue";
import Account from "@/components/Account.vue";
import LoginForm from "@/components/LoginForm.vue";
import SignupForm from "@/components/SignupForm.vue";
import BuyTicket from "@/components/BuyTicket.vue";
import CumparaBilete from "@/components/CumparaBilete.vue";
import Admin from "@/components/Admin.vue";

import { useAuthStore } from "@/stores/auth";

const routes = [
    { path: "/", component: Homepage },
    { path: "/login", component: LoginForm, meta: { guestOnly: true } },
    { path: "/signup", component: SignupForm, meta: { guestOnly: true } },

    { path: "/account", component: Account, meta: { requiresAuth: true } },
    { path: "/tickets", component: BuyTicket, meta: { requiresAuth: true } },

    { path: "/cumpara-bilete", component: CumparaBilete },
    { path: "/admin", component: Admin, meta: { requiresAdmin: true } },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to) => {
    const auth = useAuthStore();

    if (to.meta.requiresAdmin) {
        if (!auth.isAuthenticated) return "/login";
        if (auth.user?.role !== "admin") return "/"; // sau 403 page
    }
});

// Guard: dacă nu ai token => login; dacă ești logat și mergi la login => account
router.beforeEach((to) => {
    const auth = useAuthStore();

    // dacă nu s-a hidratat încă, îl lăsăm să se hydrateze din app.js (deja se face)
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { path: "/login" };
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return { path: "/account" };
    }
});

export default router;
