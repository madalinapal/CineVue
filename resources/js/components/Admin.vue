<template>
    <div class="page">
        <div class="wrap">
            <h1>Admin</h1>
            <p class="sub">Panou administrare: filme + proiecții</p>

            <div class="grid2">
                <!-- FORM -->
                <div class="card" ref="formCard">
                    <div class="cardHead">
                        <h2>
                            {{ editingId ? "Editează film" : "Adaugă film" }}
                        </h2>

                        <button
                            v-if="editingId"
                            class="btn ghost"
                            type="button"
                            @click="cancelEdit"
                        >
                            Renunță
                        </button>
                    </div>

                    <form class="form" @submit.prevent="submit">
                        <div class="row2">
                            <div class="field">
                                <label>Titlu</label>
                                <input
                                    v-model.trim="form.titlu"
                                    placeholder="ex: The Pianist"
                                />
                            </div>

                            <div class="field">
                                <label>Gen</label>
                                <input
                                    v-model.trim="form.gen"
                                    placeholder="ex: Drama"
                                />
                            </div>
                        </div>

                        <div class="row2">
                            <div class="field">
                                <label>Durată (minute)</label>
                                <input
                                    v-model.number="form.durata"
                                    type="number"
                                    min="1"
                                />
                            </div>

                            <div class="field">
                                <label>Data lansare</label>
                                <input
                                    v-model="form.data_lansare"
                                    type="date"
                                />
                            </div>
                        </div>

                        <div class="row2">
                            <div class="field">
                                <label>Director</label>
                                <input v-model.trim="form.director" />
                            </div>

                            <div class="field">
                                <label>Regizor</label>
                                <input v-model.trim="form.regizor" />
                            </div>
                        </div>

                        <div class="sep"></div>

                        <!-- Poster -->
                        <div class="posterBox">
                            <div class="posterLeft">
                                <label class="posterLabel">
                                    Poster
                                    <span class="muted">
                                        ({{
                                            editingId
                                                ? "opțional la edit"
                                                : "obligatoriu la creare"
                                        }})
                                    </span>
                                </label>

                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="onPosterChange"
                                    :required="!editingId"
                                />

                                <p class="hint" v-if="posterFile">
                                    Selectat: <b>{{ posterFile.name }}</b>
                                </p>

                                <p
                                    class="hint"
                                    v-else-if="editingId && currentPosterUrl"
                                >
                                    Poster curent: <b>existent</b>
                                </p>
                            </div>

                            <div
                                class="posterRight"
                                v-if="posterPreviewUrl || currentPosterUrl"
                            >
                                <img
                                    class="posterPreview"
                                    :src="posterPreviewUrl || currentPosterUrl"
                                    alt="Poster preview"
                                />
                            </div>
                        </div>

                        <div class="sep"></div>

                        <!-- Proiecții -->
                        <div class="projHead">
                            <h3>Proiecții</h3>
                            <button
                                class="btn small"
                                type="button"
                                @click="addProjection"
                            >
                                + Adaugă proiecție
                            </button>
                        </div>

                        <p class="hint" v-if="saliLoading">
                            Se încarcă sălile...
                        </p>
                        <p class="hint err" v-if="saliError">{{ saliError }}</p>

                        <div
                            v-if="form.projections.length === 0"
                            class="hint"
                            style="margin-top: 6px"
                        >
                            Nu ai adăugat proiecții (poți salva filmul și fără
                            proiecții).
                        </div>

                        <div
                            v-for="(p, idx) in form.projections"
                            :key="idx"
                            class="projRow"
                        >
                            <div class="field">
                                <label>Sală</label>
                                <select v-model.number="p.id_sala">
                                    <option :value="null" disabled>
                                        — Selectează —
                                    </option>
                                    <option
                                        v-for="s in sali"
                                        :key="s.id_sala"
                                        :value="s.id_sala"
                                    >
                                        {{ s.nume }} ({{ s.capacitate }})
                                    </option>
                                </select>
                            </div>

                            <div class="field">
                                <label>Data</label>
                                <input v-model="p.data" type="date" />
                            </div>

                            <div class="field">
                                <label>Ora</label>
                                <input v-model="p.ora" type="time" />
                            </div>

                            <button
                                class="btn danger small"
                                type="button"
                                @click="removeProjection(idx)"
                                title="Șterge proiecția"
                            >
                                ✕
                            </button>
                        </div>

                        <div class="sep"></div>

                        <div class="actions">
                            <button class="btn" :disabled="saving">
                                {{
                                    saving
                                        ? "Se salvează..."
                                        : editingId
                                        ? "Salvează modificările"
                                        : "Creează film"
                                }}
                            </button>

                            <button
                                class="btn ghost"
                                type="button"
                                @click="resetForm"
                                :disabled="saving"
                            >
                                Reset form
                            </button>
                        </div>

                        <p class="hint ok" v-if="successMsg">
                            {{ successMsg }}
                        </p>
                        <p class="hint err" v-if="errorMsg">{{ errorMsg }}</p>
                    </form>
                </div>

                <!-- LIST -->
                <div
                    class="card listCard"
                    ref="listCard"
                    :style="{ height: listCardHeight }"
                >
                    <div class="cardHead">
                        <h2>Filme</h2>
                        <button class="btn ghost" type="button" @click="reload">
                            Reîncarcă
                        </button>
                    </div>

                    <div class="toolbar">
                        <input
                            v-model.trim="filter"
                            placeholder="Caută după titlu / gen..."
                        />
                    </div>

                    <p class="hint" v-if="loadingMovies">
                        Se încarcă filmele...
                    </p>
                    <p class="hint err" v-if="moviesError">{{ moviesError }}</p>

                    <div
                        v-if="!loadingMovies && filteredMovies.length === 0"
                        class="hint"
                    >
                        Nu există filme (sau nu se potrivesc filtrului).
                    </div>

                    <div class="movieList scrollBox">
                        <div
                            v-for="m in filteredMovies"
                            :key="m.id_film"
                            class="movieItem"
                        >
                            <div class="moviePoster">
                                <img
                                    v-if="moviePosterUrl(m)"
                                    :src="moviePosterUrl(m)"
                                    alt="poster"
                                />
                                <div v-else class="noPoster">No poster</div>
                            </div>

                            <div class="movieMeta">
                                <div class="movieTitle">
                                    {{ m.titlu }}
                                    <span class="badge">{{ m.gen }}</span>
                                </div>

                                <div class="movieInfo">
                                    {{ m.durata }} min •
                                    {{
                                        String(m.data_lansare || "").slice(
                                            0,
                                            10
                                        )
                                    }}
                                </div>

                                <div class="movieInfo">
                                    Director: {{ m.director }} • Regizor:
                                    {{ m.regizor }}
                                </div>
                            </div>

                            <div class="movieBtns">
                                <button
                                    class="btn small"
                                    type="button"
                                    @click="startEdit(m)"
                                >
                                    Edit
                                </button>
                                <button
                                    class="btn danger small"
                                    type="button"
                                    @click="del(m)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref, nextTick } from "vue";
