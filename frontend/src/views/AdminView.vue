<script setup>
import { onMounted, reactive, ref } from 'vue'
import { session } from '../stores/session'

const users = ref([])
const couriers = ref([])
const loading = ref(false)

const accountForm = reactive({
  role: 'user',
  full_name: '',
  username: '',
  email: '',
  password: '',
})

const walletForm = reactive({
  action: 'add',
  account_id: 0,
  amount: 0,
  description: '',
})

async function loadAccounts() {
  const [userData, courierData] = await Promise.all([
    session.api('/admin/users', { headers: session.authHeaders() }),
    session.api('/admin/couriers', { headers: session.authHeaders() }),
  ])

  users.value = userData
  couriers.value = courierData
}

async function refreshAll() {
  loading.value = true

  try {
    await loadAccounts()
  } catch (error) {
    session.setFlash(error.message, 'error')
  } finally {
    loading.value = false
  }
}

async function createAccount() {
  const endpoint = accountForm.role === 'courier' ? '/admin/couriers' : '/admin/users'

  try {
    await session.api(endpoint, {
      method: 'POST',
      headers: session.authHeaders(),
      body: JSON.stringify({
        full_name: accountForm.full_name,
        username: accountForm.username,
        email: accountForm.email,
        password: accountForm.password,
      }),
    })

    accountForm.full_name = ''
    accountForm.username = ''
    accountForm.email = ''
    accountForm.password = ''

    session.setFlash('Akun berhasil dibuat.', 'success')
    await loadAccounts()
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

async function adjustWallet() {
  const endpoint = walletForm.action === 'add' ? '/admin/wallets/add' : '/admin/wallets/deduct'

  try {
    await session.api(endpoint, {
      method: 'POST',
      headers: session.authHeaders(),
      body: JSON.stringify({
        account_id: Number(walletForm.account_id),
        amount: Number(walletForm.amount),
        description: walletForm.description || null,
      }),
    })

    walletForm.amount = 0
    walletForm.description = ''

    session.setFlash('Penyesuaian saldo berhasil dicatat.', 'success')
  } catch (error) {
    session.setFlash(error.message, 'error')
  }
}

onMounted(() => {
  refreshAll()
})
</script>

<template>
  <main class="stack">
    <section class="panel inline" style="justify-content: space-between">
      <div class="stack" style="gap: 6px">
        <h2>Halaman Admin</h2>
        <p class="subtle">Kelola akun user/kurir dan adjust saldo wallet secara manual.</p>
      </div>

      <button class="btn ghost" @click="refreshAll" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Refresh Data' }}
      </button>
    </section>

    <section class="grid two">
      <article class="panel stack">
        <h3>Buat Akun User/Kurir</h3>

        <label>
          <span>Role akun</span>
          <select v-model="accountForm.role" class="select">
            <option value="user">User</option>
            <option value="courier">Courier</option>
          </select>
        </label>

        <label>
          <span>Nama lengkap</span>
          <input v-model="accountForm.full_name" class="input" type="text" required />
        </label>

        <label>
          <span>Username</span>
          <input v-model="accountForm.username" class="input" type="text" required />
        </label>

        <label>
          <span>Email</span>
          <input v-model="accountForm.email" class="input" type="email" required />
        </label>

        <label>
          <span>Password</span>
          <input v-model="accountForm.password" class="input" type="password" minlength="6" required />
        </label>

        <button class="btn primary" @click="createAccount">Simpan Akun</button>
      </article>

      <article class="panel stack">
        <h3>Adjust Wallet</h3>

        <label>
          <span>Tipe transaksi</span>
          <select v-model="walletForm.action" class="select">
            <option value="add">Tambah saldo</option>
            <option value="deduct">Kurangi saldo</option>
          </select>
        </label>

        <label>
          <span>Account ID</span>
          <input v-model.number="walletForm.account_id" class="input" type="number" min="1" />
        </label>

        <label>
          <span>Amount</span>
          <input v-model.number="walletForm.amount" class="input" type="number" min="1" />
        </label>

        <label>
          <span>Deskripsi</span>
          <textarea v-model="walletForm.description" class="textarea" />
        </label>

        <button class="btn accent" @click="adjustWallet">Simpan Penyesuaian</button>
      </article>
    </section>

    <section class="grid two">
      <article class="panel stack">
        <h3>Daftar User</h3>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Aktif</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in users" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.username }}</td>
                <td>{{ item.email }}</td>
                <td>{{ item.is_active ? 'Ya' : 'Tidak' }}</td>
              </tr>
              <tr v-if="users.length === 0">
                <td colspan="4" class="subtle">Belum ada user.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <article class="panel stack">
        <h3>Daftar Kurir</h3>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Aktif</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in couriers" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.username }}</td>
                <td>{{ item.email }}</td>
                <td>{{ item.is_active ? 'Ya' : 'Tidak' }}</td>
              </tr>
              <tr v-if="couriers.length === 0">
                <td colspan="4" class="subtle">Belum ada kurir.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </main>
</template>
