<template>
    <div id="app">
        <Navbar />
        <router-view />
    </div>
</template>

<script>
import Navbar from "./components/Navbar.vue";

export default {
    components: { Navbar },
};
</script>

<style scoped>
.app {
    min-height: 100vh;
    background: linear-gradient(135deg, #efefee, #1a1a1a);
    background-attachment: fixed; /* efect de parallax */
    color: white;
}
</style>
