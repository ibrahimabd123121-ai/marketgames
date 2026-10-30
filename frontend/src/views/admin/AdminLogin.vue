<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const route = useRoute()

const showPassword = ref(false)
const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  if (route.query.authError === 'verification') {
    errorMessage.value = 'تعذر التحقق من الجلسة. تحقق من اتصال الخادم ثم حاول مجددًا.'
  }
})

const initials = computed(() => {
  const name = email.value.split('@')[0]?.trim()
  return name ? name.slice(0, 2).toUpperCase() : 'AD'
})

async function login() {
  errorMessage.value = ''

  if (!email.value.trim() || !password.value) {
    errorMessage.value = 'يرجى إدخال البريد الإلكتروني وكلمة المرور.'
    return
  }

  loading.value = true

  try {
    const response = await api.post('/admin/login', {
      email: email.value.trim(),
      password: password.value,
    })

    const result = response.data

    if (
      !result.success ||
      !result.data?.token ||
      result.data.user?.role !== 'admin'
    ) {
      errorMessage.value = result.success
        ? 'ليس لديك صلاحية الدخول إلى لوحة الإدارة.'
        : result.message || 'تعذر تسجيل الدخول.'
      return
    }

    localStorage.setItem('admin_token', result.data.token)
    localStorage.setItem('admin_user', JSON.stringify(result.data.user))

    await router.push('/admin/dashboard')
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'تعذر الاتصال بالخادم. تحقق من تشغيل Laravel.'
  } finally {
    loading.value = false
  }
}

function returnToStore() {
  router.push('/')
}
</script>

<template>
  <main class="admin-login">
    <div class="login-art">
      <a class="admin-brand" href="/" @click.prevent="returnToStore">
        <img src="/images/carla.jpeg" alt="" />
        <span>CARLACARD<span class="brand-mark">.</span></span>
      </a>

      <div class="login-promo">
        <span class="promo-label">
          <i class="bi bi-stars"></i>
          ADMIN CONTROL CENTER
        </span>

        <h1>Everything your<br />store needs,<br /><span>in one place.</span></h1>

        <p>
          Manage your marketplace, follow your sales, and keep every order moving.
        </p>

        <div class="promo-stat">
          <div class="promo-avatars" aria-hidden="true">
            <span>JD</span>
            <span>MK</span>
            <span>AL</span>
          </div>

          <div>
            <strong>Built for your team</strong>
            <small>A clearer view of your business</small>
          </div>
        </div>
      </div>

      <div class="login-art-footer">
        <span>© 2026 Carlacard</span>
        <span>ADMIN PORTAL <i class="bi bi-arrow-up-right"></i></span>
      </div>
    </div>

    <div class="login-panel">
      <div class="login-panel-top">
        <span>Already browsing?</span>
        <a href="/" @click.prevent="returnToStore">
          Back to store <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <section class="login-card" aria-labelledby="admin-login-title">
        <div class="login-icon">
          <i class="bi bi-shield-lock"></i>
        </div>

        <span class="login-eyebrow">WELCOME BACK</span>

        <h2 id="admin-login-title" class="sign-in">Sign in to your account</h2>

        <p class="login-subtitle">
          Enter your details to access the admin workspace.
        </p>

        <form @submit.prevent="login">
          <label for="admin-email">Email address</label>

          <div class="login-input-wrap">
            <i class="bi bi-envelope"></i>
            <input
              id="admin-email"
              v-model="email"
              type="email"
              autocomplete="username"
              
              required
            />
          </div>

          <div class="password-label">
            <label for="admin-password">Password</label>
            <a href="#password-help" @click.prevent>Forgot password?</a>
          </div>

          <div class="login-input-wrap">
            <i class="bi bi-lock"></i>

            <input
              id="admin-password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              
              required
            />

            <button
              class="password-toggle"
              type="button"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
              @click="showPassword = !showPassword"
            >
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>

          <label class="remember-option">
            <input type="checkbox" />
            <span>Remember me for 30 days</span>
          </label>


          <p v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </p>


          <button class="sign-in-button" type="submit" :disabled="loading">
            {{ loading ? 'جار تسجيل الدخول...' : 'تسجيل الدخول' }}
             <i class="bi bi-arrow-right"></i>
          </button>
        </form>

        <div class="preview-notice">
          <i class="bi bi-info-circle"></i>
          <span>
            Sign in with an administrator account to access the admin workspace.
          </span>
        </div>
      </section>

      <p class="login-help">
        Need help?
        <a href="mailto:support@carlacard.com">Contact support</a>
      </p>
    </div>
  </main>