import axios from "axios";

/** refs pentru sincronizare înălțime */
const formCard = ref(null);
const listCard = ref(null);
const listCardHeight = ref("auto");

let ro = null;

const syncHeights = async () => {
    await nextTick();
    const h = formCard.value?.getBoundingClientRect?.().height;
    if (h && h > 0) listCardHeight.value = `${Math.round(h)}px`;
};

/** movies list state */
const movies = ref([]);
const loadingMovies = ref(false);
const moviesError = ref("");
const filter = ref("");

/** sali state */
const sali = ref([]);
const saliLoading = ref(false);
const saliError = ref("");

/** form state */
const saving = ref(false);
const errorMsg = ref("");
const successMsg = ref("");
const editingId = ref(null);

/** poster state */
const posterFile = ref(null);
const posterPreviewUrl = ref(""); // blob preview
const currentPosterUrl = ref(""); // când editezi un film existent

const emptyForm = () => ({
    titlu: "",
    durata: 120,
    gen: "",
    data_lansare: "",
    director: "",
    regizor: "",
    projections: [], // { id_sala, data, ora }
});

const form = ref(emptyForm());

const onPosterChange = (e) => {
    const f = e.target.files?.[0] ?? null;
    posterFile.value = f;

    // preview
    if (posterPreviewUrl.value) URL.revokeObjectURL(posterPreviewUrl.value);
    posterPreviewUrl.value = f ? URL.createObjectURL(f) : "";

    syncHeights();
};

const moviePosterUrl = (m) => {
    const p = m?.poster_path;
    if (!p) return "";
    // presupunând că ai `php artisan storage:link`
    return `/storage/${p}`;
};

const filteredMovies = computed(() => {
    const q = filter.value.toLowerCase().trim();
    if (!q) return movies.value;
    return movies.value.filter((m) => {
        const t = String(m.titlu || "").toLowerCase();
        const g = String(m.gen || "").toLowerCase();
        return t.includes(q) || g.includes(q);
    });
});

const reload = async () => {
    moviesError.value = "";
    loadingMovies.value = true;
    try {
        const { data } = await axios.get("/api/admin/movies");
        movies.value = data.movies ?? [];
    } catch (e) {
        console.error(e);
        moviesError.value =
            e.response?.data?.message || "Nu am putut încărca filmele.";
    } finally {
        loadingMovies.value = false;
        syncHeights();
    }
};

