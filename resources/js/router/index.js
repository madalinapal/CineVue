import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import LoginForm from "@/components/LoginForm.vue";
import SignupForm from "@/components/SignupForm.vue";
const Dashboard = () => import("@/components/Dashboard.vue");
const Account = () => import("@/components/Account.vue"); // 👈 nou

const routes = [
    { path: "/", redirect: "/login" },
    { path: "/login", component: LoginForm },
    { path: "/signup", component: SignupForm },
    { path: "/dashboard", component: Dashboard, meta: { requiresAuth: true } },
    { path: "/account", component: Account, meta: { requiresAuth: true } }, // 👈 nou
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to) => {
    const auth = useAuthStore();
    if (to.meta.requiresAuth && !auth.isAuthenticated)
        return { path: "/login" };
    if (
        (to.path === "/login" || to.path === "/signup") &&
        auth.isAuthenticated
    ) {
        return { path: "/dashboard" };
    }
});

export default router;
