<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { session } from '../stores/session'

const router = useRouter()
const isLoggedIn = session.isLoggedIn
const me = session.me
const products = ref([])

const highlightedProducts = computed(() => products.value.filter((x) => x.status === 'stok_tersedia').slice(0, 10))

async function loadProducts() {
  products.value = await session.api('/products')
}

function goToMainFlow() {
  if (!isLoggedIn.value) {
    router.push('/login')
    return
  }

  if (me.value?.role === 'user') {
    router.push('/dashboard')
    return
  }

  if (me.value?.role === 'courier') {
    router.push('/courier')
    return
  }

  if (me.value?.role === 'admin') {
    router.push('/admin')
  }
}

onMounted(() => {
  loadProducts().catch(() => {})
})
</script>

<template>
  <main class="stack">
    <section class="market-hero">
      <div class="stack">
        <h1>Belanja Cepat, Alur Bisnis Tetap Rapi</h1>
        <p class="subtle">
          Nuansa marketplace umum untuk pembeli, namun transaksi, fulfillment, dan wallet tetap mengikuti
          konsep role-based yang sudah ditetapkan.
        </p>
        <div class="inline">
          <button class="btn primary" @click="goToMainFlow">Mulai Sekarang</button>
          <RouterLink v-if="!isLoggedIn" to="/register" class="btn ghost">Buat Akun</RouterLink>
          <RouterLink v-if="isLoggedIn && me?.role === 'user'" to="/seller" class="btn accent">
            Kelola Toko Saya
          </RouterLink>
        </div>
      </div>

      <div class="stack">
        <div class="market-tile">
          <h3>Flow Pembeli</h3>
          <p class="subtle">Cari produk, masukkan keranjang, checkout, pantau order.</p>
        </div>
        <div class="market-tile">
          <h3>Flow Operasional</h3>
          <p class="subtle">Seller proses order, kurir kirim, admin kontrol akun dan wallet.</p>
        </div>
      </div>
    </section>

    <section class="panel stack">
      <div class="inline" style="justify-content: space-between">
        <h2>Flash Sale Internal</h2>
        <RouterLink v-if="isLoggedIn && me?.role === 'user'" to="/dashboard" class="btn ghost">
          Lihat Semua Produk
        </RouterLink>
      </div>

      <div class="product-grid">
        <article class="product-card" v-for="item in highlightedProducts" :key="item.id">
          <img class="product-image" :src="item.image_url" :alt="item.name" />
          <div class="product-body stack" style="gap: 8px">
            <h4>{{ item.name }}</h4>
            <p class="subtle">{{ item.description }}</p>
            <div class="inline" style="justify-content: space-between">
              <span class="price">Rp{{ Number(item.price).toLocaleString('id-ID') }}</span>
              <span :class="['badge', item.status === 'stok_tersedia' ? 'ok' : 'warn']">
                {{ item.status === 'stok_tersedia' ? 'Ready' : 'Habis' }}
              </span>
            </div>
          </div>
        </article>
      </div>
    </section>
  </main>
</template>
