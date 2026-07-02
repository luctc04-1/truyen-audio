<template>
  <div class="vip-page">

    <!-- VIP HERO -->
    <section class="vip-hero">
      <div class="vip-hero-glow"></div>
      <div class="container vip-hero-content">
        <div class="vip-hero-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
        </div>
        <h1 class="vip-hero-title">Trải Nghiệm <span class="text-gradient">VIP</span> Không Giới Hạn</h1>
        <p class="vip-hero-desc">Truy cập không giới hạn vào thư viện truyện audio VIP với chất lượng cao nhất, không quảng cáo và nhiều đặc quyền hấp dẫn.</p>
      </div>
    </section>

    <div class="container" style="max-width:660px;">

      <!-- SUBSCRIPTION CARD -->
      <div class="card vip-card animate-in">
        <div style="padding:20px 20px 0;">
          <h2 style="font-size:16px;font-weight:700;margin-bottom:4px;">Chọn gói VIP</h2>
          <p style="font-size:13px;color:var(--text-muted);">Tất cả gói đều cho phép nghe không giới hạn</p>

          <!-- Plans grid -->
          <div v-if="loadingPlans" class="vip-plans">
            <div v-for="n in 4" :key="'sk-plan-' + n" class="vip-plan vip-plan-skeleton">
              <div class="sk sk-line sk-line-sm sk-w-55"></div>
              <div class="sk sk-line sk-line-lg sk-w-75"></div>
              <div class="sk sk-line sk-line-sm sk-w-65"></div>
            </div>
          </div>
          <div v-else-if="plans.length" class="vip-plans">
            <div v-for="plan in plans" :key="plan.id" :class="['vip-plan', { active: selectedPlan === plan.id }]" @click="selectPlan(plan)">
              <div v-if="plan.badge" :class="['vip-plan-badge', plan.badgeColor || '']">{{ plan.badge }}</div>
              <div class="vip-plan-duration">{{ plan.duration }}</div>
              <div class="vip-plan-price">{{ plan.price }}</div>
              <div v-if="plan.per" class="vip-plan-per">{{ plan.per }}</div>
              <div v-if="plan.discount" class="vip-plan-discount">{{ plan.discount }}</div>
            </div>
          </div>
          <div v-else class="vip-plans-empty">Không có gói VIP khả dụng.</div>

          <!-- Summary -->
          <div v-if="loadingPlans" class="vip-summary vip-summary-skeleton">
            <div class="sk sk-line sk-line-sm sk-w-72"></div>
            <div class="sk sk-line sk-line-lg sk-w-96"></div>
            <div class="sk sk-line sk-line-sm sk-w-88"></div>
          </div>
          <div v-else-if="currentPlan" class="vip-summary">
            <div class="vip-summary-label">Thanh toán</div>
            <div class="vip-summary-price">{{ currentPlan.price }}</div>
            <div class="vip-summary-period">cho {{ currentPlan.duration }}</div>
          </div>

          <!-- Features -->
          <ul class="vip-features" style="margin-bottom:20px;">
            <li class="vip-feature" v-for="f in features" :key="f">
              <div class="vip-feature-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <span v-html="f"></span>
            </li>
          </ul>
        </div>

        <!-- Payment methods -->
        <div style="background:var(--bg-muted);padding:16px 20px;">
          <button class="btn btn-primary" style="width:100%;font-size:15px;height:48px;" :disabled="subscribing || !currentPlan" @click="handleSubscribe">
            <ButtonSpinner v-if="subscribing" variant="light" :size="18" />
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
            Thanh toán {{ currentPlan?.price }}
          </button>
          <p style="font-size:11px;color:var(--text-faint);text-align:center;margin-top:10px;">VIP được kích hoạt tự động sau khi thanh toán.</p>
        </div>
      </div>

      <!-- WHY VIP -->
      <section class="why-vip-section">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;text-align:center;">Tại sao nên đăng ký VIP?</h2>
        <div class="why-vip-grid">
          <div class="why-vip-item" v-for="item in whyVip" :key="item.title">
            <div class="why-vip-icon" v-html="item.icon"></div>
            <div class="why-vip-title">{{ item.title }}</div>
            <div class="why-vip-desc">{{ item.desc }}</div>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section style="padding: 0 0 32px;">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;text-align:center;">Câu hỏi thường gặp</h2>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <div class="faq-item" v-for="faq in faqs" :key="faq.q">
            <div class="faq-question" @click="faq.open = !faq.open">
              {{ faq.q }}
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="{ transform: faq.open ? 'rotate(180deg)' : '' }" style="width:16px;height:16px;transition:transform 0.2s;flex-shrink:0;"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <div class="faq-answer" v-if="faq.open">{{ faq.a }}</div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section style="padding-bottom:40px;">
        <div class="vip-cta-section">
          <div class="vip-cta-title">Sẵn sàng trải nghiệm chưa?</div>
          <p class="vip-cta-desc">Hơn 10.000+ thành viên VIP đã tham gia. Bắt đầu hành trình nghe truyện không giới hạn ngay hôm nay!</p>
          <a href="#" class="vip-cta-btn" @click.prevent="scrollToCard">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
            Đăng ký VIP ngay
          </a>
        </div>
      </section>

    </div>

    <VipPaymentModal
      :open="paymentModalOpen"
      :checkout="activeCheckout"
      :success="paymentSuccess"
      @close="closePaymentModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import ApiService from '@/services/ApiService'
