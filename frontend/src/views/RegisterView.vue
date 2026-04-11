<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { session } from '../stores/session'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  full_name: '',
  username: '',
  email: '',
  password: '',
})

async function submitRegister() {
  loading.value = true
  session.clearFlash()

  try {
    await session.register(form)
    session.setFlash('Register berhasil. Silakan login.', 'success')
    router.push('/login')
  } catch (error) {
    session.setFlash(error.message, 'error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="grid two">
    <section class="panel stack">
      <h2>Register Akun Baru</h2>
      <p class="subtle">Buat akun user untuk mulai belanja dan berjualan.</p>

      <form class="stack" @submit.prevent="submitRegister">
        <label>
          <span>Nama Lengkap</span>
          <input v-model="form.full_name" class="input" type="text" required />
        </label>

        <label>
          <span>Username</span>
          <input v-model="form.username" class="input" type="text" required />
        </label>

        <label>
          <span>Email</span>
          <input v-model="form.email" class="input" type="email" required />
        </label>

        <label>
          <span>Password</span>
          <input v-model="form.password" class="input" type="password" minlength="6" required />
        </label>

        <button class="btn primary" type="submit" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Register' }}
        </button>
      </form>
    </section>

    <section class="panel stack">
      <h3>Setelah Register</h3>
      <p class="subtle">Masuk ke halaman login, lalu lanjut ke dashboard belanja atau penjualan.</p>
      <RouterLink to="/login" class="btn ghost">Ke Login</RouterLink>
    </section>
  </main>
</template>
