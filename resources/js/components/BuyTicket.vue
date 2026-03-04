<template>
    <div class="page">
        <div class="wrap">
            <h1>Selectează locul</h1>

            <p class="sub">
                Film: <b>{{ movieTitle || "—" }}</b>
            </p>

            <!-- PROJECTIONS -->
            <div class="card">
                <h2>Alege proiecția</h2>

                <select
                    v-model="selectedProjectionId"
                    class="select"
                    :disabled="loadingProjections || projections.length === 0"
                >
                    <option value="">— Selectează —</option>
                    <option
                        v-for="p in projections"
                        :key="p.id_proiectie"
                        :value="String(p.id_proiectie)"
                    >
                        {{ p.data_proiectie }} •
                        {{ formatTime(p.ora_proiectie) }} • {{ p.sala }}•
                        {{ p.titlu }}
                    </option>
                </select>

                <p v-if="loadingProjections" class="hint">
                    Se încarcă proiecțiile...
                </p>
                <p v-else-if="projError" class="err">{{ projError }}</p>
                <p
                    v-else-if="movieTitle && projections.length === 0"
                    class="hint"
                >
                    Nu există proiecții pentru filmul selectat.
                </p>
            </div>

            <!-- LEGEND -->
            <div class="legend">
                <span class="dot free"></span> Liber
                <span class="dot selected"></span> Selectat
                <span class="dot occupied"></span> Ocupat
            </div>

            <!-- SEAT MAP -->
            <div class="seatWrap" v-if="selectedProjectionId">
                <div class="seatCard">
                    <div class="seatGrid">
                        <div v-for="row in rows" :key="row" class="seatRow">
                            <div class="rowLabel">{{ row }}</div>

                            <button
                                v-for="col in cols"
                                :key="row + col"
                                class="seat"
                                type="button"
                                :class="seatClass(row + col)"
                                :disabled="isOccupied(row + col)"
                                @click="toggleSeat(row + col)"
                            >
                                {{ col }}
                            </button>
                        </div>
                    </div>

                    <p v-if="loadingSeats" class="hint">
                        Se încarcă locurile ocupate...
                    </p>
                    <p v-else-if="seatsError" class="err">{{ seatsError }}</p>
                </div>
            </div>

            <!-- CHECKOUT (înainte de butonul cumpără) -->
            <div class="checkout" v-if="selectedProjectionId">
                <div class="checkoutCard">
                    <h2>Checkout</h2>

                    <div class="line">
                        <span class="k">Film</span>
                        <span class="v">{{ movieTitle || "-" }}</span>
                    </div>

                    <div class="line">
                        <span class="k">Proiecție</span>
                        <span class="v">
                            {{
                                selectedProjection
                                    ? `${
                                          selectedProjection.data_proiectie
                                      } • ${formatTime(
                                          selectedProjection.ora_proiectie
                                      )}`
                                    : "-"
                            }}
                        </span>
                    </div>

                    <div class="line">
                        <span class="k">Sală</span>
                        <span class="v">{{
                            selectedProjection?.sala ?? "-"
                        }}</span>
                    </div>

                    <div class="line">
                        <span class="k">Loc</span>
                        <span class="v">
                            <b>{{ selectedSeat || "— alege un loc —" }}</b>
                        </span>
                    </div>

                    <hr class="sep" />

                    <div class="line">
                        <span class="k">Preț bilet</span>
                        <span class="v">{{ price }} lei</span>
                    </div>

                    <div class="line total">
                        <span class="k">Total</span>
                        <span class="v">{{ price }} lei</span>
                    </div>

                    <p class="smallHint" v-if="!selectedSeat">
                        Selectează un loc ca să poți continua.
                    </p>
                </div>

                <button
                    class="buyBtn"
                    :disabled="!canBuy || buying"
                    @click="buyTicket"
                    type="button"
                >
                    {{ buying ? "Se cumpără..." : "Cumpără bilet" }}
                </button>

                <p v-if="buyError" class="err">{{ buyError }}</p>
                <p v-if="buyOk" class="ok">{{ buyOk }}</p>
            </div>

            <div class="actions">
                <router-link class="backBtn" to="/cumpara-bilete"
                    >Înapoi</router-link
                >
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const movieTitle = computed(() => String(route.query.movie || "").trim());

