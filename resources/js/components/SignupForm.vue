<template>
    <div class="auth-container">
        <h2>Sign Up</h2>
        <form @submit.prevent="signup">
            <input
                type="text"
                v-model.trim="username"
                placeholder="Username"
                required
            />
            <input
                type="email"
                v-model.trim="email"
                placeholder="Email"
                required
            />
            <input
                type="password"
                v-model="password"
                placeholder="Password"
                required
            />
            <button type="submit" :disabled="loading">
                {{ loading ? "Creating..." : "Create Account" }}
            </button>
        </form>
        <p>
            Already have an account?
            <router-link to="/login">Login</router-link>
        </p>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();
const username = ref("");
const email = ref("");
const password = ref("");
const loading = ref(false);

const signup = async () => {
    loading.value = true;
    try {
        await axios.post("/api/register", {
            username: username.value,
            email: email.value,
            password: password.value,
        });
        router.push("/login");
    } catch (error) {
        if (error.response?.data?.errors) {
            alert(Object.values(error.response.data.errors).flat().join("\n"));
        } else {
            alert(error.response?.data?.message || "Registration failed.");
        }
    } finally {
        loading.value = false;
    }
};
</script>

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
