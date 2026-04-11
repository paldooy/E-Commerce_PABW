<template>
  <main class="stack" style="max-width: 1000px; margin: 0 auto;">
    <div class="panel inline" style="justify-content: space-between">
      <div class="stack" style="gap: 6px">
        <h2>Pesanan Saya</h2>
        <p class="subtle">Lacak status pesanan kamu di sini.</p>
      </div>

      <button class="btn ghost" @click="refreshAll" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Refresh Data' }}
      </button>
    </div>

    <div class="stack" style="gap: 16px;">
      <div class="table-panel">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Harga Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in buyerOrders" :key="item.id" style="border-bottom: 1px solid #f1d7c7;">
                <td>#{{ String(i + 1).padStart(3, '0') }}</td>
                <td style="font-weight: 600;">
                  {{ item.product?.name }} <span class="subtle" style="font-weight: 400;">(x{{ item.quantity }})</span>
                </td>
                <td style="font-weight: 700; color: var(--ink);">{{ formatMoney(item.price * item.quantity) }}</td>
                <td>
                  <span :class="statusClass(item.status)">
                    {{ statusText(item.status) }}
                  </span>
                </td>
                <td>
                  <button v-if="item.status === 'sampai_di_tujuan'" class="btn accent" style="padding: 6px 12px; font-size: 0.8rem;" @click="markReceived(item.id)">
                    Konfirmasi Diterima
                  </button>
                  <span v-else class="subtle" style="font-size: 0.85rem">-</span>
                </td>
              </tr>
              <tr v-if="!buyerOrders.length">
                <td colspan="5" style="text-align: center; padding: 40px; color: var(--muted);">Belum ada riwayat pesanan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { session } from '../stores/session'

const buyerOrders = ref([])
const loading = ref(false)

const formatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
})

const statusTextMap = {
  menunggu_penjual: 'Menunggu penjual',
  diproses_penjual: 'Diproses penjual',
  menunggu_kurir: 'Menunggu kurir',
  sedang_dikirim: 'Sedang dikirim',
  sampai_di_tujuan: 'Sampai di tujuan',
  diterima_pembeli: 'Selesai / Diterima',
  dikomplain: 'Dikomplain',
  dikirim_balik: 'Dikirim balik',
  transaksi_gagal: 'Transaksi Batal',
}

function formatMoney(amount) {
  return formatter.format(Number(amount || 0))
}

function statusText(status) {
  return statusTextMap[status] || status
}

function statusClass(status) {
  if (status === 'diterima_pembeli') {
    return 'badge ok'
  }
  if (status === 'transaksi_gagal' || status === 'dikomplain' || status === 'dikirim_balik') {
    return 'badge warn'
  }
  return 'badge'
}

async function loadBuyerOrders() {
  buyerOrders.value = await session.api('/orders/items/mine?as_seller=false', {
    headers: session.authHeaders(),
  })
}

async function refreshAll() {
  loading.value = true
  try {
    await loadBuyerOrders()
    await session.loadMe()
  } catch (error) {
    session.setFlash(error.message, 'error')
  } finally {
    loading.value = false
  }
}

async function markReceived(itemId) {
  try {
    await session.api(`/buyer/order-items/${itemId}/receive`, {
      method: 'POST',
      headers: session.authHeaders(),
      body: JSON.stringify({
        note: 'Diterima oleh pembeli',
      }),
    })
    session.setFlash('Terima kasih. Pesanan telah dikonfirmasi selesai.', 'success')
    await refreshAll()
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

onMounted(() => {
  refreshAll()
})
</script>