// --- UI seat grid (poți ajusta)
const rows = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"];
const cols = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

// --- projections
const projections = ref([]);
const loadingProjections = ref(false);
const projError = ref("");

const selectedProjectionId = ref(""); // string
const selectedProjection = computed(() =>
    projections.value.find(
        (p) => String(p.id_proiectie) === String(selectedProjectionId.value)
    )
);

// --- occupied seats
const occupiedSeats = ref([]); // ["A1","B2",...]
const occupiedSet = computed(() => new Set(occupiedSeats.value.map(String)));

const loadingSeats = ref(false);
const seatsError = ref("");

// --- selection
const selectedSeat = ref("");

// --- checkout
const price = 25; // lei (poți face din backend dacă ai preț pe proiecție)

const canBuy = computed(() => {
    return (
        !!selectedProjectionId.value &&
        !!selectedSeat.value &&
        !isOccupied(selectedSeat.value)
    );
});

const buying = ref(false);
const buyError = ref("");
const buyOk = ref("");

const formatTime = (t) => {
    if (!t) return "-";
    const s = String(t);
    return s.length >= 5 ? s.slice(0, 5) : s;
};

const fetchProjections = async () => {
    projError.value = "";
    projections.value = [];
    selectedProjectionId.value = "";
    selectedSeat.value = "";
    occupiedSeats.value = [];

    if (!movieTitle.value) {
        projError.value = "Lipsește filmul (movie) din URL.";
        return;
    }

    loadingProjections.value = true;
    try {
        const { data } = await axios.get("/api/projections", {
            params: { movie: movieTitle.value },
        });
        projections.value = data.projections ?? [];
    } catch (e) {
        console.error(
            "PROJECTIONS ERROR:",
            e.response?.status,
            e.response?.data || e.message
        );
        projError.value =
            e.response?.data?.message || "Nu am putut încărca proiecțiile.";
    } finally {
        loadingProjections.value = false;
    }
};

const fetchOccupiedSeats = async () => {
    seatsError.value = "";
    occupiedSeats.value = [];
    selectedSeat.value = "";

    if (!selectedProjectionId.value) return;

    loadingSeats.value = true;
    try {
        const { data } = await axios.get(
            `/api/projections/${selectedProjectionId.value}/occupied-seats`
        );
        // backend: seats: [...]
        occupiedSeats.value = data.seats ?? data.occupied_seats ?? [];
    } catch (e) {
        console.error(
            "OCCUPIED SEATS ERROR:",
            e.response?.status,
            e.response?.data || e.message
        );
        seatsError.value =
            e.response?.data?.message ||
            "Nu am putut încărca locurile ocupate.";
    } finally {
        loadingSeats.value = false;
    }
};

const isOccupied = (seat) => occupiedSet.value.has(String(seat));

const seatClass = (seat) => {
    if (isOccupied(seat)) return "occupied";
    if (selectedSeat.value === seat) return "selected";
    return "free";
};

const toggleSeat = (seat) => {
    if (isOccupied(seat)) return;
    selectedSeat.value = selectedSeat.value === seat ? "" : seat;
};

