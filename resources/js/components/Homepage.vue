<template>
    <div class="page">
        <div class="wrap">
            <!-- HERO -->
            <section class="hero" v-if="featured">
                <div
                    class="hero-bg"
                    :style="{ backgroundImage: `url(${featured.poster})` }"
                ></div>

                <div class="hero-content">
                    <div class="pill">CineSpot • Acum în program</div>

                    <h1 class="hero-title">Bine ai venit la CineSpot 🎬</h1>
                    <p class="hero-sub">
                        Alege filmul preferat, vezi programul și cumpără bilete
                        rapid — direct din aplicație.
                    </p>

                    <div class="featuredMeta top">
                        <div class="featuredTitle">{{ featured.title }}</div>

                        <div class="featuredSub">
                            <span>{{ featured.year }}</span>
                            <span>•</span>
                            <span>{{ featured.genre }}</span>
                            <span>•</span>
                            <span>{{ featured.duration }}</span>
                        </div>

                        <div class="featuredTagline" v-if="featured.tagline">
                            {{ featured.tagline }}
                        </div>
                    </div>

                    <div class="hero-row">
                        <!-- ACTIONS -->
                        <div class="actionsCol">
                            <button
                                class="btn primary big"
                                @click="router.push('/cumpara-bilete')"
                            >
                                Cumpără bilete
                            </button>

                            <button
                                class="btn ghost big"
                                @click="setFeatured(featured)"
                            >
                                Vezi detalii
                            </button>

                            <div class="miniHint">
                                Sugestie: selectează un film din carusel pentru
                                detalii.
                            </div>
                        </div>

                        <!-- TODAY PROGRAM -->
                        <div class="todayCard">
                            <div class="todayHead">
                                <div>
                                    <div class="todayTitle">
                                        Programul de azi
                                    </div>
                                    <div class="todaySub">{{ todayLabel }}</div>
                                </div>
                            </div>

                            <div v-if="todayLoading" class="emptyToday">
                                Se încarcă programul...
                            </div>
                            <div
                                v-else-if="todayError"
                                class="emptyToday"
                                style="color: #ff6b6b"
                            >
                                {{ todayError }}
                            </div>

                            <div v-else class="todayList">
                                <button
                                    v-for="p in todayProgram"
                                    :key="p.id_proiectie"
                                    class="todayItem"
                                    type="button"
                                    @click="goToProjection(p)"
                                >
                                    <div class="left">
                                        <div class="time">{{ p.ora }}</div>
                                        <div class="film">{{ p.titlu }}</div>
                                        <div class="hall">
                                            Sala: {{ p.sala }}
                                        </div>
                                    </div>

                                    <div class="right">
                                        <div class="seats">
                                            <b>{{ p.locuri_libere }}</b> /
                                            {{ p.capacitate }} libere
                                        </div>

                                        <div class="occ">
                                            ocupare:
                                            <b>{{ p.ocupare_proc }}%</b>
                                        </div>

                                        <div class="bar">
                                            <div
                                                class="barFill"
                                                :style="{
                                                    width: p.ocupare_proc + '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <div
                                v-if="
                                    !todayLoading &&
                                    !todayError &&
                                    todayProgram.length === 0
                                "
                                class="emptyToday"
                            >
                                Nu există proiecții azi.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero-poster">
                    <img :src="featured.poster" :alt="featured.title" />
                </div>
            </section>

            <!-- NOW PLAYING -->
            <section class="section">
                <div class="section-head">
                    <h2>ACUM ÎN PROGRAM</h2>
                </div>

                <div class="carousel">
                    <button
                        class="nav left"
                        @click="scrollPrev"
                        aria-label="left"
                    >
                        ‹
                    </button>

                    <div class="rail" ref="railRef">
                        <button
                            v-for="m in movies"
                            :key="m.title"
                            class="movie"
                            @click="setFeatured(m)"
                            :title="m.title"
                        >
                            <div class="poster">
                                <img
                                    :src="m.poster"
                                    :alt="m.title"
                                    loading="lazy"
                                />
                                <div class="overlay">
                                    <div class="overlay-title">
                                        {{ m.title }}
                                    </div>
                                    <div class="overlay-sub">
                                        {{ m.year }} • {{ m.duration }}
                                    </div>
                                </div>
                            </div>
                            <div class="title">{{ m.title }}</div>
                        </button>
                    </div>

                    <button
                        class="nav right"
                        @click="scrollNext"
                        aria-label="right"
                    >
                        ›
                    </button>
                </div>
            </section>

            <!-- STATS -->
            <section class="section">
                <h2>Statistici</h2>

                <div class="grid">
                    <!-- Card 1 -->
                    <div class="info-card">
                        <h3>Top 5 filme</h3>
                        <p>
                            Top 5 filme după numărul de bilete vândute, cu
                            categoria de client majoritară pentru fiecare film.
                        </p>

                        <div v-if="statsLoading">Se încarcă...</div>
                        <div v-else-if="statsError" style="color: #ff6b6b">
                            {{ statsError }}
                        </div>

                        <ul v-else style="margin: 0; padding-left: 18px">
                            <li v-for="m in topMovies" :key="m.id_film">
                                <b>{{ m.titlu }}</b> —
                                {{ m.total_bilete }} bilete, majoritar:
                                <b>{{ m.categorie_majoritara }}</b>
                            </li>
                        </ul>
                    </div>

                    <!-- Card 2 -->
                    <div class="info-card">
                        <h3>Distribuție bilete pe tip client</h3>

                        <div v-if="typeLoading">Se încarcă...</div>
                        <div v-else-if="typeError" style="color: #ff6b6b">
                            {{ typeError }}
                        </div>

                        <div v-else>
                            <p style="margin: 0 0 8px; opacity: 0.8">
                                Total bilete: <b>{{ typeTotal }}</b>
                            </p>
                            <ul style="margin: 0; padding-left: 18px">
                                <li v-for="x in typeStats" :key="x.tip">
                                    <b>{{ x.tip }}</b> —
                                    {{ x.total_bilete }} ({{ x.procent }}%)
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="info-card">
                        <h3>Top 3 săli după ocupare</h3>

                        <div class="rowDates">
                            <input type="date" v-model="hallStart" />
                            <input type="date" v-model="hallEnd" />
                            <button class="miniBtn" @click="loadHallStats">
                                Aplică
                            </button>
                        </div>

                        <div v-if="hallLoading">Se încarcă...</div>
                        <div v-else-if="hallError" style="color: #ff6b6b">
                            {{ hallError }}
                        </div>

                        <ul v-else style="margin: 0; padding-left: 18px">
                            <li v-for="s in topHalls" :key="s.id_sala">
                                <b>{{ s.nume }}</b> — {{ s.ocupare_proc }}% ({{
                                    s.vandute
                                }}/{{ s.locuri_total }}) •
                                {{ s.nr_proiectii }} proiecții
                            </li>
                        </ul>

                        <p
                            style="
                                margin-top: 8px;
                                opacity: 0.75;
                                font-size: 0.9rem;
                            "
                        >
                            Perioadă: <b>{{ hallStart }}</b> –
                            <b>{{ hallEnd }}</b>
                        </p>
                    </div>

                    <!-- Card 4 -->
                    <div class="info-card">
                        <h3>Proiecții cu ocupare &gt; 80%</h3>

                        <div v-if="occLoading">Se încarcă...</div>
                        <div v-else-if="occError" style="color: #ff6b6b">
                            {{ occError }}
                        </div>

                        <ul v-else style="margin: 0; padding-left: 18px">
                            <li
                                v-for="p in occProjections"
                                :key="p.id_proiectie"
                            >
                                <b>{{ p.titlu }}</b> — {{ p.sala }}:
                                <b>{{ p.ocupare_proc }}%</b>
                                ({{ p.bilete_vandute }}/{{ p.capacitate }}) •
                                {{ String(p.data_proiectie).slice(0, 10) }}
                                {{ String(p.ora_proiectie).slice(0, 5) }}
                            </li>
                        </ul>
                    </div>

                    <!-- Card 5 -->
                    <div class="info-card">
                        <h3>Top 5 utilizatori</h3>

                        <div v-if="usersLoading">Se încarcă...</div>
                        <div v-else-if="usersError" style="color: #ff6b6b">
                            {{ usersError }}
                        </div>

                        <ul v-else style="margin: 0; padding-left: 18px">
                            <li v-for="u in topUsers" :key="u.user_id">
                                <b>{{ u.username }}</b> —
                                {{ u.total_bilete }} bilete, încasări:
                                <b>{{ u.incasari ?? 0 }}</b>
                            </li>
                        </ul>
                    </div>

                    <!-- Card 6 -->
                    <div class="info-card">
                        <h3>Filme sold out</h3>

                        <div class="rowDates">
                            <input type="date" v-model="soldFrom" />
                            <input type="date" v-model="soldTo" />
                            <button class="miniBtn" @click="loadSoldout">
                                Aplică
                            </button>
                        </div>

                        <div v-if="soldoutLoading">Se încarcă...</div>
                        <div v-else-if="soldoutError" style="color: #ff6b6b">
                            {{ soldoutError }}
                        </div>

                        <ul v-else style="margin: 0; padding-left: 18px">
                            <li v-for="m in soldoutMovies" :key="m.id_film">
                                <b>{{ m.titlu }}</b> — sold out:
                                <b>{{ m.nr_proiectii_soldout }}</b> /
                                {{ m.total_proiectii_in_perioada }}
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

