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
        <p class="vip-hero-desc">Mở khóa toàn bộ kho truyện premium, nghe mọi lúc mọi nơi, không quảng cáo, không gián đoạn</p>
      </div>
    </section>

    <div class="container" style="max-width:660px;">

      <!-- SUBSCRIPTION CARD -->
      <div class="card vip-card animate-in">
        <div style="padding:20px 20px 0;">
          <h2 style="font-size:16px;font-weight:700;margin-bottom:4px;">Chọn gói VIP</h2>
          <p style="font-size:13px;color:var(--text-muted);">Tất cả gói đều cho phép nghe không giới hạn</p>

          <!-- Plans grid -->
          <div class="vip-plans">
            <div v-for="plan in plans" :key="plan.id" :class="['vip-plan', { active: selectedPlan === plan.id }]" @click="selectPlan(plan)">
              <div v-if="plan.badge" :class="['vip-plan-badge', plan.badgeColor || '']">{{ plan.badge }}</div>
              <div class="vip-plan-duration">{{ plan.duration }}</div>
              <div class="vip-plan-price">{{ plan.price }}</div>
              <div class="vip-plan-per">{{ plan.per }}</div>
              <div v-if="plan.discount" class="vip-plan-discount">{{ plan.discount }}</div>
            </div>
          </div>

          <!-- Summary -->
          <div class="vip-summary">
            <div class="vip-summary-label">Thanh toán</div>
            <div class="vip-summary-price">{{ currentPlan.price }}</div>
            <div class="vip-summary-period">cho {{ currentPlan.periodLabel }}</div>
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
          <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px;">Phương thức thanh toán</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <div v-for="method in paymentMethods" :key="method" class="pay-method">{{ method }}</div>
          </div>
          <button class="btn btn-primary" style="width:100%;font-size:15px;height:48px;" @click="handleSubscribe">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
            {{ btnLabel }}
          </button>
          <p style="font-size:11px;color:var(--text-faint);text-align:center;margin-top:10px;">Bằng cách đăng ký, bạn đồng ý với điều khoản sử dụng</p>
        </div>
      </div>

      <!-- WHY VIP -->
      <section class="why-vip-section">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;text-align:center;">Tại sao chọn VIP?</h2>
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const selectedPlan = ref('3month')
const btnLabel = ref('Đăng ký VIP ngay')

const plans = ref([
  { id: '1month', duration: '1 Tháng', price: '37.000đ', per: '/ tháng', periodLabel: 'tháng' },
  { id: '3month', duration: '3 Tháng', price: '99.000đ', per: '33.000đ / tháng', discount: 'Tiết kiệm 12%', badge: 'Phổ biến', periodLabel: '3 tháng' },
  { id: '6month', duration: '6 Tháng', price: '179.000đ', per: '29.833đ / tháng', discount: 'Tiết kiệm 20%', periodLabel: '6 tháng' },
  { id: '12month', duration: '12 Tháng', price: '299.000đ', per: '24.916đ / tháng', discount: 'Tiết kiệm 33%', badge: 'Rẻ nhất', badgeColor: 'green', periodLabel: 'năm' },
])

const currentPlan = computed(() => plans.value.find(p => p.id === selectedPlan.value) || plans.value[1])

const selectPlan = (plan) => { selectedPlan.value = plan.id }

const handleSubscribe = () => {
  btnLabel.value = '⏳ Đang xử lý...'
  setTimeout(() => { btnLabel.value = '✅ Đã đặt hàng!' }, 1200)
}

const scrollToCard = () => { document.querySelector('.vip-card')?.scrollIntoView({ behavior: 'smooth' }) }

const features = [
  '<strong>Nghe không giới hạn</strong> tất cả tập VIP',
  '<strong>Không quảng cáo</strong> - Trải nghiệm mượt mà',
  '<strong>Tải về nghe offline</strong> trên ứng dụng',
  '<strong>Hỗ trợ ưu tiên</strong> 24/7',
  '<strong>Truy cập sớm</strong> các tập mới nhất',
]

const paymentMethods = ['Momo', 'ZaloPay', 'VNPay', 'Chuyển khoản', 'Google Pay']

const whyVip = [
  { title: 'Không giới hạn', desc: 'Nghe không giới hạn tất cả tập VIP trong kho', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>' },
  { title: 'Nghe offline', desc: 'Tải về thiết bị để nghe khi không có mạng', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>' },
  { title: 'Truy cập sớm', desc: 'Được nghe trước khi tập mới phát hành đại trà', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="16"/><line x1="8" x2="16" y1="12" y2="12"/></svg>' },
  { title: 'Hỗ trợ 24/7', desc: 'Hỗ trợ ưu tiên qua chat và email cho thành viên VIP', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>' },
]

const faqs = ref([
  { q: 'VIP có tự động gia hạn không?', a: 'Không, VIP không tự động gia hạn. Bạn sẽ cần gia hạn thủ công khi gói hết hạn. Chúng tôi sẽ gửi thông báo trước 3 ngày.', open: false },
  { q: 'Có thể nghe offline không?', a: 'Có, thành viên VIP có thể tải về tập truyện bất kỳ trên ứng dụng điện thoại để nghe khi không có kết nối internet.', open: false },
  { q: 'Thanh toán bằng phương thức nào?', a: 'Chúng tôi hỗ trợ Momo, ZaloPay, VNPay, chuyển khoản ngân hàng và Google Pay. Thanh toán an toàn và bảo mật.', open: false },
  { q: 'Tôi có được hoàn tiền nếu không hài lòng?', a: 'Chúng tôi có chính sách hoàn tiền trong vòng 7 ngày nếu bạn chưa sử dụng quá 30% thời gian gói. Liên hệ hỗ trợ để được giải quyết.', open: false },
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
.vip-plans { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 16px 0; }
@media (min-width: 480px) { .vip-plans { grid-template-columns: repeat(4, 1fr); } }

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

/* Payment */
.pay-method { padding: 6px 12px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 6px; font-size: 12px; font-weight: 600; }

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
