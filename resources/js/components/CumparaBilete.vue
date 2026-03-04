<template>
    <div class="page">
        <div class="wrap">
            <h1>Cumpără bilete</h1>

            <!-- SEARCH + FILTER -->
            <div class="search">
                <input
                    v-model.trim="query"
                    placeholder="Caută film după actor sau titlu..."
                />

                <select v-model="genre">
                    <option value="">Toate genurile</option>
                    <option v-for="g in genreOptions" :key="g" :value="g">
                        {{ g }}
                    </option>
                </select>

                <button
                    class="btn small"
                    type="button"
                    @click="clearSearch"
                    :disabled="!query && !genre"
                >
                    Reset
                </button>
            </div>

            <p class="hint" v-if="(query || genre) && !loading">
                Rezultate pentru:
                <b>{{ query || "—" }}</b>
                <span v-if="genre">
                    • gen: <b>{{ genre }}</b></span
                >
            </p>

            <p class="hint" v-if="loading">Se caută...</p>

            <p class="hint" v-if="!loading && movies.length === 0">
                Nu am găsit filme pentru căutarea introdusă.
            </p>

            <p class="sub">
                Alege un film ca să mergi la pagina de selecție locuri.
            </p>

            <!-- GRID -->
            <div class="grid">
                <button
                    v-for="m in movies"
                    :key="m.id_film"
                    class="card"
                    type="button"
                    @click="goBuy(m)"
                >
                    <img
                        class="poster"
                        :src="m.poster_url || fallbackPoster"
                        :alt="m.titlu"
                        @error="(e) => (e.target.src = fallbackPoster)"
                    />
                    <div class="meta">
                        <div class="title">{{ m.titlu }}</div>
                        <div class="info">
                            {{ getYear(m.data_lansare) }} •
                            {{ formatDuration(m.durata) }}
                        </div>
                        <div class="genre">{{ m.gen }}</div>
                    </div>
                </button>
            </div>

            <div class="actions">
                <router-link class="btn" to="/">Înapoi</router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

// fallback local (dacă un film n-are poster_url)
import fightClub from "../../images/fight-club.jpg";
const fallbackPoster = fightClub;

const router = useRouter();

const query = ref("");
const genre = ref("");

const loading = ref(false);
const movies = ref([]);

// dacă vrei opțiunile dinamic din DB, îți arăt imediat cum.
// momentan listă statică:
const genreOptions = [
    "Drama",
    "Sci-Fi",
    "Thriller",
    "Crima",
    "Actiune",
    "Fantasy",
    "Romance",
    "Animatie",
];

const clearSearch = () => {
    query.value = "";
    genre.value = "";
};

const getYear = (dateStr) => {
    if (!dateStr) return "-";
    return String(dateStr).slice(0, 4);
};

const formatDuration = (minutes) => {
    const m = Number(minutes || 0);
    if (!m) return "-";
    const h = Math.floor(m / 60);
    const mm = m % 60;
    return `${h}h ${mm}m`;
};

const goBuy = (m) => {
    // important: în ProjectionApiController tu cauți după filme.titlu
    router.push({ path: "/tickets", query: { movie: m.titlu } });
};

const fetchMovies = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/movies", {
            params: {
                q: query.value.trim(),
                genre: genre.value || "",
            },
        });

        movies.value = data.movies ?? [];
    } catch (e) {
        console.error(
            "MOVIES ERROR:",
            e.response?.status,
            e.response?.data || e.message
        );
        movies.value = [];
    } finally {
        loading.value = false;
    }
};

// debounce
let timer = null;
watch(
    [query, genre],
    () => {
        clearTimeout(timer);
        timer = setTimeout(fetchMovies, 250);
    },
    { immediate: true }
);
</script>

<style scoped>
.search {
    display: flex;
    gap: 10px;
    align-items: center;
    margin: 12px 0 18px;
}

.search input,
.search select {
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(0, 0, 0, 0.25);
    color: #fff;
}

.search input {
    flex: 1;
}

.btn.small {
    padding: 10px 12px;
    border-radius: 12px;
}

.hint {
    margin: 0 0 8px;
    color: rgba(255, 255, 255, 0.7);
}

.page {
    min-height: 100vh;
    background: #0b0b0b;
    color: #fff;
}
.wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 36px 18px 80px;
}
h1 {
    margin: 0 0 6px;
    font-size: 2rem;
}
.sub {
    margin: 0 0 18px;
    color: rgba(255, 255, 255, 0.7);
}

.grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.card {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    overflow: hidden;
    padding: 0;
    cursor: pointer;
    text-align: left;
    color: inherit;
}

.poster {
    width: 100%;
    height: 260px;
    object-fit: cover;
    display: block;
}
.meta {
    padding: 10px;
}
.title {
    font-weight: 800;
}
.info {
    color: rgba(255, 255, 255, 0.7);
    margin-top: 4px;
    font-size: 0.95rem;
}
.genre {
    margin-top: 6px;
    font-size: 0.9rem;
    opacity: 0.8;
}

.actions {
    margin-top: 16px;
}
.btn {
    display: inline-block;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.06);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
}

@media (max-width: 900px) {
    .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