import fightClub from "../../images/fight-club.jpg";
import inception from "../../images/inception.jpg";
import lalaland from "../../images/lalaland.jpg";
import matrix from "../../images/matrix.jpg";
import parasite from "../../images/parasite.jpg";
import pulpFiction from "../../images/pulp-fiction.jpg";
import shawshank from "../../images/shawshank.jpg";
import whiplash from "../../images/whiplash.jpg";
import interstellar from "../../images/interstellar.jpg";
import backToTheFuture from "../../images/back-to-the-future.jpg";
import cityOfGod from "../../images/city-of-god.jpeg";
import djangoUnchained from "../../images/django-unchained.jpg";
import forrestGump from "../../images/forrest-gump.jpg";
import gladiator from "../../images/gladiator.jpg";
import inglouriousBasterds from "../../images/inglourious.jpg";
import lotrReturn from "../../images/LOTR_the-return.jpg";
import lotrTwoTowers from "../../images/LOTR-the_2towers.jpg";
import lotrFellowship from "../../images/LOTR-the_fellowship.jpg";
import savingPrivateRyan from "../../images/saving-private-ryan.jpg";
import se7en from "../../images/se7en.jpg";
import spiritedAway from "../../images/spirited-away.jpg";
import theDarkKnight from "../../images/the-dark-knight.jpg";
import theDeparted from "../../images/the-departed.jpg";
import theGreenMile from "../../images/the-green-mile.jpg";
import theLionKing from "../../images/the-lion-king.jpg";
import thePianist from "../../images/the-pianist.jpg";
import thePrestige from "../../images/the-prestige.jpg";
import theSilenceOfTheLambs from "../../images/the-silence-of-the-lambs.jpg";
import theSocialNetwork from "../../images/the-social-network.jpg";