import OrderService from '@/services/OrderService'
import ButtonSpinner from '@/components/ButtonSpinner.vue'
import VipPaymentModal from '@/components/VipPaymentModal.vue'
import { useAuthStore } from '@/stores/authStore'
import { useToastStore } from '@/stores/toastStore'
import { getEcho } from '@/services/echo'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()
const { user, token, isAuthenticated } = storeToRefs(auth)

const selectedPlan = ref(null)
const subscribing = ref(false)
const plans = ref([])
const loadingPlans = ref(true)
const paymentModalOpen = ref(false)
const paymentSuccess = ref(false)
const activeCheckout = ref(null)

let paymentChannel = null

const currentPlan = computed(() =>
  plans.value.find(p => p.id === selectedPlan.value) ?? plans.value[0] ?? null
)

const loadPlans = async () => {
  loadingPlans.value = true
  try {
    const response = await ApiService.get('/plans')
    const payload = response?.data ?? response
    plans.value = Array.isArray(payload) ? payload : []
    const popular = plans.value.find(p => p.code === 'vip_3m')
    selectedPlan.value = popular?.id ?? plans.value[0]?.id ?? null
  } catch (error) {
    console.error('Không tải được gói VIP:', error)
    plans.value = []
  } finally {
    loadingPlans.value = false
  }
}

onMounted(async () => {
  await loadPlans()
  await checkReturnFromPayOs()
})

onUnmounted(() => {
  stopPaymentWatchers()
})

const selectPlan = (plan) => { selectedPlan.value = plan.id }

const handleSubscribe = async () => {
  if (!isAuthenticated.value) {
    router.push({ name: 'Auth', query: { redirect: '/vip' } })
    return
  }

  if (!currentPlan.value) return

  subscribing.value = true
  try {
    const checkout = await OrderService.checkout(currentPlan.value.id)
    activeCheckout.value = checkout
    paymentSuccess.value = false
    paymentModalOpen.value = true
    startPaymentWatchers(checkout.order_code)
  } catch (error) {
    toast.error(error.message || 'Không tạo được đơn thanh toán.')
  } finally {
    subscribing.value = false
  }
}

const handlePaymentSuccess = (payload = null) => {
  if (paymentSuccess.value) return

  paymentSuccess.value = true
  stopPaymentWatchers()

  if (payload?.user) {
    auth.user = payload.user
  } else {
    auth.fetchMe()
  }

  toast.success('Thanh toán thành công! VIP đã được kích hoạt.')
}

const startPaymentWatchers = (orderCode) => {
  stopPaymentWatchers()

  const echo = getEcho(token.value)
  if (echo && user.value?.id) {
    paymentChannel = echo.private(`user.${user.value.id}`)
    paymentChannel.listen('.vip.payment.succeeded', (event) => {
      if (Number(event.order_code) === Number(orderCode)) {
        handlePaymentSuccess(event)
      }
    })
  }
}

const stopPaymentWatchers = () => {
  if (paymentChannel) {
    paymentChannel.stopListening('.vip.payment.succeeded')
    paymentChannel = null
  }
}

