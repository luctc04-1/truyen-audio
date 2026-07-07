<template>
  <ul v-if="visible" class="pw-reqs">
    <li
      v-for="rule in checks"
      :key="rule.key"
      :class="['pw-req', { met: rule.met }]"
    >
      <span class="pw-req-icon" aria-hidden="true">{{ rule.met ? '✓' : '×' }}</span>
      <span>{{ rule.label }}</span>
    </li>
  </ul>
</template>

<script setup>
import { computed } from 'vue'
import { getPasswordChecks } from '@/utils/validation'

const props = defineProps({
  password: {
    type: String,
    default: '',
  },
  visible: {
    type: Boolean,
    default: true,
  },
})

const checks = computed(() => getPasswordChecks(props.password))
</script>

<style scoped>
.pw-reqs {
  list-style: none;
  margin: 8px 0 0;
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  background: var(--bg-muted);
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.pw-req {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: var(--text-faint);
  line-height: 1.4;
}

.pw-req.met {
  color: #4ade80;
}

.pw-req-icon {
  width: 14px;
  flex-shrink: 0;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
}
</style>