const router = useRouter();

/* MOVIES */
const movies = ref([
    {
        title: "Fight Club",
        poster: fightClub,
        year: 1999,
        genre: "Drama",
        duration: "2h 19m",
        tagline: "Regula #1: nu vorbim despre Fight Club.",
    },
    {
        title: "Inception",
        poster: inception,
        year: 2010,
        genre: "Sci-Fi / Thriller",
        duration: "2h 28m",
        tagline: "Ideea e cea mai periculoasă armă.",
    },
    {
        title: "La La Land",
        poster: lalaland,
        year: 2016,
        genre: "Romance / Musical",
        duration: "2h 8m",
        tagline: "Un oraș al viselor, două destine.",
    },
    {
        title: "The Matrix",
        poster: matrix,
        year: 1999,
        genre: "Sci-Fi",
        duration: "2h 16m",
        tagline: "Bine ai venit în deșertul realității.",
    },
    {
        title: "Parasite",
        poster: parasite,
        year: 2019,
        genre: "Thriller / Drama",
        duration: "2h 12m",
        tagline: "O familie intră… și nu mai iese la fel.",
    },
    {
        title: "Pulp Fiction",
        poster: pulpFiction,
        year: 1994,
        genre: "Crime",
        duration: "2h 34m",
        tagline: "Povești încrucișate, stil Tarantino.",
    },
    {
        title: "The Shawshank Redemption",
        poster: shawshank,
        year: 1994,
        genre: "Drama",
        duration: "2h 22m",
        tagline: "Speranța e un lucru bun.",
    },
    {
        title: "Whiplash",
        poster: whiplash,
        year: 2014,
        genre: "Drama / Music",
        duration: "1h 47m",
        tagline: "Nu există două cuvinte mai periculoase: «Good job».",
    },
    {
        title: "Interstellar",
        poster: interstellar,
        year: 2014,
        genre: "Sci-Fi",
        duration: "2h 49m",
        tagline: "Călătoria dincolo de stele pentru salvarea umanității.",
    },
    {
        title: "Back to the Future",
        poster: backToTheFuture,
        year: 1985,
        genre: "Sci-Fi / Adventure",
        duration: "1h 56m",
        tagline: "",
    },
    {
        title: "City of God",
        poster: cityOfGod,
        year: 2002,
        genre: "Crime / Drama",
        duration: "2h 10m",
        tagline: "",
    },
    {
        title: "Django Unchained",
        poster: djangoUnchained,
        year: 2012,
        genre: "Western / Drama",
        duration: "2h 45m",
        tagline: "",
    },
    {
        title: "Forrest Gump",
        poster: forrestGump,
        year: 1994,
        genre: "Drama / Romance",
        duration: "2h 22m",
        tagline: "",
    },
    {
        title: "Gladiator",
        poster: gladiator,
        year: 2000,
        genre: "Action / Drama",
        duration: "2h 35m",
        tagline: "",
    },
    {
        title: "Inglourious Basterds",
        poster: inglouriousBasterds,
        year: 2009,
        genre: "War / Drama",
        duration: "2h 33m",
        tagline: "",
    },
    {
        title: "The Lord of the Rings: The Return of the King",
        poster: lotrReturn,
        year: 2003,
        genre: "Fantasy / Adventure",
        duration: "3h 21m",
        tagline: "",
    },
    {
        title: "The Lord of the Rings: The Two Towers",
        poster: lotrTwoTowers,
        year: 2002,
        genre: "Fantasy / Adventure",
        duration: "2h 59m",
        tagline: "",
    },
    {
        title: "The Lord of the Rings: The Fellowship of the Ring",
        poster: lotrFellowship,
        year: 2001,
        genre: "Fantasy / Adventure",
        duration: "2h 58m",
        tagline: "",
    },
    {
        title: "Saving Private Ryan",
        poster: savingPrivateRyan,
        year: 1998,
        genre: "War / Drama",
        duration: "2h 49m",
        tagline: "",
    },
    {
        title: "Se7en",
        poster: se7en,
        year: 1995,
        genre: "Crime / Thriller",
        duration: "2h 7m",
        tagline: "",
    },
    {
        title: "Spirited Away",
        poster: spiritedAway,
        year: 2001,
        genre: "Animation / Fantasy",
        duration: "2h 5m",
        tagline: "",
    },
    {
        title: "The Dark Knight",
        poster: theDarkKnight,
        year: 2008,
        genre: "Action / Crime",
        duration: "2h 32m",
        tagline: "",
    },
    {
        title: "The Departed",
        poster: theDeparted,
        year: 2006,
        genre: "Crime / Drama",
        duration: "2h 31m",
        tagline: "",
    },
    {
        title: "The Green Mile",
        poster: theGreenMile,
        year: 1999,
        genre: "Drama / Fantasy",
        duration: "3h 9m",
        tagline: "",
    },
    {
        title: "The Lion King",
        poster: theLionKing,
        year: 1994,
        genre: "Animation / Adventure",
        duration: "1h 28m",
        tagline: "",
    },
    {
        title: "The Pianist",
        poster: thePianist,
        year: 2002,
        genre: "Biography / Drama",
        duration: "2h 30m",
        tagline: "",
    },
    {
        title: "The Prestige",
        poster: thePrestige,
        year: 2006,
        genre: "Drama / Mystery",
        duration: "2h 10m",
        tagline: "",
    },
    {
        title: "The Silence of the Lambs",
        poster: theSilenceOfTheLambs,
        year: 1991,
        genre: "Thriller / Crime",
        duration: "1h 58m",
        tagline: "",
    },
    {
        title: "The Social Network",
        poster: theSocialNetwork,
        year: 2010,
        genre: "Biography / Drama",
        duration: "2h 1m",
        tagline: "",
    },
]);

