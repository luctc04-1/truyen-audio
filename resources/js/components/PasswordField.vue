<template>
  <label class="auth-field">
    <span class="auth-field-label">{{ label }}</span>
    <div class="auth-field-input-wrap" :class="{ invalid: touched && fieldError }">
      <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <input
        :value="modelValue"
        :type="visible ? 'text' : 'password'"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        @input="$emit('update:modelValue', $event.target.value)"
        @focus="focused = true"
        @blur="onBlur"
      />
      <button type="button" class="auth-field-toggle" tabindex="-1" @click="visible = !visible">
        <svg v-if="visible" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
      </button>
    </div>
    <PasswordRequirements
      v-if="showRequirements"
      :password="modelValue"
      :visible="showChecklist"
    />
    <p v-if="touched && fieldError" class="auth-field-error">{{ fieldError }}</p>
  </label>
</template>

<script setup>
import { ref, computed } from 'vue'
import PasswordRequirements from '@/components/PasswordRequirements.vue'
import { useAuthField } from '@/composables/useAuthField'
import {
  passwordRequiredError,
  strongPasswordError,
  passwordConfirmError,
} from '@/utils/validation'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: 'Mật khẩu',
  },
  placeholder: {
    type: String,
    default: '••••••••',
  },
  autocomplete: {
    type: String,
    default: 'new-password',
  },
  mode: {
    type: String,
    default: 'strong',
    validator: (value) => ['login', 'strong', 'confirm'].includes(value),
  },
  match: {
    type: String,
    default: '',
  },
  showRequirements: {
    type: Boolean,
    default: undefined,
  },
})

defineEmits(['update:modelValue'])

const visible = ref(false)
const focused = ref(false)

const showRequirements = computed(() => {
  if (props.showRequirements !== undefined) return props.showRequirements
  return props.mode === 'strong'
})

const showChecklist = computed(() => focused.value || props.modelValue.length > 0)

const fieldError = computed(() => {
  if (props.mode === 'login') return passwordRequiredError(props.modelValue)
  if (props.mode === 'confirm') return passwordConfirmError(props.match, props.modelValue)
  return strongPasswordError(props.modelValue)
})

const { touched, touch, validate, reset } = useAuthField(fieldError, {
  onReset: () => {
    focused.value = false
  },
})

const onBlur = () => {
  focused.value = false
  touch()
}

defineExpose({ touch, validate, reset, error: fieldError })
</script>
