<template>
  <main class="stack" style="max-width: 800px; margin: 0 auto;">
    <div class="panel inline" style="justify-content: space-between">
      <div class="stack" style="gap: 6px">
        <h2>Keranjang Belanja</h2>
        <p class="subtle">Periksa dan kelola barang yang akan kamu beli.</p>
      </div>

      <button class="btn ghost" @click="refreshAll" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Refresh Data' }}
      </button>
    </div>

    <div class="panel stack cart-panel">
      <div class="mini-list" v-if="cartItems.length > 0">
        <div v-for="row in cartItems" :key="row.id" class="mini-item stack" style="border: 1px solid #f1d7c7; padding: 12px; border-radius: 8px; background: #fffdfb;">
          <div style="font-weight: 700; color: var(--ink);">{{ row.product?.name }}</div>
          <div style="display: flex; justify-content: space-between; align-items: center;">
              <span class="price">{{ formatMoney(row.product?.price) }} / item</span>
              <span style="font-weight: 700; color: var(--primary)">Total: {{ formatMoney(row.subtotal) }}</span>
          </div>
          <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px;">
            <input v-model.number="cartQty[row.id]" type="number" min="1" class="input" style="width: 70px; padding: 6px 10px;" />
            <button class="btn accent" style="padding: 6px 10px;" @click="saveCartItem(row)">Update</button>
            <button class="btn warn" style="padding: 6px 10px;" @click="removeCartItem(row.id)">🗑 Hapus</button>
          </div>
        </div>
        <div style="margin-top: 14px; padding-top: 14px; border-top: 2px dashed #f2d6c4;">
          <div style="display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 800; margin-bottom: 14px;">
            <span>Total Harus Dibayar</span>
            <span style="color: var(--primary-strong);">{{ formatMoney(cartTotal) }}</span>
          </div>
          <button class="btn primary" style="width: 100%; font-size: 1.1rem; padding: 12px;" @click="runCheckout">
            Bayar Sekarang
          </button>
        </div>
      </div>
      <div v-else style="text-align: center; padding: 40px; color: var(--muted);">
        <div style="font-size: 3rem; margin-bottom: 10px;">🛒</div>
        Keranjangmu masih kosong.<br/>
        <RouterLink to="/dashboard" class="btn ghost" style="display: inline-block; margin-top: 14px;">Mulai Belanja</RouterLink>
      </div>
    </div>
  </main>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { session } from '../stores/session'
import { useRouter } from 'vue-router'

const cartItems = ref([])
const cartQty = ref({})
const loading = ref(false)
const router = useRouter()

const formatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
})

function formatMoney(amount) {
  return formatter.format(Number(amount || 0))
}

const cartTotal = computed(() =>
  cartItems.value.reduce((acc, row) => acc + Number(row.subtotal || 0), 0)
)

async function loadCart() {
  const result = await session.api('/cart', {
    headers: session.authHeaders(),
  })
  cartItems.value = result.items || []
  if (cartItems.value.length === 0 && Array.isArray(result)) {
      cartItems.value = result
  }

  for (const item of cartItems.value) {
    cartQty.value[item.id] = item.quantity
  }
}

async function refreshAll() {
  loading.value = true
  try {
    await loadCart()
    await session.loadMe()
  } catch (error) {
    session.setFlash(error.message, 'error')
  } finally {
    loading.value = false
  }
}

async function saveCartItem(item) {
  const quantity = Number(cartQty.value[item.id] || item.quantity)
  if (quantity < 1) {
    session.setFlash('Jumlah minimal 1.', 'error')
    return
  }

  try {
    await session.api(`/cart/items/${item.id}`, {
      method: 'PATCH',
      headers: session.authHeaders(),
      body: JSON.stringify({
        product_id: item.product_id,
        quantity,
      }),
    })
    await loadCart()
    session.setFlash('Keranjang diperbarui.', 'success')
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

async function removeCartItem(itemId) {
  try {
    await session.api(`/cart/items/${itemId}`, {
      method: 'DELETE',
      headers: session.authHeaders(),
    })
    await loadCart()
    session.setFlash('Item keranjang dihapus.', 'success')
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

async function runCheckout() {
  try {
    const result = await session.api('/checkout', {
      method: 'POST',
      headers: session.authHeaders(),
    })
    session.setFlash(`Checkout berhasil. Order: ${result.order_number}`, 'success')
    await refreshAll()
    router.push('/orders')
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

onMounted(() => {
  refreshAll()
})
</script>
