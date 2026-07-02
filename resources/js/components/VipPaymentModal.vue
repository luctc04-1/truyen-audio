<template>
  <Teleport to="body">
    <Transition name="vip-pay-fade">
      <div v-if="open" class="vip-pay-overlay" @click.self="onClose">
        <div class="vip-pay-modal">
          <button type="button" class="vip-pay-close" aria-label="Đóng" @click="onClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          </button>

          <div v-if="success" class="vip-pay-success">
            <div class="vip-pay-success-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/></svg>
            </div>
            <h3>Thanh toán thành công!</h3>
            <p>VIP đã được kích hoạt. Bạn có thể nghe không giới hạn ngay bây giờ.</p>
            <button type="button" class="vip-pay-btn" @click="onClose">Bắt đầu nghe</button>
          </div>

          <template v-else>
            <div class="vip-pay-header">
              <div class="vip-pay-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
              </div>
              <div class="vip-pay-header-text">
                <h3 class="vip-pay-title">Quét mã QR để thanh toán</h3>
                <p class="vip-pay-subtitle">Sử dụng app ngân hàng hoặc ví điện tử để quét mã bên dưới</p>
              </div>
            </div>

            <div class="vip-pay-qr-wrap">
              <div class="vip-pay-qr-box">
                <div v-if="qrLoading" class="vip-pay-qr vip-pay-qr-loading">
                  <ButtonSpinner :size="28" variant="primary" />
                </div>
                <img
                  v-else-if="qrDataUrl"
                  :src="qrDataUrl"
                  alt="Mã QR thanh toán"
                  class="vip-pay-qr"
                  :class="{ 'is-visible': qrVisible }"
                  @load="qrVisible = true"
                />
              </div>
            </div>

            <div v-if="checkout" class="vip-pay-details">
              <div class="vip-pay-plan">Gói {{ checkout.plan_name }}</div>
              <div class="vip-pay-amount">{{ formattedAmount }}</div>

              <div v-if="checkout.bank_name" class="vip-pay-row">
                <span>Ngân hàng</span>
                <span>{{ checkout.bank_name }}</span>
              </div>
              <div v-if="checkout.account_number" class="vip-pay-row">
                <span>Số TK</span>
                <span class="vip-pay-mono">{{ checkout.account_number }}</span>
              </div>
              <div v-if="checkout.account_name" class="vip-pay-row">
                <span>Chủ TK</span>
                <span>{{ checkout.account_name }}</span>
              </div>
              <div v-if="checkout.transfer_content" class="vip-pay-row">
                <span>Nội dung CK</span>
                <span class="vip-pay-mono">{{ checkout.transfer_content }}</span>
              </div>
            </div>

            <div class="vip-pay-status">
              <ButtonSpinner :size="16" variant="primary" />
              <span>Đang chờ xác nhận thanh toán... Trang sẽ tự cập nhật khi thanh toán thành công.</span>
            </div>
          </template>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import QRCode from 'qrcode'
import ButtonSpinner from '@/components/ButtonSpinner.vue'

const BIN_BANK_NAMES = {
  '970452': 'KienlongBank',
  '970422': 'MB Bank',
  '970436': 'Vietcombank',
  '970415': 'VietinBank',
  '970418': 'BIDV',
  '970405': 'Agribank',
  '970407': 'Techcombank',
  '970416': 'ACB',
  '970432': 'VPBank',
  '970423': 'TPBank',
  '970403': 'Sacombank',
  '970437': 'HDBank',
  '970441': 'VIB',
  '970443': 'SHB',
  '970426': 'MSB',
  '970449': 'LienVietPostBank',
}

