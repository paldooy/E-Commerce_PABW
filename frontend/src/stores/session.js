import { computed, ref } from 'vue'

const apiBase = ref('http://127.0.0.1:8000')
const token = ref(localStorage.getItem('token') || '')
const me = ref(null)
const wallet = ref(null)
const flash = ref({ type: 'info', text: '' })

const isLoggedIn = computed(() => Boolean(token.value))

function setFlash(text, type = 'info') {
  flash.value = { type, text }
}

function clearFlash() {
  flash.value = { type: 'info', text: '' }
}

function authHeaders(extraHeaders = {}) {
  if (!token.value) {
    return { ...extraHeaders }
  }

  return {
    ...extraHeaders,
    Authorization: `Bearer ${token.value}`,
  }
}

async function api(path, options = {}) {
  const headers = { ...(options.headers || {}) }

  if (options.body && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json'
  }

  const response = await fetch(`${apiBase.value}${path}`, {
    ...options,
    headers,
  })

  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new Error(data.detail || 'Request gagal')
  }

  return data
}

async function loadMe() {
  if (!token.value) {
    me.value = null
    return null
  }

  me.value = await api('/me', {
    headers: authHeaders(),
  })

  try {
      if (me.value && me.value.role === 'user') {
        wallet.value = await api('/me/wallet', { headers: authHeaders() })
      }
  } catch(e) {}

  return me.value
}

async function register(payload) {
  return api('/auth/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

async function login(payload) {
  const result = await api('/auth/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  })

  token.value = result.access_token
  localStorage.setItem('token', token.value)
  await loadMe()

  return result
}

function logout() {
  token.value = ''
  me.value = null
  localStorage.removeItem('token')
}

async function restoreSession() {
  if (!token.value) {
    return
  }

  try {
    await loadMe()
  } catch {
    logout()
  }
}

export const session = {
  apiBase,
  token,
  me,
  wallet,
  flash,
  isLoggedIn,
  setFlash,
  clearFlash,
  authHeaders,
  api,
  loadMe,
  register,
  login,
  logout,
  restoreSession,
}
