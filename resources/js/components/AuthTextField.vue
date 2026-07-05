<template>
  <label class="auth-field">
    <span class="auth-field-label">{{ label }}</span>
    <div class="auth-field-input-wrap" :class="{ invalid: touched && fieldError }">
      <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <template v-if="variant === 'email'">
          <rect width="20" height="16" x="2" y="4" rx="2" />
          <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
        </template>
        <template v-else>
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </template>
      </svg>
      <input
        :value="modelValue"
        :type="inputType"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="touch"
      />
    </div>
    <p v-if="touched && fieldError" class="auth-field-error">{{ fieldError }}</p>
  </label>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthField } from '@/composables/useAuthField'
import { emailError, usernameError } from '@/utils/validation'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  variant: {
    type: String,
    default: 'email',
    validator: (value) => ['email', 'username'].includes(value),
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  autocomplete: {
    type: String,
    default: '',
  },
})

defineEmits(['update:modelValue'])

const defaults = {
  email: {
    label: 'Email',
    placeholder: 'email@example.com',
    autocomplete: 'email',
    type: 'email',
  },
  username: {
    label: 'Họ tên',
    placeholder: 'Nguyễn Văn A',
    autocomplete: 'name',
    type: 'text',
  },
}

const config = computed(() => defaults[props.variant])

const label = computed(() => props.label || config.value.label)
const placeholder = computed(() => props.placeholder || config.value.placeholder)
const autocomplete = computed(() => props.autocomplete || config.value.autocomplete)
const inputType = computed(() => config.value.type)

const fieldError = computed(() => (
  props.variant === 'email'
    ? emailError(props.modelValue)
    : usernameError(props.modelValue)
))

const { touched, touch, validate, reset } = useAuthField(fieldError)

defineExpose({ touch, validate, reset, error: fieldError })
</script>
