<template>
  <main class="stack" style="max-width: 1200px; margin: 0 auto;">
    <div class="panel inline" style="justify-content: space-between">
      <div class="stack" style="gap: 6px">
        <h2>Belanja Kebutuhan Harian</h2>
        <p class="subtle">Temukan berbagai produk pilihan dari mitra SembakoMart dan toko lainnya.</p>
      </div>

      <button class="btn ghost" @click="refreshAll" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Refresh Data' }}
      </button>
    </div>

    <!-- CATEGORY PILLS -->
    <div class="category-row" style="margin-top: 10px; margin-bottom: 20px;">
      <button
        class="category-pill"
        :class="{ active: selectedCategory === '' }"
        @click="setCategory('')"
      >
        Semua Kategori
      </button>
      <button
        v-for="cat in categoryPills"
        :key="cat"
        class="category-pill"
        :class="{ active: selectedCategory === cat }"
        @click="setCategory(cat)"
      >
        #{{ cat }}
      </button>
    </div>

    <!-- PRODUCT CATALOG -->
    <div class="product-grid">
      <article v-for="item in filteredProducts" :key="item.id" class="product-card stack" style="gap: 0; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
        <div>
           <img :src="item.image_url" :alt="item.name" class="product-image" />
           <div class="product-body stack">
             <h4 style="font-size: 1rem; color: var(--ink); line-height: 1.3">{{ item.name }}</h4>
             <p class="subtle" style="font-size: 0.85rem">{{ item.description.substring(0, 40) }}...</p>
             <div class="price" style="font-size: 1.15rem;">{{ formatMoney(item.price) }}</div>
             <div style="font-size: 0.8rem; color: var(--muted); margin-top: 4px;">Stok Tersedia: {{ item.stock }}</div>
           </div>
        </div>

        <div style="padding: 12px; border-top: 1px solid #f2d6c4; display: flex; gap: 8px;">
          <input v-model.number="productQty[item.id]" type="number" min="1" class="input" style="width: 65px; padding: 6px 10px;" />
          <button class="btn primary" style="flex: 1; padding: 6px 10px;" @click="addToCart(item.id)">+ Keranjang</button>
        </div>
      </article>

      <div v-if="!filteredProducts.length" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--muted); background: white; border-radius: 12px; border: 1px solid var(--line);">
        <div style="font-size: 3rem; margin-bottom: 14px;">🔍</div>
        Waduh, barang "{{ keyword }}" tidak ditemukan.<br/>Coba cari dengan kata kunci lain.
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { session } from '../stores/session'

const me = session.me
const route = useRoute()
const router = useRouter()
const products = ref([])
const keyword = ref(route.query.q || '')
const selectedCategory = ref('')
const productQty = ref({})
const loading = ref(false)

const formatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
})

const categoryPills = computed(() => {
  const tokens = new Set()
  for (const item of products.value) {
    const words = item.name.toLowerCase().split(/[^a-z0-9]+/)
    for (const word of words) {
      if (word.length >= 5) {
        tokens.add(word)
      }
      if (tokens.size >= 8) break
    }
    if (tokens.size >= 8) break
  }
  return Array.from(tokens)
})

const filteredProducts = computed(() => {
  const search = keyword.value.trim().toLowerCase()
  const category = selectedCategory.value.trim().toLowerCase()

  return products.value.filter((item) => {
    // Sembunyikan barang dagangan sendiri jika login
    if (me.value && item.seller_id === me.value.id) {
       return false
    }

    const normalizedName = item.name.toLowerCase()

    if (category && !normalizedName.includes(category)) {
      return false
    }

    if (!search) {
      return true
    }

    return normalizedName.includes(search)
  })
})

function setCategory(category) {
  selectedCategory.value = category
}

function formatMoney(amount) {
  return formatter.format(Number(amount || 0))
}

async function loadProducts() {
  products.value = await session.api('/products')
  for (const product of products.value) {
    if (!productQty.value[product.id]) {
      productQty.value[product.id] = 1
    }
  }
}

async function refreshAll() {
  loading.value = true
  try {
    await Promise.all([loadProducts(), session.loadMe()])
  } catch (error) {
    session.setFlash(error.message, 'error')
  } finally {
    loading.value = false
  }
}

async function addToCart(productId) {
  const quantity = Number(productQty.value[productId] || 1)
  if (quantity < 1) {
    session.setFlash('Jumlah minimal 1.', 'error')
    return
  }

  try {
    await session.api('/cart/items', {
      method: 'POST',
      headers: session.authHeaders(),
      body: JSON.stringify({
        product_id: productId,
        quantity,
      }),
    })
    session.setFlash('Produk dimasukkan ke keranjang.', 'success')
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

watch(() => route.query.q, (newQ) => {
  keyword.value = newQ || ''
})

onMounted(() => {
  refreshAll()
})
</script>
