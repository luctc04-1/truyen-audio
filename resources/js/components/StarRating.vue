<template>
  <div :class="['star-rating', { interactive, readonly }]">
    <button
      v-for="star in 5"
      :key="star"
      type="button"
      class="star-btn"
      :disabled="readonly"
      :aria-label="`${star} sao`"
      @click="select(star)"
      @mouseenter="interactive && !readonly && (hover = star)"
      @mouseleave="interactive && !readonly && (hover = 0)"
    >
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path
          :fill="star <= displayValue ? '#f59e0b' : 'none'"
          :stroke="star <= displayValue ? '#f59e0b' : '#71717a'"
          stroke-width="1.5"
          d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.60l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"
        />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: { type: Number, default: 0 },
  readonly: { type: Boolean, default: false },
  interactive: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])

const hover = ref(0)

const displayValue = computed(() => {
  if (!props.interactive || props.readonly) return props.modelValue
  return hover.value || props.modelValue
})

const select = (star) => {
  if (props.readonly) return
  emit('update:modelValue', star)
}
</script>

<style scoped>
.star-rating {
  display: inline-flex;
  gap: 2px;
}

.star-btn {
  display: flex;
  padding: 0;
  border: none;
  background: none;
  cursor: default;
  line-height: 0;
}

.interactive:not(.readonly) .star-btn {
  cursor: pointer;
}

.star-btn svg {
  width: 16px;
  height: 16px;
}

.star-rating:not(.readonly) .star-btn:hover svg {
  transform: scale(1.08);
  transition: transform 0.15s;
}
</style>
