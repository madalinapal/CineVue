<template>
    <nav class="navbar">
        <div class="left">
            <router-link to="/" class="logo-link">
                <img :src="logo" alt="CineVue Logo" class="logo" />
            </router-link>
        </div>

        <ul class="nav-links">
            <li><router-link to="/">Acasă</router-link></li>

            <li>
                <router-link to="/cumpara-bilete">Cumpără bilete</router-link>
            </li>

            <!-- LOGGED OUT -->
            <template v-if="!auth.isAuthenticated">
                <li><router-link to="/login">Login</router-link></li>
                <li><router-link to="/signup">Sign Up</router-link></li>
            </template>

            <!-- LOGGED IN -->
            <template v-else>
                <li><router-link to="/account">Cont</router-link></li>

                <li v-if="auth.user?.role === 'admin'">
                    <router-link to="/admin">Admin</router-link>
                </li>

                <li><a href="#" @click.prevent="logout">Logout</a></li>
            </template>
        </ul>
    </nav>
</template>

<script setup>
import logo from "../../images/logo2.png";
import axios from "axios";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const auth = useAuthStore();

const logout = async () => {
    try {
        await axios.post("/api/logout");
    } catch (_) {}

    auth.logout();
    router.push("/login");
};
</script>

<style scoped>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 120px;
    padding: 0 100px;
    background-color: #efefee;
    color: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
}
.logo {
    height: 70px;
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}
.logo:hover {
    transform: scale(1.08);
}
.nav-links {
    list-style: none;
    display: flex;
    gap: 40px;
    align-items: center;
    margin: 0;
    padding: 0;
}
.nav-links a {
    color: black;
    font-size: 1.2rem;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.2s ease;
}
.nav-links a:hover {
    color: #ff3b3b;
}
</style>
