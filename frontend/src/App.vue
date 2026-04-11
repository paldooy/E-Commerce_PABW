<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { session } from './stores/session'

const router = useRouter()
const route = useRoute()
const me = session.me
const flash = session.flash
const isLoggedIn = session.isLoggedIn
const globalSearch = ref('')

// Format uang ke dalam format Rupiah Indonesia
const formatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
})

function formatMoney(amount) {
  return formatter.format(Number(amount || 0))
}

function logout() {
  session.logout()
  session.setFlash('Kamu sudah logout.', 'info')
  router.push('/login')
}

function runGlobalSearch() {
  const keyword = globalSearch.value.trim()
  if (!keyword) {
    return
  }

  if (!isLoggedIn.value) {
    router.push({
      name: 'login',
      query: {
        redirect: `/dashboard?q=${encodeURIComponent(keyword)}`,
      },
    })
    return
  }

  if (me.value?.role !== 'user') {
    session.setFlash('Pencarian produk tersedia untuk role user/pembeli.', 'info')
    return
  }

  router.push({
    name: 'dashboard',
    query: {
      q: keyword,
    },
  })
}

onMounted(() => {
  session.restoreSession()
  if (route.name === 'dashboard' && typeof route.query.q === 'string') {
    globalSearch.value = route.query.q
  }
})
</script>

<template>
  <div class="page-bg">
    <header class="market-header">
      <div class="app-shell">
        <div class="market-main" style="padding: 12px 0;">
          <RouterLink to="/" class="brand">SokoFlow</RouterLink>

          <form class="global-search" @submit.prevent="runGlobalSearch" style="margin: 0 20px;">
            <input
              v-model="globalSearch"
              type="text"
              placeholder="Cari produk favorit kamu..."
            />
            <button type="submit">Cari</button>
          </form>

          <div class="header-account inline" style="gap: 16px; align-items: center;">
            <template v-if="me?.role === 'user'">
              <div class="inline" style="gap: 14px;">
                <RouterLink to="/dashboard" title="Belanja" style="color: var(--ink);">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </RouterLink>
                 <RouterLink to="/seller" title="Toko Saya" style="color: var(--ink);">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-2.205a2 2 0 0 1 1.79 0L12 7l3.8-1.9a2 2 0 0 1 1.8 0L22 7v14H2V7Z"/><path d="M16 21V11a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v10"/><path d="M12 21v-4"/></svg>
                </RouterLink>
                <RouterLink to="/cart" title="Keranjang Belanja" style="color: var(--ink);">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </RouterLink>
                <RouterLink to="/orders" title="Pesanan Saya" style="color: var(--ink);">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </RouterLink>
              </div>
              <div class="inline" style="background: linear-gradient(135deg, var(--primary) 0%, #ff8a00 100%); color: white; padding: 6px 16px; border-radius: 20px; font-size: 0.95rem; font-weight: 700; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 120px; justify-content: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                {{ session.wallet.value ? formatMoney(session.wallet.value.balance) : 'Rp 0' }}
              </div>
            </template>
            <template v-else-if="me?.role === 'courier'">
              <RouterLink to="/courier" class="btn ghost">Pengiriman</RouterLink>
            </template>
            <template v-else-if="me?.role === 'admin'">
              <RouterLink to="/admin" class="btn ghost">Kontrol Admin</RouterLink>
            </template>

            <div class="inline" style="gap: 8px;">
              <template v-if="isLoggedIn">
                <button class="btn ghost" @click="logout" style="padding: 6px 12px; border: 1px solid var(--line);">Logout</button>
              </template>
              <template v-else>
                <RouterLink to="/register" class="btn ghost">Daftar</RouterLink>
                <RouterLink to="/login" class="btn primary">Masuk</RouterLink>
              </template>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="app-shell">

      <p v-if="flash.text" :class="['flash', flash.type]">{{ flash.text }}</p>

      <RouterView />
    </div>
  </div>
</template>