const featured = ref(movies.value[0]);

/* HERO helpers */
const todayLabel = computed(() => new Date().toISOString().slice(0, 10));

/* TODAY PROGRAM */
const todayProgram = ref([]);
const todayLoading = ref(false);
const todayError = ref("");

const getTodayISO = () => new Date().toLocaleDateString("sv-SE");

const normalizeTodayProgram = (rows) => {
    return (rows || []).map((r) => {
        const capacitate = Number(r.capacitate ?? r.locuri_total ?? 0);
        const vandute = Number(r.bilete_vandute ?? r.ocupate ?? 0);
        const libere = capacitate ? Math.max(capacitate - vandute, 0) : 0;
        const ocupare = capacitate
            ? Math.round((vandute / capacitate) * 100)
            : 0;

        return {
            id_proiectie: r.id_proiectie,
            titlu: r.titlu ?? r.film ?? "",
            sala: r.sala ?? r.nume_sala ?? "",
            ora:
                (r.ora_proiectie
                    ? String(r.ora_proiectie).slice(0, 5)
                    : r.ora) || "",
            capacitate,
            locuri_libere: Number(r.locuri_libere ?? libere),
            ocupare_proc: Number(r.ocupare_proc ?? ocupare),
        };
    });
};

const loadTodayProgram = async () => {
    todayLoading.value = true;
    todayError.value = "";
    try {
        const d = getTodayISO();
        const { data } = await axios.get("/api/projections", {
            params: { date: d },
        });
        const list = data.projections ?? data.data ?? [];
        todayProgram.value = normalizeTodayProgram(list);
    } catch (e) {
        todayError.value =
            e.response?.data?.message ||
            "Nu am putut încărca programul de azi.";
        todayProgram.value = [];
    } finally {
        todayLoading.value = false;
    }
};

