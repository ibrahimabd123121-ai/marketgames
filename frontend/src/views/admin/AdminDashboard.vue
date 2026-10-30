<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import CategoriesView from './CategoriesView.vue'
import ProductsView from './ProductsView.vue'


const router = useRouter()
const email = ref('')
const activeSection = ref('Overview')

const initials = computed(() => {
  const name = email.value.split('@')[0]?.trim()
  return name ? name.slice(0, 2).toUpperCase() : 'AD'
})

onMounted(() => {
  try {
    const user = JSON.parse(localStorage.getItem('admin_user') || '{}')
    email.value = user.email || ''
  } catch (error) {
    console.error('Unable to read admin profile:', error)
  }
})

const metrics = [
  { label: 'Total revenue', value: '$48,294', change: '+12.8%', icon: 'bi-currency-dollar', tone: 'violet' },
  { label: 'Total orders', value: '1,284', change: '+8.2%', icon: 'bi-bag-check', tone: 'blue' },
  { label: 'Products', value: '356', change: '+4.6%', icon: 'bi-box-seam', tone: 'orange' },
  { label: 'Customers', value: '8,549', change: '+16.4%', icon: 'bi-people', tone: 'green' },
]

const sales = [
  { day: 'Mon', height: 38 },
  { day: 'Tue', height: 56 },
  { day: 'Wed', height: 45 },
  { day: 'Thu', height: 73 },
  { day: 'Fri', height: 61 },
  { day: 'Sat', height: 88 },
  { day: 'Sun', height: 68 },
]

const orders = [
  { id: '#MG-2048', customer: 'Sophia Carter', initials: 'SC', product: 'PlayStation Gift Card', date: 'Oct 23, 2026', amount: '$50.00', status: 'Completed' },
  { id: '#MG-2047', customer: 'Noah Williams', initials: 'NW', product: 'Xbox Game Pass Ultimate', date: 'Oct 23, 2026', amount: '$16.99', status: 'Processing' },
  { id: '#MG-2046', customer: 'Olivia Martinez', initials: 'OM', product: 'Steam Wallet Code', date: 'Oct 22, 2026', amount: '$25.00', status: 'Completed' },
  { id: '#MG-2045', customer: 'Liam Thompson', initials: 'LT', product: 'Nintendo eShop Card', date: 'Oct 22, 2026', amount: '$35.00', status: 'Pending' },
]

async function signOut() {
  try {
    await api.post('/admin/logout')
  } catch (error) {
    console.error('Unable to revoke admin session:', error)
  } finally {
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_user')
    await router.replace('/admin/login')
  }
}

function returnToStore() {
  router.push('/')
}
</script>

