<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { session } from '../stores/session'

const myProducts = ref([])
const sellerOrders = ref([])
const loading = ref(false)
const activeTab = ref('dashboard')
const fileInput = ref(null)

const form = reactive({
  name: '',
  description: '',
  image_url: '',
  price: 0,
  stock: 1,
})

const me = session.me

const statusTextMap = {
  menunggu_penjual: 'Menunggu penjual',
  diproses_penjual: 'Diproses penjual',
  menunggu_kurir: 'Menunggu kurir',
  sedang_dikirim: 'Sedang dikirim',
  sampai_di_tujuan: 'Sampai di tujuan',
  diterima_pembeli: 'Diterima pembeli',
  dikomplain: 'Dikomplain',
  dikirim_balik: 'Dikirim balik',
  transaksi_gagal: 'Transaksi gagal',
}

const canSubmit = computed(() => Number(form.price) > 0 && Number(form.stock) >= 0)

function statusText(status) {
  return statusTextMap[status] || status
}

function nextSellerStatus(status) {
  if (status === 'menunggu_penjual') {
    return 'diproses_penjual'
  }

  if (status === 'diproses_penjual') {
    return 'menunggu_kurir'
  }

  return null
}

function nextStatusLabel(status) {
  const next = nextSellerStatus(status)
  if (next === 'diproses_penjual') {
    return 'Proses (Kemas)'
  }

  if (next === 'menunggu_kurir') {
    return 'Serahkan Kurir'
  }

  return '-'
}