const goToProjection = (p) => {
    router.push({
        path: "/tickets",
        query: { movie: p.titlu, projection: p.id_proiectie },
    });
};

/* Carousel */
const railRef = ref(null);
const CARD_W = 210;
const GAP = 18;
const VISIBLE = 5;
const STEP = (CARD_W + GAP) * VISIBLE;

const scrollPrev = () =>
    railRef.value?.scrollBy({ left: -STEP, behavior: "smooth" });
const scrollNext = () =>
    railRef.value?.scrollBy({ left: STEP, behavior: "smooth" });

const setFeatured = (m) => (featured.value = m);

/* Stats #1 */
const topMovies = ref([]);
const statsLoading = ref(false);
const statsError = ref("");

const loadStats = async () => {
    statsLoading.value = true;
    statsError.value = "";
    try {
        const { data } = await axios.get("/api/stats/top-movies");
        topMovies.value = data.top_movies ?? [];
    } catch (e) {
        statsError.value =
            e.response?.data?.message || "Nu am putut încărca top movies.";
        topMovies.value = [];
    } finally {
        statsLoading.value = false;
    }
};

/* Stats #2 */
const typeStats = ref([]);
const typeTotal = ref(0);
const typeLoading = ref(false);
const typeError = ref("");

const loadTypeStats = async () => {
    typeLoading.value = true;
    typeError.value = "";
    try {
        const { data } = await axios.get("/api/stats/tickets-by-client-type");
        typeStats.value = data.by_type ?? [];
        typeTotal.value = data.total_bilete ?? 0;
    } catch (e) {
        typeError.value =
            e.response?.data?.message ||
            "Nu am putut încărca distribuția pe tip.";
        typeStats.value = [];
        typeTotal.value = 0;
    } finally {
        typeLoading.value = false;
    }
};

/* Stats #3 - top halls */
const topHalls = ref([]);
const hallLoading = ref(false);
const hallError = ref("");

const pad2 = (n) => String(n).padStart(2, "0");
const now = new Date();
const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

const hallStart = ref(
    `${firstDay.getFullYear()}-${pad2(firstDay.getMonth() + 1)}-${pad2(
        firstDay.getDate()
    )}`
);
const hallEnd = ref(
    `${lastDay.getFullYear()}-${pad2(lastDay.getMonth() + 1)}-${pad2(
        lastDay.getDate()
    )}`
);

const loadHallStats = async () => {
    hallLoading.value = true;
    hallError.value = "";
    try {
        const { data } = await axios.get("/api/stats/top-halls-occupancy", {
            params: { start: hallStart.value, end: hallEnd.value },
        });
        topHalls.value = data.top_halls ?? [];
    } catch (e) {
        hallError.value =
            e.response?.data?.message || "Nu am putut încărca top săli.";
        topHalls.value = [];
    } finally {
        hallLoading.value = false;
    }
};

/* Stats #4 */
const occProjections = ref([]);
const occLoading = ref(false);
const occError = ref("");