const loadSali = async () => {
    saliError.value = "";
    saliLoading.value = true;
    try {
        const { data } = await axios.get("/api/admin/sali");
        sali.value = data.sali ?? [];
    } catch (e) {
        console.error(e);
        saliError.value =
            e.response?.data?.message || "Nu am putut încărca sălile.";
    } finally {
        saliLoading.value = false;
        syncHeights();
    }
};

const resetPoster = () => {
    posterFile.value = null;
    if (posterPreviewUrl.value) URL.revokeObjectURL(posterPreviewUrl.value);
    posterPreviewUrl.value = "";
};

const resetForm = () => {
    form.value = emptyForm();
    errorMsg.value = "";
    successMsg.value = "";
    currentPosterUrl.value = "";
    resetPoster();
    syncHeights();
};

const cancelEdit = () => {
    editingId.value = null;
    resetForm();
    syncHeights();
};

const addProjection = () => {
    form.value.projections.push({
        id_sala: sali.value?.[0]?.id_sala ?? null,
        data: "",
        ora: "",
    });
    syncHeights();
};

const removeProjection = (idx) => {
    form.value.projections.splice(idx, 1);
    syncHeights();
};

const startEdit = async (m) => {
    errorMsg.value = "";
    successMsg.value = "";
    editingId.value = m.id_film;

    // setează film
    form.value = {
        titlu: m.titlu ?? "",
        durata: Number(m.durata ?? 120),
        gen: m.gen ?? "",
        data_lansare: String(m.data_lansare ?? "").slice(0, 10),
        director: m.director ?? "",
        regizor: m.regizor ?? "",
        projections: [],
    };

    // poster curent
    currentPosterUrl.value = moviePosterUrl(m);
    resetPoster();

    // projections din API
    try {
        const { data } = await axios.get(
            `/api/admin/movies/${m.id_film}/projections`
        );
        const rows = data.projections ?? [];
        form.value.projections = rows.map((p) => ({
            id_sala: Number(p.id_sala),
            data: String(p.data_proiectie ?? "").slice(0, 10),
            ora: String(p.ora_proiectie ?? "").slice(0, 5),
        }));
    } catch (e) {
        console.error(e);
        errorMsg.value =
            e.response?.data?.message ||
            "Nu am putut încărca proiecțiile pentru acest film.";
    } finally {
        await syncHeights();
    }
};

const submit = async () => {
    errorMsg.value = "";
    successMsg.value = "";
    saving.value = true;

    try {
        // poster obligatoriu doar la CREATE
        if (!editingId.value && !posterFile.value) {
            errorMsg.value = "Posterul este obligatoriu la crearea filmului.";
            return;
        }

        const fd = new FormData();
        fd.append("titlu", form.value.titlu);
        fd.append("durata", String(form.value.durata));
        fd.append("gen", form.value.gen);
        fd.append("data_lansare", form.value.data_lansare);
        fd.append("director", form.value.director);
        fd.append("regizor", form.value.regizor);

        // proiecții ca JSON string (ușor de citit în Laravel)
        fd.append("projections", JSON.stringify(form.value.projections ?? []));

        // poster doar dacă e selectat
        if (posterFile.value) fd.append("poster", posterFile.value);

        if (editingId.value) {
            // multipart + PUT -> folosim _method
            fd.append("_method", "PUT");
            await axios.post(`/api/admin/movies/${editingId.value}`, fd, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            successMsg.value = "Filmul a fost actualizat.";
        } else {
            await axios.post("/api/admin/movies", fd, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            successMsg.value = "Filmul a fost creat.";
        }

        await reload();
        cancelEdit();
    } catch (e) {
        console.error(e);
        if (e.response?.status === 422 && e.response?.data?.errors) {
            const firstKey = Object.keys(e.response.data.errors)[0];
            errorMsg.value =
                e.response.data.errors[firstKey]?.[0] ?? "Date invalide.";
            console.log("422 DATA:", e.response?.data);
            console.log("422 ERRORS:", e.response?.data?.errors);
        } else {
            errorMsg.value =
                e.response?.data?.message || e.message || "Eroare la salvare.";
        }
    } finally {
        saving.value = false;
        syncHeights();
    }
};

const del = async (m) => {
    const ok = confirm(`Ștergi filmul "${m.titlu}"? (Se șterg și proiecțiile)`);
    if (!ok) return;

    try {
        await axios.delete(`/api/admin/movies/${m.id_film}`);
        await reload();
        if (editingId.value === m.id_film) cancelEdit();
    } catch (e) {
        console.error(e);
        alert(e.response?.data?.message || "Nu am putut șterge filmul.");
    } finally {
        syncHeights();
    }
};

onMounted(async () => {
    await Promise.all([reload(), loadSali()]);
    await syncHeights();

    ro = new ResizeObserver(() => syncHeights());
    if (formCard.value) ro.observe(formCard.value);

    window.addEventListener("resize", syncHeights);
});

onBeforeUnmount(() => {
    if (ro && formCard.value) ro.unobserve(formCard.value);
    if (ro) ro.disconnect();
    window.removeEventListener("resize", syncHeights);
});
</script>

<style scoped>
*,
*::before,
*::after {
    box-sizing: border-box;
}

.page {
    min-height: 100vh;
    background: #0b0b0b;
    color: #fff;
}

.wrap {
    max-width: 1250px;
    margin: 0 auto;
    padding: 34px 18px 80px;
}

h1 {
    margin: 0 0 6px;
    font-size: 2.2rem;
}

.sub {
    margin: 0 0 18px;
    color: rgba(255, 255, 255, 0.7);
}

.grid2 {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 16px;
    align-items: start;
}

.card {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 14px;
    overflow: hidden;
    min-width: 0;
}

/* Card listă = container flex cu scroll în interior */
.listCard {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
}

/* lista devine scrollabilă în interiorul cardului */
.scrollBox {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding-right: 6px;
}

/* optional: scrollbar discret */
.scrollBox::-webkit-scrollbar {
    width: 8px;
}
.scrollBox::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 999px;
}
.scrollBox::-webkit-scrollbar-track {
    background: transparent;
}

.cardHead {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
}

.cardHead h2 {
    margin: 0;
    font-size: 1.2rem;
}

.form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.field label {
    display: block;
    font-size: 0.9rem;
    margin: 0 0 6px;
    color: rgba(255, 255, 255, 0.75);
}

.field input,
.field select,
.toolbar input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(0, 0, 0, 0.25);
    color: #fff;
    outline: none;
}

