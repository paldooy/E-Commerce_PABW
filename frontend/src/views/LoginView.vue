<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { session } from '../stores/session'

const route = useRoute()
const router = useRouter()
const loading = ref(false)

const form = reactive({
  username: '',
  password: '',
})

async function submitLogin() {
  loading.value = true
  session.clearFlash()

  try {
    await session.login(form)
    session.setFlash('Login berhasil.', 'success')

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : ''
    if (redirect) {
      router.push(redirect)
      return
    }

    if (session.me.value?.role === 'user') {
      router.push('/dashboard')
      return
    }

    if (session.me.value?.role === 'courier') {
      router.push('/courier')
      return
    }

    if (session.me.value?.role === 'admin') {
      router.push('/admin')
      return
    }

    router.push('/')
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
      <h2>Login</h2>
      <p class="subtle">Masuk untuk akses dashboard sesuai role.</p>

      <form class="stack" @submit.prevent="submitLogin">
        <label>
          <span>Username</span>
          <input v-model="form.username" class="input" type="text" required />
        </label>

        <label>
          <span>Password</span>
          <input v-model="form.password" class="input" type="password" required />
        </label>

        <button class="btn primary" type="submit" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Login' }}
        </button>
      </form>
    </section>

    <section class="panel stack">
      <h3>Akun Admin Default</h3>
      <p class="subtle">username: admin</p>
      <p class="subtle">password: admin123</p>
      <RouterLink to="/register" class="btn ghost">Belum punya akun? Register</RouterLink>
    </section>
  </main>
</template>