const loadHighOcc = async () => {
    occLoading.value = true;
    occError.value = "";
    try {
        const { data } = await axios.get(
            "/api/stats/projections-high-occupancy",
            { params: { min: 80 } }
        );
        occProjections.value = data.projections ?? [];
    } catch (e) {
        occError.value =
            e.response?.data?.message || "Nu am putut încărca ocuparea >80%.";
        occProjections.value = [];
    } finally {
        occLoading.value = false;
    }
};

/* Stats #5 */
const topUsers = ref([]);
const usersLoading = ref(false);
const usersError = ref("");

const loadTopUsers = async () => {
    usersLoading.value = true;
    usersError.value = "";
    try {
        const { data } = await axios.get("/api/stats/top-users");
        topUsers.value = data.top_users ?? [];
    } catch (e) {
        usersError.value =
            e.response?.data?.message || "Nu am putut încărca top users.";
        topUsers.value = [];
    } finally {
        usersLoading.value = false;
    }
};

/* Soldout */
const soldoutMovies = ref([]);
const soldoutLoading = ref(false);
const soldoutError = ref("");

const soldFrom = ref("");
const soldTo = ref("");

const loadSoldout = async () => {
    soldoutLoading.value = true;
    soldoutError.value = "";
    try {
        const { data } = await axios.get("/api/stats/soldout-movies", {
            params: {
                from: soldFrom.value || undefined,
                to: soldTo.value || undefined,
            },
        });
        soldoutMovies.value = data.soldout_movies ?? [];
    } catch (e) {
        soldoutError.value =
            e.response?.data?.message ||
            "Nu am putut încărca filmele sold out.";
        soldoutMovies.value = [];
    } finally {
        soldoutLoading.value = false;
    }
};

onMounted(() => {
    loadTodayProgram();
    loadStats();
    loadTypeStats();
    loadHallStats();
    loadHighOcc();
    loadTopUsers();
    loadSoldout();
});
</script>

<style scoped>
.page {
    min-height: 100vh;
    background: #0b0b0b;
    color: #fff;
}
.wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 36px 18px 80px;
}

/* HERO */
.hero {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    padding: 28px;
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 22px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: blur(18px) brightness(0.45);
    transform: scale(1.1);
    opacity: 0.9;
}
.hero-content,
.hero-poster {
    position: relative;
    z-index: 1;
}

.pill {
    display: inline-flex;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.1);
    font-size: 0.85rem;
    border: 1px solid rgba(255, 255, 255, 0.12);
    margin-bottom: 10px;
}
.hero-title {
    font-size: 2.2rem;
    margin: 8px 0 8px;
    letter-spacing: 0.2px;
}
.hero-sub {
    color: rgba(255, 255, 255, 0.85);
    max-width: 56ch;
    line-height: 1.5;
    margin-bottom: 10px;
}

/* ✅ featured meta “sus” */
.featuredMeta.top {
    margin-top: 10px;
    margin-bottom: 16px;
    padding: 12px 12px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(0, 0, 0, 0.22);
}
.featuredTitle {
    font-weight: 950;
    font-size: 1.15rem;
}
.featuredSub {
    margin-top: 4px;
    color: rgba(255, 255, 255, 0.75);
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.featuredTagline {
    margin-top: 6px;
    color: rgba(255, 255, 255, 0.82);
}

/* HERO ROW */
.hero-row {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 18px;
    align-items: start;
    margin-top: 10px;
}

.actionsCol {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.miniHint {
    margin-top: 6px;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.65);
    line-height: 1.4;
}

/* buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto !important;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.06);
    color: #fff;
    cursor: pointer;
    font-weight: 800;
    letter-spacing: 0.2px;
    transition: transform 0.15s ease, background 0.15s ease,
        border-color 0.15s ease, box-shadow 0.15s ease;
}
.btn.big {
    padding: 14px 16px;
    font-size: 1rem;
}
.btn.primary {
    background: linear-gradient(180deg, #ff1a1a, #d6000a);
    border-color: rgba(255, 0, 25, 0.55);
    box-shadow: 0 10px 22px rgba(229, 9, 20, 0.25);
}
.btn.ghost {
    background: rgba(255, 255, 255, 0.04);
}
.btn:hover {
    transform: translateY(-1px);
    border-color: rgba(255, 255, 255, 0.28);
}
.btn.primary:hover {
    box-shadow: 0 12px 28px rgba(229, 9, 20, 0.3);
}

/* TODAY */
.todayCard {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.06);
    border-radius: 18px;
    padding: 14px;
    min-width: 0;
}
.todayHead {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 12px;
}
.todayTitle {
    font-size: 1.1rem;
    font-weight: 900;
}
.todaySub {
    margin-top: 2px;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.65);
}
.todayList {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 290px;
    overflow: auto;
    padding-right: 6px;
}
.todayItem {
    text-align: left;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(0, 0, 0, 0.22);
    border-radius: 16px;
    padding: 12px;
    display: grid;
    grid-template-columns: 1fr 170px;
    gap: 10px;
    color: #fff;
    cursor: pointer;
    transition: transform 0.15s ease, border-color 0.15s ease,
        background 0.15s ease;
}
.todayItem:hover {
    transform: translateY(-1px);
    border-color: rgba(255, 255, 255, 0.22);
    background: rgba(0, 0, 0, 0.3);
}
.time {
    font-size: 1.2rem;
    font-weight: 900;
}
.film {
    margin-top: 4px;
    font-weight: 900;
}
.hall {
    margin-top: 3px;
    color: rgba(255, 255, 255, 0.7);
}
.right {
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 6px;
    justify-content: center;
}
.seats,
.occ {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.92rem;
}
.bar {
    height: 8px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    overflow: hidden;
}
.barFill {
    height: 100%;
    background: rgba(255, 255, 255, 0.45);
    border-radius: 999px;
}
.emptyToday {
    padding: 10px;
    color: rgba(255, 255, 255, 0.75);
}