.sep {
    height: 1px;
    background: rgba(255, 255, 255, 0.12);
    margin: 4px 0;
}

.posterBox {
    display: grid;
    grid-template-columns: 1fr 130px;
    gap: 12px;
    align-items: start;
}

.posterLabel {
    display: block;
    font-size: 0.9rem;
    margin-bottom: 6px;
    color: rgba(255, 255, 255, 0.75);
}

.muted {
    color: rgba(255, 255, 255, 0.55);
    font-weight: 600;
    margin-left: 6px;
    font-size: 0.85rem;
}

.posterPreview {
    width: 130px;
    height: 170px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.projHead {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.projHead h3 {
    margin: 0;
    font-size: 1.05rem;
}

.projRow {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr auto;
    gap: 10px;
    align-items: end;
    padding: 10px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.04);
}

.actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    cursor: pointer;
    font-weight: 700;
}

.btn.small {
    padding: 8px 10px;
    border-radius: 10px;
    font-weight: 700;
}

.btn.ghost {
    background: transparent;
}

.btn.danger {
    border-color: rgba(255, 80, 80, 0.35);
    background: rgba(255, 80, 80, 0.12);
}

.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.hint {
    margin: 0;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.95rem;
}

.hint.err {
    color: #ff6b6b;
}

.hint.ok {
    color: #45d483;
}

.toolbar {
    margin-bottom: 12px;
}

.movieList {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.movieItem {
    display: grid;
    grid-template-columns: 62px 1fr auto;
    gap: 12px;
    padding: 12px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.04);
    align-items: center;
}

.moviePoster img {
    width: 62px;
    height: 82px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.noPoster {
    width: 62px;
    height: 82px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 1px dashed rgba(255, 255, 255, 0.18);
    color: rgba(255, 255, 255, 0.55);
    font-size: 0.8rem;
    text-align: center;
    padding: 6px;
}

.movieTitle {
    font-weight: 800;
}

.movieInfo {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.92rem;
    margin-top: 3px;
}

.badge {
    display: inline-block;
    margin-left: 8px;
    padding: 2px 8px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.8rem;
    font-weight: 700;
}

.movieBtns {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 92px;
    justify-content: center;
}

.note {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.95rem;
}

@media (max-width: 980px) {
    .grid2 {
        grid-template-columns: 1fr;
    }
    .posterBox {
        grid-template-columns: 1fr;
    }
    /* pe mobil nu forțăm înălțimi egale */
    .listCard {
        height: auto !important;
    }
}

@media (max-width: 650px) {
    .row2 {
        grid-template-columns: 1fr;
    }
    .projRow {
        grid-template-columns: 1fr 1fr;
        grid-auto-rows: auto;
    }
    .projRow .btn.danger {
        grid-column: 1 / -1;
    }
    .movieItem {
        grid-template-columns: 62px 1fr;
    }
    .movieBtns {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: flex-end;
    }
}
</style>