</template>
<style scoped>
.admin-login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
  background:
    radial-gradient(circle at top left, #f8efd921 0, transparent 38%),
    linear-gradient(135deg, #faf9f618, #f3efe61e);
  font-family: Inter, Arial, sans-serif;
  color: #29251d;
}

/* Hide the old promotional panel */
.login-art {
  display: none;
}

.login-panel {
  width: 100%;
  max-width: 440px;
  min-height: auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}

.login-panel-top {
  position: absolute;
  bottom: calc(100% + 24px);
  right: 0;
  left: 0;
  display: flex;
  justify-content: center;
  gap: 6px;
  font-size: 13px;
  color: #777064;
}

.login-panel-top a,
.login-help a {
  color: #9a772e;
  font-weight: 600;
  text-decoration: none;
}

.login-panel-top a:hover,
.login-help a:hover {
  color: #75591e;
}

.login-card {
  width: 100%;
  padding: 42px 36px;
  background: rgba(20, 17, 2, 0.882);
  border: 1px solid #dda521;
  border-radius: 18px;
  box-shadow:
    0 18px 50px rgba(79, 62, 26, 0.08),
    0 2px 8px rgba(79, 62, 26, 0.03);
}

.login-icon {
  width: 54px;
  height: 54px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
  border-radius: 15px;
  background: #f8f0df;
  border: 1px solid #eee0bd;
  color: #c5bdae;
  font-size: 24px;
}

.login-eyebrow {
  color: #756523;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
}

.login-card h2 {
  margin: 10px 0;
  font-size: 27px;
  line-height: 1.3;
  font-weight: 700;
  letter-spacing: -0.7px;
}

.login-subtitle {
  margin: 0 0 30px;
  color: #eeece9;
  font-size: 14px;
  line-height: 1.7;
}

.login-card form {
  display: flex;
  flex-direction: column;
}

.login-card form > label:not(.remember-option),
.password-label label {
  margin-bottom: 9px;
  color: #494337;
  font-size: 13px;
  font-weight: 600;
}

.login-input-wrap {
  display: flex;
  align-items: center;
  gap: 11px;
  min-height: 49px;
  margin-bottom: 21px;
  padding: 0 14px;
  background: #0c0202;
  border: 1px solid #e6e1d7;
  border-radius: 9px;
  color: #d7d4cd;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.login-input-wrap:focus-within {
  border-color: #c6a45b;
  box-shadow: 0 0 0 3px rgba(198, 164, 91, 0.13);
}

.login-input-wrap > i {
  font-size: 17px;
}

.login-input-wrap input {
  width: 100%;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  color: #d1cfca;
  font-size: 14px;
}

.login-input-wrap input::placeholder {
  color: #b5b0a6;
}

.password-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.password-label a {
  margin-bottom: 9px;
  color: #9a772e;
  font-size: 12px;
  text-decoration: none;
}

.password-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
  border: none;
  background: transparent;
  color: #8d8577;
  cursor: pointer;
}

.remember-option {
  display: flex;
  align-items: center;
  gap: 9px;
  margin: 0 0 24px;
  color: #70695d;
  font-size: 12px;
  cursor: pointer;
}

.remember-option input {
  accent-color: #b08b3e;
}
.sign-in{
  color: #eae9e5;
}

.sign-in-button {
  width: 100%;
  min-height: 49px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 1px solid #b18b3e;
  border-radius: 9px;
  background: linear-gradient(135deg, #e8e7e6, #d89b1f);
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.sign-in-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(207, 205, 200, 0.2);
}

.preview-notice {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  margin-top: 23px;
  padding: 12px;
  border-radius: 8px;
  background: #faf6ec;
  color: #82765d;
  font-size: 11px;
  line-height: 1.6;
}

.preview-notice i {
  margin-top: 1px;
  color: #ad8b47;
}

.login-help {
  margin: 24px 0 0;
  color: #817b70;
  font-size: 12px;
  text-align: center;
}

@media (max-width: 480px) {
  .admin-login {
    padding: 24px 16px;
  }

  .login-card {
    padding: 32px 23px;
  }

  .login-card h2 {
    font-size: 24px;
  }

  .login-panel-top {
    position: static;
    margin-bottom: 20px;
  }
}
</style>