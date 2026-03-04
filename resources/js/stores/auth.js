import { defineStore } from "pinia";
import axios from "axios";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: null,
        hydrated: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token, // ✅ IMPORTANT: bazat pe TOKEN
    },

    actions: {
        setUser(user) {
            this.user = user;
            localStorage.setItem("auth_user", JSON.stringify(user));
        },

        setToken(token) {
            this.token = token;
            if (token) {
                localStorage.setItem("auth_token", token);
                axios.defaults.headers.common[
                    "Authorization"
                ] = `Bearer ${token}`;
            } else {
                localStorage.removeItem("auth_token");
                delete axios.defaults.headers.common["Authorization"];
            }
        },

        setAuth(user, token) {
            this.setUser(user);
            this.setToken(token);
        },

        hydrate() {
            const token = localStorage.getItem("auth_token");
            const userRaw = localStorage.getItem("auth_user");

            if (token) this.setToken(token);
            if (userRaw) {
                try {
                    this.user = JSON.parse(userRaw);
                } catch {
                    this.user = null;
                    localStorage.removeItem("auth_user");
                }
            }

            this.hydrated = true;
        },

        logout() {
            this.user = null;
            this.token = null;
            this.hydrated = true;

            localStorage.removeItem("auth_user");
            localStorage.removeItem("auth_token");
            delete axios.defaults.headers.common["Authorization"];
        },
    },
});