const props = defineProps({
  open: { type: Boolean, default: false },
  checkout: { type: Object, default: null },
  success: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const qrDataUrl = ref('')
const qrLoading = ref(false)
const qrVisible = ref(false)

watch(() => props.checkout, async (checkout) => {
  qrDataUrl.value = ''
  qrVisible.value = false

  if (!checkout) return

  qrLoading.value = true
  try {
    qrDataUrl.value = await buildPlainQr(checkout)
  } catch (error) {
    console.error('Không tạo được mã QR:', error)
  } finally {
    qrLoading.value = false
  }
}, { immediate: true })

watch(() => props.open, (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

const checkout = computed(() => {
  const c = props.checkout
  if (!c) return null

  const bin = c.bank_bin ? String(c.bank_bin) : ''
  return {
    ...c,
    bank_name: BIN_BANK_NAMES[bin] || (bin ? `BIN ${bin}` : null),
  }
})

const formattedAmount = computed(() => {
  const amount = props.checkout?.amount
  if (!amount) return ''
  return new Intl.NumberFormat('vi-VN').format(amount) + 'đ'
})

async function buildPlainQr(checkout) {
  const payload = resolveQrPayload(checkout)
  if (!payload) return ''

  return QRCode.toDataURL(payload, {
    width: 512,
    margin: 1,
    errorCorrectionLevel: 'M',
    color: { dark: '#000000', light: '#ffffff' },
  })
}

function resolveQrPayload(checkout) {
  if (checkout.qr_payload) {
    return checkout.qr_payload
  }

  const raw = checkout.qr_code?.trim()
  if (!raw) {
    return checkout.checkout_url || null
  }

  if (raw.startsWith('000201')) {
    return raw
  }

  if (raw.startsWith('http://') || raw.startsWith('https://') || raw.startsWith('data:')) {
    return checkout.checkout_url || raw
  }

  if (/^[A-Za-z0-9+/=]+$/.test(raw) && raw.length > 200) {
    return checkout.checkout_url || null
  }

  return raw
}

const onClose = () => emit('close')
</script>

<style scoped>
.vip-pay-fade-enter-active,
.vip-pay-fade-leave-active {
  transition: opacity 0.22s ease;
}

.vip-pay-fade-enter-active .vip-pay-modal,
.vip-pay-fade-leave-active .vip-pay-modal {
  transition: transform 0.22s ease, opacity 0.22s ease;
}

.vip-pay-fade-enter-from,
.vip-pay-fade-leave-to {
  opacity: 0;
}

.vip-pay-fade-enter-from .vip-pay-modal,
.vip-pay-fade-leave-to .vip-pay-modal {
  transform: translateY(12px) scale(0.98);
  opacity: 0;
}

.vip-pay-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  isolation: isolate;
}

.vip-pay-modal {
  position: relative;
  width: 100%;
  max-width: 400px;
  background: #141414;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.vip-pay-close {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 2;
  width: 30px;
  height: 30px;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.45);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.vip-pay-close:hover {
  color: rgba(255, 255, 255, 0.85);
}

.vip-pay-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 20px;
  padding-right: 28px;
}

.vip-pay-header-icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--primary-light);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.vip-pay-title {
  font-size: 16px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 4px;
  line-height: 1.3;
}

.vip-pay-subtitle {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.45);
  margin: 0;
  line-height: 1.45;
}

.vip-pay-qr-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.vip-pay-qr-box {
  width: 240px;
  height: 240px;
}

.vip-pay-qr {
  width: 240px;
  height: 240px;
  border-radius: 12px;
  background: #fff;
  object-fit: contain;
  padding: 10px;
  display: block;
  opacity: 0;
  transition: opacity 0.28s ease;
}

.vip-pay-qr.is-visible {
  opacity: 1;
}

.vip-pay-qr-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 1;
}

.vip-pay-details {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
}

.vip-pay-plan {
  text-align: center;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.55);
  margin-bottom: 4px;
}

.vip-pay-amount {
  text-align: center;
  font-size: 28px;
  font-weight: 800;
  color: var(--primary);
  margin-bottom: 14px;
  letter-spacing: -0.5px;
}

.vip-pay-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  font-size: 12px;
  padding: 6px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.vip-pay-row span:first-child {
  color: rgba(255, 255, 255, 0.45);
  flex-shrink: 0;
}

.vip-pay-row span:last-child {
  color: rgba(255, 255, 255, 0.9);
  text-align: right;
  word-break: break-all;
}

.vip-pay-mono {
  font-family: ui-monospace, 'Cascadia Code', monospace;
  font-size: 11px;
}

.vip-pay-status {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.55);
  line-height: 1.5;
}

.vip-pay-success {
  text-align: center;
  padding: 12px 0 4px;
}

.vip-pay-success-icon {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: var(--gradient-premium);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  box-shadow: 0 8px 24px rgba(168, 85, 247, 0.35);
}

.vip-pay-success h3 {
  font-size: 20px;
  font-weight: 800;
  color: #fff;
  margin: 0 0 8px;
}

.vip-pay-success p {
  font-size: 14px;
  color: rgba(255, 255, 255, 0.55);
  margin: 0 0 20px;
  line-height: 1.5;
}

.vip-pay-btn {
  width: 100%;
  height: 44px;
  border: none;
  border-radius: 8px;
  background: var(--gradient-premium);
  color: #fff;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
}

.vip-pay-btn:hover {
  opacity: 0.9;
}
</style>
