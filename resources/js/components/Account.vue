<template>
    <div class="account">
        <h1>Account</h1>

        <div class="card" v-if="loaded">
            <div class="row">
                <span class="label">Username</span>
                <span class="value">{{ user?.username ?? "-" }}</span>
            </div>
            <div class="row">
                <span class="label">Email</span>
                <span class="value">{{ user?.email ?? "-" }}</span>
            </div>

            <hr />

            <form @submit.prevent="save">
                <div class="grid">
                    <label>
                        Nume
                        <input v-model.trim="form.nume" required />
                    </label>

                    <label>
                        Prenume
                        <input v-model.trim="form.prenume" required />
                    </label>

                    <label>
                        Data nașterii
                        <input type="date" v-model="form.data_nasterii" />
                    </label>

                    <label>
                        Nr. telefon
                        <input v-model.trim="form.nr_telefon" />
                    </label>

                    <label>
                        Email (client)
                        <input
                            type="email"
                            v-model.trim="form.email"
                            required
                        />
                    </label>

                    <label>
                        Tip
                        <select v-model="form.tip">
                            <option :value="null">— Niciun tip —</option>
                            <option value="elev">elev</option>
                            <option value="student">student</option>
                            <option value="pensionar">pensionar</option>
                        </select>
                    </label>
                </div>

                <button class="btn" :disabled="saving">
                    {{ saving ? "Saving..." : "Save changes" }}
                </button>
            </form>

            <hr />

            <h2 class="h2">Biletele mele</h2>

            <div v-if="tickets.length === 0" class="empty">
                Nu ai bilete cumpărate încă.
            </div>

            <table v-else class="t">
                <thead>
                    <tr>
                        <th>Film</th>
                        <th>Sală</th>
                        <th>Dată</th>
                        <th>Ora</th>
                        <th>Loc</th>
                        <th>Preț</th>
                        <th>Cumpărat la</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in tickets" :key="t.id_bilet ?? t.id">
                        <td>{{ t.film ?? "-" }}</td>
                        <td>{{ t.sala ?? "-" }}</td>
                        <td>{{ t.data_proiectie ?? "-" }}</td>
                        <td>{{ formatTime(t.ora_proiectie) }}</td>
                        <td>{{ t.loc_sala ?? "-" }}</td>
                        <td>{{ t.pret ?? "-" }} lei</td>
                        <td>{{ formatDateTime(t.data_cumparare) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else>Loading...</p>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

const router = useRouter();
const auth = useAuthStore();

const userId = computed(() => auth.user?.id);

const loaded = ref(false);
const saving = ref(false);

const user = ref(null);
const tickets = ref([]);

const form = ref({
    nume: "",
    prenume: "",
    data_nasterii: "",
    nr_telefon: "",
    email: "",
    tip: null,
});

const formatTime = (t) => {
    if (!t) return "-";
    return String(t).slice(0, 5);
};

const formatDateTime = (dt) => {
    if (!dt) return "-";
    return String(dt).replace("T", " ").slice(0, 16);
};

const fetchAccount = async () => {
    // dacă nu există token, e clar: nu ești autentificat
    if (!auth.isAuthenticated) {
        router.push("/login");
        return;
    }

    // dacă nu e user încă, așteptăm (se va re-rula la watch)
    if (!userId.value) {
        loaded.value = true;
        return;
    }

    loaded.value = false;

    try {
        const { data } = await axios.get(`/api/account/${userId.value}`);

        user.value = data.user;
        tickets.value = data.tickets ?? [];

        if (data.client) {
            form.value = {
                nume: data.client.nume ?? "",
                prenume: data.client.prenume ?? "",
                data_nasterii: data.client.data_nasterii ?? "",
                nr_telefon: data.client.nr_telefon ?? "",
                email: data.client.email ?? data.user.email,
                tip: data.client.tip ?? null,
            };
        } else {
            form.value = {
                nume: "",
                prenume: "",
                data_nasterii: "",
                nr_telefon: "",
                email: data.user?.email ?? "",
                tip: null,
            };
        }
    } catch (e) {
        // 401 => token invalid/expirat
        if (e.response?.status === 401) {
            auth.logout();
            router.push("/login");
            return;
        }

        console.error(
            "ACCOUNT ERROR:",
            e.response?.status,
            e.response?.data || e.message
        );
        alert(
            e.response?.data?.message || "Nu am putut încărca datele contului."
        );
    } finally {
        loaded.value = true;
    }
};

const save = async () => {
    if (!auth.isAuthenticated || !userId.value) return;

    saving.value = true;
    try {
        await axios.put(`/api/account/${userId.value}`, form.value);
        alert("Account updated.");
        await fetchAccount();
    } catch (e) {
        if (e.response?.data?.errors) {
            alert(Object.values(e.response.data.errors).flat().join("\n"));
        } else {
            alert(e.response?.data?.message || "Eroare la salvare.");
        }
    } finally {
        saving.value = false;
    }
};

watch(
    () => [auth.token, userId.value],
    () => fetchAccount(),
    { immediate: true }
);
</script>

<style scoped>
.account {
    max-width: 980px;
    margin: 60px auto;
    padding: 0 16px;
}
.card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}
.label {
    color: #666;
}
.value {
    font-weight: 600;
    color: #111;
}
.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 12px;
}
label {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 0.95rem;
}
input,
select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
}
.btn {
    margin-top: 16px;
    padding: 10px 16px;
    background: #222;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
hr {
    border: none;
    border-top: 1px solid #f0f0f0;
    margin: 14px 0;
}
.h2 {
    margin: 6px 0 10px;
}
.empty {
    color: #666;
    padding: 10px 0;
}
.t {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6px;
    overflow: hidden;
    border-radius: 10px;
}
.t th,
.t td {
    text-align: left;
    padding: 10px;
    border-bottom: 1px solid #eee;
    font-size: 0.95rem;
}
.t thead th {
    background: #fafafa;
    font-weight: 700;
    color: #333;
}
@media (max-width: 900px) {
    .grid {
        grid-template-columns: 1fr;
    }
    .t {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>