const buyTicket = async () => {
    buyError.value = "";
    buyOk.value = "";

    if (!canBuy.value) return;

    buying.value = true;
    try {
        await axios.post("/api/tickets", {
            id_proiectie: Number(selectedProjectionId.value),
            loc_sala: selectedSeat.value,
            pret: price,
        });

        buyOk.value = "Bilet cumpărat cu succes!";
        // refresh locuri ocupate ca să vezi imediat roșu pe locul cumpărat
        await fetchOccupiedSeats();

        // opțional: te duce în cont
        // router.push("/account");
    } catch (e) {
        console.error(
            "BUY ERROR:",
            e.response?.status,
            e.response?.data || e.message
        );

        // dacă e duplicate key / 409, seat-ul a fost luat între timp
        buyError.value =
            e.response?.data?.message ||
            "Nu am putut cumpăra biletul. Încearcă din nou.";

        // refresh ca să vezi starea reală
        await fetchOccupiedSeats();
    } finally {
        buying.value = false;
    }
};

watch(
    () => movieTitle.value,
    () => fetchProjections(),
    { immediate: true }
);

watch(
    () => selectedProjectionId.value,
    () => {
        buyError.value = "";
        buyOk.value = "";
        fetchOccupiedSeats();
    }
);

onMounted(() => {
    // dacă ai nevoie de alt init
});
</script>

<style scoped>
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
    margin: 0 0 10px;
    font-size: 2.4rem;
}
.sub {
    margin: 0 0 18px;
    color: rgba(255, 255, 255, 0.7);
}

.card {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
}

.card h2 {
    margin: 0 0 10px;
    font-size: 1.1rem;
}

.select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(0, 0, 0, 0.25);
    color: #fff;
}

.legend {
    display: flex;
    gap: 16px;
    align-items: center;
    margin: 12px 0 14px;
    color: rgba(255, 255, 255, 0.75);
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
    margin-right: 6px;
}
.dot.free {
    background: rgba(255, 255, 255, 0.25);
}
.dot.selected {
    background: #2ecc71;
}
.dot.occupied {
    background: #ff3b3b;
}

.seatWrap {
    margin-top: 6px;
}
.seatCard {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.04);
    border-radius: 18px;
    padding: 16px;
}

.seatGrid {
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 6px;
}
.seatRow {
    display: grid;
    grid-template-columns: 44px repeat(12, 46px);
    gap: 8px;
    align-items: center;
}
.rowLabel {
    font-weight: 800;
    color: rgba(255, 255, 255, 0.8);
}

.seat {
    height: 44px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.9);
    cursor: pointer;
    font-weight: 700;
}

.seat.free:hover {
    border-color: rgba(255, 255, 255, 0.22);
    background: rgba(255, 255, 255, 0.1);
}

.seat.selected {
    background: rgba(46, 204, 113, 0.35);
    border-color: rgba(46, 204, 113, 0.8);
    color: #fff;
}

.seat.occupied {
    background: rgba(255, 59, 59, 0.35);
    border-color: rgba(255, 59, 59, 0.8);
    color: rgba(255, 255, 255, 0.85);
    cursor: not-allowed;
}

.seat:disabled {
    opacity: 0.9;
}

.checkout {
    margin-top: 18px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.checkoutCard {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 16px;
}

.checkoutCard h2 {
    margin: 0 0 12px;
    font-size: 1.15rem;
}

.line {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0;
}
.k {
    color: rgba(255, 255, 255, 0.7);
}
.v {
    font-weight: 700;
}

.sep {
    border: none;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin: 10px 0;
}

.total .v {
    font-size: 1.1rem;
}

.smallHint {
    margin: 10px 0 0;
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.95rem;
}

.buyBtn {
    padding: 12px 16px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    font-weight: 900;
    cursor: pointer;
    width: fit-content;
}

.buyBtn:hover {
    background: rgba(255, 255, 255, 0.12);
}

.buyBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.actions {
    margin-top: 18px;
}

.backBtn {
    display: inline-block;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.06);
    color: #fff;
    text-decoration: none;
    font-weight: 800;
}

.hint {
    margin: 10px 0 0;
    color: rgba(255, 255, 255, 0.7);
}
.err {
    margin: 10px 0 0;
    color: #ff5c5c;
    font-weight: 700;
}
.ok {
    margin: 10px 0 0;
    color: #2ecc71;
    font-weight: 800;
}
</style>
