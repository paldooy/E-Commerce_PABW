<script setup>
import { onMounted, ref } from 'vue'
import { session } from '../stores/session'

const tasks = ref([])
const loading = ref(false)

const statusTextMap = {
  menunggu_kurir: 'Menunggu kurir',
  sedang_dikirim: 'Sedang dikirim',
  sampai_di_tujuan: 'Sampai di tujuan',
  dikirim_balik: 'Dikirim balik',
  menunggu_penjual: 'Kembali ke penjual',
}

function statusText(status) {
  return statusTextMap[status] || status
}

function availableActions(status) {
  if (status === 'menunggu_kurir') {
    return [
      { value: 'sedang_dikirim', label: 'Ambil Tugas' },
      { value: 'dikirim_balik', label: 'Kirim Balik' },
    ]
  }

  if (status === 'sedang_dikirim') {
    return [{ value: 'sampai_di_tujuan', label: 'Konfirmasi Sampai' }]
  }

  if (status === 'dikirim_balik') {
    return [{ value: 'menunggu_penjual', label: 'Barang Kembali ke Penjual' }]
  }

  return []
}

async function loadTasks() {
  tasks.value = await session.api('/courier/order-items', {
    headers: session.authHeaders(),
  })
}

async function refreshTasks() {
  loading.value = true

  try {
    await loadTasks()
  } catch (error) {
    session.setFlash(error.message, 'error')
  } finally {
    loading.value = false
  }
}

async function updateStatus(itemId, newStatus) {
  try {
    await session.api(`/courier/order-items/${itemId}/status`, {
      method: 'PATCH',
      headers: session.authHeaders(),
      body: JSON.stringify({
        new_status: newStatus,
      }),
    })

    session.setFlash('Status tugas kurir diperbarui.', 'success')
    await loadTasks()
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

onMounted(() => {
  refreshTasks()
})
</script>

<template>
  <main class="stack">
    <section class="panel inline" style="justify-content: space-between">
      <div class="stack" style="gap: 6px">
        <h2>Halaman Kurir</h2>
        <p class="subtle">Ambil tugas pengiriman dan update status pengantaran.</p>
      </div>

      <button class="btn ghost" @click="refreshTasks" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Refresh Tugas' }}
      </button>
    </section>

    <section class="panel stack">
      <h3>Daftar Tugas</h3>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>ID Item</th>
              <th>Produk</th>
              <th>Qty</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in tasks" :key="item.id">
              <td>#{{ item.id }}</td>
              <td>{{ item.product_name_snapshot }}</td>
              <td>{{ item.quantity }}</td>
              <td>{{ statusText(item.status) }}</td>
              <td>
                <div class="inline">
                  <button
                    v-for="action in availableActions(item.status)"
                    :key="action.value"
                    class="btn primary"
                    @click="updateStatus(item.id, action.value)"
                  >
                    {{ action.label }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="tasks.length === 0">
              <td colspan="5" class="subtle">Tidak ada tugas aktif.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</template>