<template>
  <main class="admin-dashboard">
    <aside class="admin-sidebar">
      <a class="dashboard-brand" href="/" @click.prevent="returnToStore">
        <img src="/images/carla.jpeg" alt="" />
        <span>CARLACARD<span>.</span><small>ADMIN CONSOLE</small></span>
      </a>

      <span class="sidebar-label">WORKSPACE</span>

      <nav class="sidebar-nav" aria-label="Admin navigation">
        <button
          v-for="item in [
            { name: 'Overview', icon: 'bi-grid-1x2' },
            { name: 'Orders', icon: 'bi-bag' },
            { name: 'Products', icon: 'bi-box-seam' },
            { name: 'Categories', icon: 'bi-tags' },
            { name: 'Customers', icon: 'bi-people' },
          ]"
          :key="item.name"
          type="button"
          :class="{ active: activeSection === item.name }"
          @click="activeSection = item.name"
        >
          <i :class="`bi ${item.icon}`"></i>
          <span>{{ item.name }}</span>
          <span v-if="item.name === 'Orders'" class="nav-count">8</span>
        </button>
      </nav>

      <div class="sidebar-bottom">

        <button class="sidebar-store-link" type="button" @click="returnToStore">
          <i class="bi bi-box-arrow-up-right"></i> Visit storefront
        </button>

        <button class="sidebar-profile" type="button" @click="signOut">
          <span class="profile-avatar">{{ initials }}</span>
          <span class="profile-copy">
            <strong>{{ email.split('@')[0] || 'Admin' }}</strong>
            <small>Administrator</small>
          </span>
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>
    </aside>

    <section class="dashboard-main">
      <header class="dashboard-topbar">
        <div class="breadcrumb">
          <span>Workspace</span>
          <i class="bi bi-chevron-right"></i>
          <strong>{{ activeSection }}</strong>
        </div>

        <div class="topbar-actions">
          <div class="dashboard-search">
          <input class="dashboard-search" type="search" placeholder="Search anything..." aria-label="Search dashboard">
            <i class="bi bi-search"></i>
          </div>

          <button class="topbar-icon" type="button" aria-label="Help">
            <i class="bi bi-question-circle"></i>
          </button>

          <button class="topbar-icon notification-button" type="button" aria-label="Notifications">
            <i class="bi bi-bell"></i><span></span>
          </button>

          <span class="topbar-avatar">{{ initials }}</span>
        </div>
      </header>

      <div class="dashboard-content">
        <div class="dashboard-heading">
          <div>
            <span class="dashboard-date">
              <i class="bi bi-calendar3"></i> Friday, October 23, 2026
            </span>

            <h1>
              {{ activeSection === 'Overview' ? 'Good morning' : activeSection }}
              <span v-if="activeSection === 'Overview'">, {{ email.split('@')[0] || 'Admin' }} 👋</span>
            </h1>

            <p>
              {{ activeSection === 'Overview'
                ? 'Here’s what’s happening with your store today.'
                : `A quick look at your ${activeSection.toLowerCase()} activity.` }}
            </p>
          </div>

          <button class="date-filter" type="button">
            <i class="bi bi-calendar4"></i> Last 7 days <i class="bi bi-chevron-down"></i>
          </button>
        </div>
        <categories-view v-if="activeSection === 'Categories'" />
        <products-view v-else-if="activeSection === 'Products'" />
        <div v-else class="metrics-grid" >
          <article v-for="metric in metrics" :key="metric.label" class="metric-card">
            <div class="metric-top">
              <span>{{ metric.label }}</span>
              <span class="metric-icon" :class="metric.tone">
                <i :class="`bi ${metric.icon}`"></i>
              </span>
            </div>

            <strong class="metric-value">{{ metric.value }}</strong>

            <div class="metric-change">
              <span><i class="bi bi-arrow-up-right"></i> {{ metric.change }}</span>
              <small>vs last week</small>
            </div>
          </article>
        </div>

        

        <section v-if="activeSection !=='Categories'" class="dashboard-card orders-card">
          <div class="card-heading orders-heading">
            <div>
              <h2>Recent orders</h2>
              <p>Keep track of your latest transactions.</p>
            </div>
            <button type="button" class="view-all" @click="activeSection = 'Orders'">
              View all orders <i class="bi bi-arrow-right"></i>
            </button>
          </div>

          <div class="orders-table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ORDER</th><th>CUSTOMER</th><th>PRODUCT</th>
                  <th>DATE</th><th>AMOUNT</th><th>STATUS</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td class="order-id">{{ order.id }}</td>
                  <td><span class="customer-avatar">{{ order.initials }}</span>{{ order.customer }}</td>
                  <td>{{ order.product }}</td>
                  <td>{{ order.date }}</td>
                  <td class="order-amount">{{ order.amount }}</td>
                  <td>
                    <span class="order-status" :class="order.status.toLowerCase()">
                      {{ order.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <p class="dashboard-disclaimer">
          <i class="bi bi-info-circle"></i>
          Preview dashboard with sample data. Sign-in is not connected to an authentication service.
        </p>
      </div>
    </section>
  </main>
</template>
<style scoped>
:global(*) {
  box-sizing: border-box;
}

:global(body) {
  margin: 0;
  background: #f3eee4;
  color: #30271c;
  font-family: Inter, "Segoe UI", Arial, sans-serif;
}

.admin-dashboard {
  --gold: #b58b3d;
  --gold-dark: #8d6728;
  --gold-light: #ead7ad;
  --cream: #f5efe3;
  --panel: #fffdf8;
  --muted: #887f70;
  --border: #e9e0d0;
  display: flex;
  min-height: 100vh;
  background:
    radial-gradient(ellipse at 90% 0%, #f7e9c9 0, transparent 30%),
    linear-gradient(135deg, #f3eee4, #eee5d5);
  color: #30271c;
  font-size: 13px;
}

.admin-dashboard button,
.admin-dashboard input {
  font: inherit;
}

.admin-dashboard button {
  cursor: pointer;
}

.admin-sidebar {
  width: 250px;
  flex-shrink: 0;
  min-height: 100vh;
  padding: 28px 22px 20px;
  display: flex;
  flex-direction: column;
  background: rgba(250, 246, 237, 0.88);
  border-right: 1px solid #e7dece;
}

.dashboard-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0 0 42px 4px;
  color: #79571f;
  text-decoration: none;
  font-size: 20px;
  font-weight: 750;
  letter-spacing: 0.5px;
}

.dashboard-brand img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
}

.dashboard-brand > span > span {
  color: #bd954a;
}

.dashboard-brand small {
  display: block;
  margin-top: 5px;
  color: #93836a;
  font-size: 9px;
  font-weight: 600;
  letter-spacing: 2px;
}

.sidebar-label {
  margin: 0 0 14px 8px;
  color: #9c907d;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1.5px;
}

.sidebar-nav {
  display: grid;
  gap: 8px;
}

.sidebar-nav button {
  min-height: 48px;
  padding: 0 14px;
  display: flex;
  align-items: center;
  gap: 13px;
  border: 1px solid transparent;
  border-radius: 11px;
  background: transparent;
  color: #5d5140;
  text-align: left;
  transition: background 0.2s, color 0.2s;
}

.sidebar-nav button > i {
  width: 18px;
  font-size: 17px;
}

.sidebar-nav button:hover {
  background: #f2e8d5;
}

.sidebar-nav button.active {
  background: linear-gradient(110deg, #ead7ad, #dfc18a);
  border-color: #e2c994;
  color: #75511c;
  font-weight: 700;
}

.nav-count {
  width: 22px;
  height: 22px;
  display: grid;
  place-items: center;
  margin-left: auto;
  border-radius: 50%;
  background: #ae8335;
  color: white;
  font-size: 11px;
}

.sidebar-bottom {
  margin-top: auto;
}

.sidebar-tip {
  margin: 35px 0 25px;
  padding: 18px 15px;
  border: 1px solid #ebdfc6;
  border-radius: 14px;
  background: linear-gradient(145deg, #f6ecda, #f0e3cc);
}

.sidebar-tip > i {
  margin-right: 7px;
  color: #ba8c35;
  font-size: 17px;
}

.sidebar-tip > strong {
  color: #815c23;
  font-size: 13px;
}

.sidebar-tip p {
  margin: 13px 0;
  color: #81735e;
  line-height: 1.7;
}

.sidebar-tip button {
  padding: 10px 13px;
  border: 0;
  border-radius: 8px;
  background: linear-gradient(135deg, #c6a15a, #a77c2d);
  color: white;
  font-size: 12px;
  font-weight: 650;
}

.sidebar-store-link {
  width: 100%;
  padding: 17px 8px;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 0;
  border-top: 1px solid #e7dece;
  background: transparent;
  color: #655744;
  text-align: left;
}

.sidebar-profile {
  width: 100%;
  margin-top: 15px;
  padding: 17px 0;
  display: flex;
  align-items: center;
  gap: 11px;
  border: 0;
  border-top: 1px solid #e7dece;
  background: transparent;
  color: #4d4131;
  text-align: left;
}

.profile-avatar,
.topbar-avatar,
.customer-avatar {
  display: inline-grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 50%;
  background: linear-gradient(145deg, #d6b774, #a77b2c);
  color: white;
  font-weight: 700;
}

.profile-avatar {
  width: 42px;
  height: 42px;
}

.profile-copy {
  min-width: 0;
  display: grid;
  gap: 5px;
}

.profile-copy strong {
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-copy small {
  color: #918574;
}

.sidebar-profile > i:last-child {
  margin-left: auto;
}

.dashboard-main {
  flex: 1;
  min-width: 0;
}

.dashboard-topbar {
  min-height: 84px;
  padding: 18px 35px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  border-bottom: 1px solid rgba(223, 211, 190, 0.8);
  background: rgba(255, 252, 245, 0.38);
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #968975;
}

.breadcrumb i {
  font-size: 10px;
}

.breadcrumb strong {
  color: #57452c;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.dashboard-search {
  width: min(390px, 35vw);
  min-height: 44px;
  padding: 0 13px;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid #e9dfce;
  border-radius: 11px;
  background: rgba(255, 253, 248, 0.9);
  color: #a19786;
  
}

.dashboard-search input {
  flex: 1;
  width: 100%;
  border: 0;
  outline: 0;
  background: transparent;
  color: #443729;
}

.dashboard-search kbd {
  padding: 4px 6px;
  border: 1px solid #e8dfd0;
  border-radius: 5px;
  font-size: 10px;
}

.topbar-icon {
  position: relative;
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: #7c6540;
  font-size: 18px;
}

.topbar-icon:hover {
  background: #f0e4cf;
}

.notification-button > span {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #c59a44;
}

.topbar-avatar {
  width: 40px;
  height: 40px;
}

.dashboard-content {
  max-width: 1800px;
  margin: 0 auto;
  padding: 32px 35px 24px;
}

.dashboard-heading {
  margin-bottom: 23px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.dashboard-date {
  color: #856b43;
  font-size: 12px;
}

.dashboard-date i {
  margin-right: 6px;
}

.dashboard-heading h1 {
  margin: 12px 0 7px;
  color: #30251a;
  font-size: clamp(25px, 2.2vw, 32px);
  font-weight: 750;
  letter-spacing: -0.8px;
}

.dashboard-heading p {
  margin: 0;
  color: #877b69;
  font-size: 13px;
}

.date-filter {
  min-height: 43px;
  padding: 0 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid #e4d8c2;
  border-radius: 10px;
  background: rgba(255, 253, 248, 0.75);
  color: #695437;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.metric-card,
.dashboard-card {
  border: 1px solid rgba(231, 221, 204, 0.9);
  border-radius: 14px;
  background: rgba(255, 253, 248, 0.88);
  box-shadow: 0 5px 18px rgba(90, 67, 29, 0.035);
}

.metric-card {
  position: relative;
  overflow: hidden;
  min-width: 0;
  padding: 20px;
}

.metric-card::after {
  content: "";
  position: absolute;
  right: -12px;
  bottom: -30px;
  width: 125px;
  height: 90px;
  border-radius: 60% 40% 0 0;
  background: linear-gradient(135deg, rgba(226, 204, 159, 0.05), rgba(222, 196, 144, 0.2));
  transform: rotate(-10deg);
  pointer-events: none;
}

.metric-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  color: #796b57;
  font-size: 12px;
}

.metric-icon,
.activity-icon {
  display: inline-grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 50%;
}

.metric-icon {
  width: 43px;
  height: 43px;
  background: #f1e4ca;
  color: #8c6323;
  font-size: 18px;
}

.metric-icon.blue {
  background: #e3ebf5;
  color: #5779a4;
}

.metric-icon.orange {
  background: #f8e7d2;
  color: #b57c38;
}

.metric-icon.green {
  background: #e2eddf;
  color: #598451;
}

.metric-value {
  display: block;
  margin: 13px 0 10px;
  color: #34271c;
  font-size: clamp(23px, 2vw, 29px);
  font-weight: 750;
  letter-spacing: -0.6px;
}

.metric-change {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 7px;
  font-size: 11px;
}

.metric-change > span {
  color: #248252;
  font-weight: 700;
}

.metric-change small {
  color: #978a77;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.35fr) minmax(310px, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

.dashboard-card {
  min-width: 0;
  padding: 21px;
}

.card-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
}

.card-heading h2 {
  margin: 0 0 6px;
  color: #382b1f;
  font-size: 15px;
  font-weight: 750;
}

.card-heading p {
  margin: 0;
  color: #938674;
  font-size: 11px;
}

.card-menu {
  width: 34px;
  height: 34px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: #8a7b66;
  font-size: 19px;
}

.card-menu:hover {
  background: #f3ead9;
}

.sales-summary {
  margin: 22px 0 16px;
  display: flex;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 10px;
}

.sales-summary > strong {
  color: #392b1e;
  font-size: 24px;
}

.sales-summary > span {
  color: #238354;
  font-size: 12px;
  font-weight: 700;
}

.sales-summary > small {
  color: #9b8e7c;
  font-size: 11px;
}

.chart {
  position: relative;
  height: 225px;
  padding: 8px 0 28px 42px;
}

.chart-guides {
  position: absolute;
  inset: 8px 0 28px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  pointer-events: none;
}

.chart-guides span {
  position: relative;
  color: #8e8372;
  font-size: 11px;
}

.chart-guides span::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 42px;
  right: 0;
  border-top: 1px solid #eee6d9;
}

.chart-bars {
  position: relative;
  z-index: 1;
  height: 100%;
  display: flex;
  align-items: stretch;
  justify-content: space-around;
  gap: 12px;
  border-bottom: 1px solid #e8decd;
}

.chart-column {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}

.chart-bar {
  width: min(38px, 75%);
  min-height: 4px;
  border-radius: 5px 5px 0 0;
  background: linear-gradient(180deg, #e4c98e, #c9a35b);
  transition: opacity 0.2s;
}

.chart-bar:hover {
  opacity: 0.75;
}

.chart-column > span {
  position: absolute;
  bottom: -22px;
  color: #786b58;
  font-size: 10px;
}

.activity-card {
  display: flex;
  flex-direction: column;
}

.activity-card .card-heading {
  margin-bottom: 15px;
}

.activity-stat {
  min-height: 77px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid #eee5d7;
}

.activity-icon {
  width: 43px;
  height: 43px;
  background: #eee4f8;
  color: #8d6cba;
  font-size: 18px;
}

.activity-icon.orange {
  background: #fae5d0;
  color: #b77938;
}

.activity-icon.green {
  background: #dfeddf;
  color: #4f895b;
}

.activity-stat > div {
  min-width: 0;
  display: grid;
  gap: 6px;
}

.activity-stat > div strong {
  color: #44372a;
  font-size: 12px;
}

.activity-stat > div small {
  color: #958877;
  font-size: 10px;
}

.activity-stat > b {
  margin-left: auto;
  display: grid;
  gap: 5px;
  color: #38291d;
  font-size: 16px;
  text-align: right;
}

.activity-stat > b small {
  color: #278252;
  font-size: 10px;
}

.activity-footer {
  margin-top: auto;
  padding: 14px 11px 0;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 10px;
  color: #6f6453;
  font-size: 11px;
}

.activity-footer > span:last-child {
  color: #318256;
}

.activity-footer > span:last-child i {
  display: inline-block;
  width: 7px;
  height: 7px;
  margin-right: 5px;
  border-radius: 50%;
  background: #4eaa72;
}

.orders-card {
  padding: 21px 18px 10px;
}

.orders-heading {
  margin-bottom: 18px;
}

.view-all {
  padding: 9px 12px;
  border: 1px solid #dfd0b6;
  border-radius: 8px;
  background: #fffdf8;
  color: #72562b;
  font-size: 11px;
}

.view-all:hover {
  background: #f5ead5;
}

.orders-table-wrap {
  width: 100%;
  overflow-x: auto;
}

.orders-table-wrap table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  white-space: nowrap;
}

.orders-table-wrap thead {
  background: #f4eddf;
}

.orders-table-wrap th {
  padding: 12px 10px;
  color: #8b7c66;
  font-size: 9px;
  font-weight: 750;
  letter-spacing: 0.6px;
}

.orders-table-wrap td {
  padding: 12px 10px;
  border-bottom: 1px solid #eee6d9;
  color: #655746;
  font-size: 11px;
}

.orders-table-wrap tbody tr:last-child td {
  border-bottom: 0;
}

.orders-table-wrap tbody tr:hover {
  background: #fcf7ec;
}

.order-id,
.order-amount {
  color: #3e3023 !important;
  font-weight: 700;
}

.customer-avatar {
  width: 27px;
  height: 27px;
  margin-right: 8px;
  font-size: 9px;
  vertical-align: middle;
}

.order-status {
  display: inline-block;
  padding: 5px 9px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 650;
}

.order-status.completed {
  background: #dceede;
  color: #267747;
}

.order-status.processing {
  background: #dce8fb;
  color: #3764a3;
}

.order-status.pending {
  background: #fae8bd;
  color: #94631b;
}

.dashboard-disclaimer {
  margin: 17px 3px 0;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #9a8e7c;
  font-size: 10px;
}

.dashboard-disclaimer i {
  color: #a99165;
}

@media (min-width: 1500px) {
  .dashboard-content {
    padding: 38px 45px 28px;
  }

  .metric-card {
    padding: 24px;
  }
}

@media (max-width: 1150px) {
  .admin-sidebar {
    width: 215px;
    padding-inline: 16px;
  }

  .dashboard-content {
    padding: 26px 22px;
  }

  .dashboard-topbar {
    padding-inline: 22px;
  }

  .metrics-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .admin-dashboard {
    display: block;
  }

  .admin-sidebar {
    width: 100%;
    min-height: auto;
    padding: 15px;
    border-right: 0;
    border-bottom: 1px solid #e7dece;
  }

  .dashboard-brand {
    margin: 0 0 17px;
  }

  .sidebar-label,
  .sidebar-bottom {
    display: none;
  }

  .sidebar-nav {
    display: flex;
    gap: 7px;
    overflow-x: auto;
  }

  .sidebar-nav button {
    flex: 0 0 auto;
    min-height: 41px;
    padding: 0 12px;
  }

  .dashboard-topbar {
    min-height: 65px;
    padding: 12px 16px;
  }

  .breadcrumb {
    display: none;
  }

  .topbar-actions {
    width: 100%;
    gap: 8px;
  }

  .dashboard-search {
    width: auto;
    flex: 1;
  }

  .dashboard-search kbd {
    display: none;
  }

  .dashboard-content {
    padding: 22px 14px;
  }

  .dashboard-heading {
    align-items: flex-start;
  }

  .dashboard-heading h1 {
    font-size: 24px;
  }

  .date-filter {
    padding: 0 9px;
    gap: 6px;
    font-size: 10px;
  }

  .metrics-grid {
    gap: 10px;
  }

  .metric-card {
    padding: 14px;
  }

  .metric-top {
    align-items: flex-start;
    font-size: 11px;
  }

  .metric-icon {
    width: 34px;
    height: 34px;
    font-size: 15px;
  }

  .metric-value {
    font-size: 23px;
  }

  .dashboard-card {
    padding: 16px 12px;
  }

  .chart {
    height: 200px;
  }

  .orders-card {
    padding-inline: 10px;
  }

  .orders-heading {
    align-items: flex-start;
  }

  .view-all {
    padding: 8px;
    white-space: nowrap;
  }
}

@media (max-width: 380px) {
  .dashboard-heading {
    display: block;
  }

  .date-filter {
    margin-top: 15px;
  }

  .metric-value {
    font-size: 20px;
  }

  .topbar-icon {
    display: none;
  }
}
</style>