const closePaymentModal = () => {
  paymentModalOpen.value = false
  paymentSuccess.value = false
  activeCheckout.value = null
  stopPaymentWatchers()
}

const checkReturnFromPayOs = async () => {
  const orderCode = route.query.order_code
  if (!orderCode || !isAuthenticated.value) return

  try {
    const status = await OrderService.status(orderCode)
    if (status?.status === 'paid') {
      await auth.fetchMe()
      paymentSuccess.value = true
      paymentModalOpen.value = true
      toast.success('Thanh toán thành công! VIP đã được kích hoạt.')
    } else if (status?.status === 'pending') {
      activeCheckout.value = { order_code: Number(orderCode), plan_name: status.plan_name, amount: status.amount }
      paymentModalOpen.value = true
      startPaymentWatchers(Number(orderCode))
    }
  } catch {
    // ignore
  }

  if (route.query.order_code) {
    router.replace({ path: '/vip' })
  }
}

const scrollToCard = () => { document.querySelector('.vip-card')?.scrollIntoView({ behavior: 'smooth' }) }

const features = [
  '<span>Nghe không giới hạn tất cả tập VIP</span>',
  '<span>Không quảng cáo - Trải nghiệm mượt mà</span>',
  '<span>Huy hiệu Hội viên độc quyền</span>',
  '<span>Truy cập sớm các tập mới nhất</span>',
]