/* poster */
.hero-poster {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}
.hero-poster img {
    width: min(280px, 100%);
    aspect-ratio: 2 / 3;
    object-fit: cover;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
}

/* Sections */
.section {
    margin-top: 30px;
}
.section-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    margin: 18px 0 10px;
}
.section h2 {
    font-size: 1.35rem;
    letter-spacing: 0.6px;
}

/* carousel */
.carousel {
    --card: 210px;
    --gap: 18px;
    max-width: calc(5 * var(--card) + 4 * var(--gap) + 16px);
    margin: 0 auto;
    position: relative;
}
.rail {
    display: flex;
    gap: var(--gap);
    overflow-x: auto;
    padding: 10px 8px 12px;
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
}
.movie {
    flex: 0 0 var(--card);
    width: var(--card);
    scroll-snap-align: start;
    background: transparent;
    border: none;
    padding: 0;
    text-align: left;
    color: inherit;
    cursor: pointer;
}
.poster {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.05);
}
.poster img {
    width: 100%;
    height: 310px;
    object-fit: cover;
    display: block;
    transition: transform 0.25s ease;
}
.movie:hover .poster img {
    transform: scale(1.04);
}
.overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, 0.82),
        rgba(0, 0, 0, 0.1)
    );
    opacity: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 12px;
    transition: opacity 0.22s ease;
}
.movie:hover .overlay {
    opacity: 1;
}
.title {
    margin-top: 10px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.92);
    line-height: 1.2;
}
.nav {
    position: absolute;
    top: 38%;
    transform: translateY(-50%);
    z-index: 2;
    width: 46px;
    height: 72px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(0, 0, 0, 0.35);
    color: #fff;
    font-size: 2rem;
    cursor: pointer;
    display: grid;
    place-items: center;
}
.nav.left {
    left: -6px;
}
.nav.right {
    right: -6px;
}

/* stats grid */
.grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 12px;
}
.info-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 14px;
}
.info-card h3 {
    margin: 0 0 6px;
}
.info-card p {
    margin: 0 0 10px;
    color: rgba(255, 255, 255, 0.75);
    line-height: 1.5;
}

/* small controls */
.rowDates {
    display: flex;
    gap: 8px;
    align-items: center;
    margin: 10px 0 10px;
    flex-wrap: wrap;
}
.rowDates input {
    border-radius: 10px;
    padding: 8px 10px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(0, 0, 0, 0.25);
    color: #fff;
}
.miniBtn {
    border-radius: 10px;
    padding: 8px 10px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    cursor: pointer;
    font-weight: 800;
}
.miniBtn:hover {
    transform: translateY(-1px);
}

@media (max-width: 900px) {
    .hero {
        grid-template-columns: 1fr;
    }
    .hero-row {
        grid-template-columns: 1fr;
    }
    .todayItem {
        grid-template-columns: 1fr;
    }
    .right {
        text-align: left;
    }
    .grid {
        grid-template-columns: 1fr;
    }
    .nav {
        display: none;
    }
}
</style>
