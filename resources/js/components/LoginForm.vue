<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const auth = useAuthStore();

const email = ref("");
const password = ref("");
const loading = ref(false);
const error = ref("");

const submit = async () => {
    error.value = "";
    loading.value = true;

    try {
        const { data } = await axios.post("/api/login", {
            email: email.value,
            password: password.value,
        });

        const user = data?.user;
        const token = data?.token;

        if (!user?.id || !token) {
            throw new Error("Backend-ul nu a trimis { user, token }.");
        }

        auth.setAuth(user, token);

        router.push("/account");
    } catch (e) {
        error.value =
            e.response?.data?.message ||
            e.message ||
            "Eroare la autentificare.";
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="auth-container">
        <h1>Login</h1>

        <form @submit.prevent="submit">
            <input v-model.trim="email" type="email" placeholder="Email" />
            <input v-model="password" type="password" placeholder="Password" />

            <button :disabled="loading">
                {{ loading ? "Se autentifică..." : "Login" }}
            </button>

            <p v-if="error" style="color: red; margin-top: 10px">
                {{ error }}
            </p>
        </form>
    </div>
</template>

<style scoped>
.auth-container {
    max-width: 400px;
    margin: 100px auto;
    text-align: center;
}
input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
}
button {
    padding: 10px 20px;
    background: #b80000;
    color: white;
    border: none;
    cursor: pointer;
}
button[disabled] {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