const whyVip = [
  { title: 'Không giới hạn', desc: 'Nghe không giới hạn tất cả tập VIP trong kho', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>' },
  { title: 'Nghe không quảng cáo', desc: 'Trải nghiệm nghe truyện liền mạch, không bị gián đoạn', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles w-4.5 h-4.5 text-primary"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"></path><path d="M20 3v4"></path><path d="M22 5h-4"></path><path d="M4 17v2"></path><path d="M5 18H3"></path></svg>' },
  { title: 'Truy cập sớm', desc: 'Được nghe trước khi tập mới phát hành đại trà', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="16"/><line x1="8" x2="16" y1="12" y2="12"/></svg>' },
  { title: 'Nội dung mới mỗi tuần', desc: 'Các tập mới được cập nhật thường xuyên, không lo hết truyện', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>' },
]

const faqs = ref([
  { q: 'Làm thế nào để thanh toán?', a: 'Nhấn nút "Thanh toán", bạn sẽ được chuyển đến trang thanh toán PayOS. Hỗ trợ chuyển khoản ngân hàng, ví MoMo, ZaloPay và nhiều phương thức khác.', open: false },
  { q: 'VIP được kích hoạt khi nào?', a: 'VIP được kích hoạt TỰ ĐỘNG ngay sau khi thanh toán thành công. Không cần chờ admin duyệt.', open: false },
  { q: 'Thanh toán bằng phương thức nào?', a: 'Tùy theo gói bạn chọn: 1 tháng (30 ngày), 3 tháng (90 ngày), 6 tháng (180 ngày), hoặc 12 tháng (360 ngày).', open: false },
])
</script>

<style scoped>
.vip-page { min-height: 100vh; }

.container { max-width: 660px; margin: 0 auto; padding: 0 16px; }
@media (min-width: 640px) { .container { padding: 0 24px; } }

/* Hero */
.vip-hero { position: relative; padding: 48px 0 40px; text-align: center; overflow: hidden; }
.vip-hero-glow { position: absolute; inset: 0; background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(168,85,247,0.18), transparent); pointer-events: none; }
.vip-hero-content { position: relative; display: flex; flex-direction: column; align-items: center; }
.vip-hero-icon { display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: var(--gradient-premium); margin-bottom: 20px; box-shadow: 0 12px 32px rgba(168,85,247,0.35); }
.vip-hero-title { font-size: 28px; font-weight: 800; margin-bottom: 12px; line-height: 1.2; }
@media (min-width: 768px) { .vip-hero-title { font-size: 36px; } }
.vip-hero-desc { font-size: 14px; color: var(--text-muted); max-width: 480px; }
.text-gradient { background: var(--gradient-premium); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

/* Card */
.card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.vip-card { margin-bottom: 32px; }
.animate-in { animation: slideUp 0.6s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* Plans */
.vip-plans-empty { margin: 16px 0; padding: 24px; text-align: center; font-size: 13px; color: var(--text-muted); }
.vip-plans { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 16px 0; }
@media (min-width: 480px) { .vip-plans { grid-template-columns: repeat(4, 1fr); } }

.vip-plan-skeleton { pointer-events: none; cursor: default; padding: 18px 10px; }
.vip-plan-skeleton .sk { margin: 0 auto; }
.vip-plan-skeleton .sk-line-sm:first-child { margin-bottom: 8px; }
.vip-plan-skeleton .sk-line-lg { margin-bottom: 6px; }
.vip-summary-skeleton { min-height: 44px; }
.sk-w-55 { width: 55%; }
.sk-w-65 { width: 65%; }
.sk-w-72 { width: 72px; }
.sk-w-75 { width: 75%; }
.sk-w-88 { width: 88px; }
.sk-w-96 { width: 96px; }

.sk {
  background: linear-gradient(90deg, var(--bg-muted) 25%, rgba(255,255,255,0.06) 50%, var(--bg-muted) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 4px;
}
.sk-line { height: 12px; }
.sk-line-lg { height: 16px; }
.sk-line-sm { height: 10px; }
@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.vip-plan { position: relative; border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: 14px 10px; text-align: center; cursor: pointer; transition: all 0.2s; background: var(--bg-muted); }
.vip-plan:hover { border-color: var(--primary); }
.vip-plan.active { border-color: var(--primary); background: var(--primary-light); }
.vip-plan-badge { position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: var(--gradient-premium); color: #fff; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: var(--radius-full); white-space: nowrap; }
.vip-plan-badge.green { background: linear-gradient(135deg, #22c55e, #16a34a); }
.vip-plan-duration { font-size: 13px; font-weight: 700; margin-bottom: 6px; }
.vip-plan-price { font-size: 16px; font-weight: 800; color: var(--primary); margin-bottom: 2px; }
.vip-plan-per { font-size: 10px; color: var(--text-muted); }
.vip-plan-discount { font-size: 10px; color: var(--success); font-weight: 600; margin-top: 4px; }

/* Summary */
.vip-summary { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); margin: 16px 0; }
.vip-summary-label { font-size: 13px; color: var(--text-muted); }
.vip-summary-price { font-size: 20px; font-weight: 800; color: var(--primary); }
.vip-summary-period { font-size: 13px; color: var(--text-muted); }

/* Features */
.vip-features { list-style: none; display: flex; flex-direction: column; gap: 10px; }
.vip-feature { display: flex; align-items: center; gap: 10px; font-size: 14px; }
.vip-feature-icon { width: 20px; height: 20px; border-radius: 50%; background: rgba(34,197,94,0.12); color: var(--success); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.vip-feature-icon svg { width: 12px; height: 12px; stroke: var(--success); }

/* Button */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: var(--radius-sm); font-weight: 500; transition: all 0.2s; cursor: pointer; font-family: inherit; }
.btn-primary { background: var(--gradient-premium); color: #fff; border: none; }
.btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }

/* Why VIP */
.why-vip-section { padding: 0 0 32px; }
.why-vip-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.why-vip-item { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px 16px; text-align: center; }
.why-vip-icon { width: 48px; height: 48px; border-radius: var(--radius-md); background: var(--primary-light); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
.why-vip-icon :deep(svg) { width: 22px; height: 22px; stroke: var(--primary); }
.why-vip-title { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
.why-vip-desc { font-size: 12px; color: var(--text-muted); line-height: 1.5; }

/* FAQ */
.faq-item { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
.faq-question { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.faq-question:hover { background: var(--bg-muted); }
.faq-answer { padding: 0 16px 14px; font-size: 13px; color: var(--text-muted); line-height: 1.6; border-top: 1px solid var(--border); padding-top: 12px; }

/* CTA */
.vip-cta-section { background: linear-gradient(135deg, rgba(168,85,247,0.12), rgba(236,72,153,0.08)); border: 1px solid rgba(168,85,247,0.25); border-radius: var(--radius-lg); padding: 28px 24px; text-align: center; }
.vip-cta-title { font-size: 20px; font-weight: 800; margin-bottom: 10px; }
.vip-cta-desc { font-size: 14px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.6; }
.vip-cta-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: var(--radius-sm); background: var(--gradient-premium); color: #fff; font-weight: 600; font-size: 15px; transition: opacity 0.2s; }
.vip-cta-btn:hover { opacity: 0.88; }
</style>