function formatMoney(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

async function loadMyProducts() {
  const products = await session.api('/products')
  myProducts.value = products.filter((item) => item.seller_id === me.value?.id)
}

async function loadSellerOrders() {
  sellerOrders.value = await session.api('/orders/items/mine?as_seller=true', {
    headers: session.authHeaders(),
  })
}

async function refreshAll() {
  loading.value = true

  try {
    await Promise.all([loadMyProducts(), loadSellerOrders()])
  } catch (error) {
    session.setFlash(error.message, 'error')
  } finally {
    loading.value = false
  }
}

const isUploading = ref(false)

async function handleImageUpload(event) {
  const file = event.target.files[0]
  if (!file) return

  isUploading.value = true
  const formData = new FormData()
  formData.append('file', file)

  try {
    const res = await fetch(`${session.apiBase.value}/products/upload-image`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${session.token.value}`,
      },
      body: formData,
    })
    
    const data = await res.json()
    if (!res.ok) throw new Error(data.detail || 'Request gagal')

    form.image_url = data.image_url
    session.setFlash('Foto berhasil diunggah.', 'success')
  } catch (error) {
    session.setFlash('Gagal mengunggah foto: ' + error.message, 'error')
  } finally {
    isUploading.value = false
  }
}

async function submitProduct() {
  if (!canSubmit.value) {
    session.setFlash('Harga harus lebih dari 0 dan stok minimal 0.', 'error')
    return
  }

  if (!form.image_url) {
    session.setFlash('Silakan unggah foto produk terlebih dahulu.', 'error')
    return
  }

  try {
    await session.api('/products', {
      method: 'POST',
      headers: session.authHeaders(),
      body: JSON.stringify({
        ...form,
        price: Number(form.price),
        stock: Number(form.stock),
      }),
    })

    form.name = ''
    form.description = ''
    form.image_url = ''
    form.price = 0
    form.stock = 1
    if (fileInput.value) fileInput.value.value = ''

    session.setFlash('Produk berhasil ditambahkan.', 'success')
    activeTab.value = 'products'
    await loadMyProducts()
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

async function advanceSellerStatus(item) {
  const nextStatus = nextSellerStatus(item.status)
  if (!nextStatus) {
    return
  }

  try {
    await session.api(`/seller/order-items/${item.id}/status`, {
      method: 'PATCH',
      headers: session.authHeaders(),
      body: JSON.stringify({
        new_status: nextStatus,
        note: 'Update dari halaman toko',
      }),
    })

    session.setFlash('Status pesanan berhasil diperbarui.', 'success')
    await loadSellerOrders()
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

onMounted(() => {
  refreshAll()
})
</script>

<template>
  <main class="dashboard-layout">
    <aside class="panel stack sidebar-menu" style="padding: 12px; position: sticky; top: 100px;">
      <h3 style="padding: 10px 14px; font-size: 1.1rem; color: var(--ink);">SokoFlow Seller</h3>
      <button class="sidebar-item" :class="{ active: activeTab === 'dashboard' }" @click="activeTab = 'dashboard'">
        📊 Ringkasan Dasbor
      </button>
      <button class="sidebar-item" :class="{ active: activeTab === 'products' }" @click="activeTab = 'products'">
        📦 Produk Saya ({{ myProducts.length }})
      </button>
      <button class="sidebar-item" :class="{ active: activeTab === 'add' }" @click="activeTab = 'add'">
        ➕ Tambah Produk
      </button>
      <button class="sidebar-item" :class="{ active: activeTab === 'orders' }" @click="activeTab = 'orders'">
        📑 Pesanan Masuk
      </button>
    </aside>

    <div class="stack" style="gap: 20px;">
      <section class="panel inline" style="justify-content: space-between">
        <div class="stack" style="gap: 6px">
          <h2 v-if="activeTab === 'dashboard'">Dashboard Toko</h2>
          <h2 v-if="activeTab === 'products'">Daftar Produk Aktif</h2>
          <h2 v-if="activeTab === 'add'">Tambah Produk Baru</h2>
          <h2 v-if="activeTab === 'orders'">Kelola Pesanan Pelanggan</h2>
          <p class="subtle">Kelola toko Anda dengan mudah layaknya profesional.</p>
        </div>

        <button class="btn ghost" @click="refreshAll" :disabled="loading">
          {{ loading ? 'Memuat...' : 'Refresh Data' }}
        </button>
      </section>

      <!-- TAB: DASHBOARD -->
      <section v-if="activeTab === 'dashboard'" class="stack">
        <div class="grid three">
          <div class="stat-box">
            <span class="stat-label">Total Produk Aktif</span>
            <span class="stat-value">{{ myProducts.length }}</span>
          </div>
          <div class="stat-box">
            <span class="stat-label">Pesanan Baru (Perlu Diproses)</span>
            <span class="stat-value">{{ sellerOrders.filter(o => o.status === 'menunggu_penjual').length }}</span>
          </div>
          <div class="stat-box">
            <span class="stat-label">Sedang Dikirim</span>
            <span class="stat-value">{{ sellerOrders.filter(o => o.status === 'sedang_dikirim').length }}</span>
          </div>
        </div>

        <div class="table-panel" style="margin-top: 10px;">
          <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>Perlu Diproses Segera ({{ sellerOrders.filter(o => o.status === 'menunggu_penjual').length }})</span>
            <button class="btn ghost" style="padding: 4px 10px; font-size: 0.8rem;" @click="activeTab = 'orders'">Lihat Semua</button>
          </div>
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Info Barang</th>
                  <th>Jumlah</th>
                  <th>Total Harga</th>
                  <th>Aksi Cepat</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in sellerOrders.filter(o => o.status === 'menunggu_penjual').slice(0, 5)" :key="order.id">
                  <td>{{ order.product?.name }}</td>
                  <td>{{ order.quantity }}x</td>
                  <td style="font-weight: 700; color: var(--primary);">{{ formatMoney(order.price * order.quantity) }}</td>
                  <td>
                    <button class="btn accent" style="padding: 6px 12px; font-size: 0.8rem;" @click="advanceSellerStatus(order)">
                      Kemas Barang
                    </button>
                  </td>
                </tr>
                <tr v-if="!sellerOrders.some(o => o.status === 'menunggu_penjual')">
                  <td colspan="4" style="text-align: center; color: var(--muted); padding: 30px;">Hebat! Semua pesanan telah diproses.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB: PRODUCTS -->
      <section v-if="activeTab === 'products'" class="stack">
        <div class="table-panel">
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Info Produk</th>
                  <th>Harga Satuan</th>
                  <th>Stok Tersedia</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in myProducts" :key="item.id">
                  <td style="display: flex; gap: 12px; align-items: center;">
                    <img :src="item.image_url" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #f2d6c4" />
                    <div>
                      <div style="font-weight: 700; color: var(--ink);">{{ item.name }}</div>
                      <div style="font-size: 0.85rem; color: var(--muted); margin-top: 4px;">Deskripsi: {{ item.description.substring(0, 50) }}...</div>
                    </div>
                  </td>
                  <td style="font-weight: 700; color: var(--primary); vertical-align: middle;">{{ formatMoney(item.price) }}</td>
                  <td style="vertical-align: middle;">
                     <span class="badge" :class="item.stock > 0 ? 'ok' : 'warn'">{{ item.stock > 0 ? 'Tersedia' : 'Habis' }}: {{ item.stock }}</span>
                  </td>
                </tr>
                <tr v-if="!myProducts.length">
                  <td colspan="3" style="text-align: center; padding: 40px; color: var(--muted);">
                    Belum ada produk jualan. <br/>
                    <button class="btn primary" style="margin-top: 14px;" @click="activeTab = 'add'">Tambah Produk Sekarang</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB: ADD PRODUCT -->
      <article v-if="activeTab === 'add'" class="panel stack" style="max-width: 600px;">
        <label>
          <span style="font-size: 0.95rem; font-weight: 600; color: var(--ink); margin-bottom: 6px; display: block;">Nama Produk</span>
          <input v-model="form.name" class="input" type="text" placeholder="Contoh: Sepatu Sneakers Pria" required />
        </label>

        <label>
          <span style="font-size: 0.95rem; font-weight: 600; color: var(--ink); margin-bottom: 6px; display: block;">Deskripsi</span>
          <textarea v-model="form.description" class="textarea" placeholder="Jelaskan detail ukuran, bahan, dll..." required />
        </label>

        <label>
          <span style="font-size: 0.95rem; font-weight: 600; color: var(--ink); margin-bottom: 6px; display: block;">Foto Produk</span>
          <input type="file" ref="fileInput" @change="handleImageUpload" class="input" accept="image/*" required :disabled="isUploading" />
          <div v-if="isUploading" style="margin-top: 8px; font-size: 0.85rem; color: var(--primary);">Sedang mengunggah foto...</div>
          <div v-if="form.image_url && !isUploading" style="margin-top: 10px;">
             <img :src="form.image_url" style="max-width: 150px; border-radius: 6px; border: 1px solid var(--line);" />
          </div>
        </label>

        <div class="grid two">
          <label>
            <span style="font-size: 0.95rem; font-weight: 600; color: var(--ink); margin-bottom: 6px; display: block;">Harga (Rp)</span>
            <input v-model.number="form.price" class="input" type="number" min="1" required />
          </label>

          <label>
            <span style="font-size: 0.95rem; font-weight: 600; color: var(--ink); margin-bottom: 6px; display: block;">Stok</span>
            <input v-model.number="form.stock" class="input" type="number" min="0" required />
          </label>
        </div>

        <button class="btn primary" style="margin-top: 10px; font-size: 1.1rem; padding: 12px;" @click="submitProduct" :disabled="!form.name || !form.price">Simpan Produk Baru</button>
      </article>

      <!-- TAB: ORDERS -->
      <section v-if="activeTab === 'orders'" class="stack">
        <div class="table-panel">
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Produk Dipesan</th>
                  <th>Total Biaya</th>
                  <th>Status Pesanan</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(order, i) in sellerOrders" :key="order.id">
                  <td>#{{ String(i + 1).padStart(3, '0') }}</td>
                  <td style="font-weight: 600;">{{ order.product?.name }} <span class="subtle" style="font-weight: 400;">(x{{ order.quantity }})</span></td>
                  <td style="font-weight: 700; color: var(--ink);">{{ formatMoney(order.price * order.quantity) }}</td>
                  <td>
                    <span class="badge" :class="order.status === 'menunggu_penjual' ? 'warn' : 'ok'">
                      {{ statusText(order.status) }}
                    </span>
                  </td>
                  <td>
                    <template v-if="nextSellerStatus(order.status)">
                      <button
                        class="btn ghost"
                        style="padding: 6px 10px; font-size: 0.8rem;"
                        @click="advanceSellerStatus(order)"
                      >
                        {{ nextStatusLabel(order.status) }}
                      </button>
                    </template>
                    <span v-else-if="order.status === 'menunggu_kurir'" class="subtle" style="font-size: 0.85rem">Menunggu Pick-up</span>
                    <span v-else class="subtle" style="font-size: 0.85rem">-</span>
                  </td>
                </tr>
                <tr v-if="!sellerOrders.length">
                  <td colspan="5" style="text-align: center; padding: 40px; color: var(--muted);">Belum ada riwayat pesanan apa pun.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </div>
  </main>
</template>