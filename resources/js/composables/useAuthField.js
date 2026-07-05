import { ref } from 'vue'

export function useAuthField(fieldError, { onReset } = {}) {
    const touched = ref(false)

    const touch = () => {
        touched.value = true
    }

    const validate = () => {
        touch()
        return fieldError.value
    }

    const reset = () => {
        touched.value = false
        onReset?.()
    }

    return { touched, touch, validate, reset }
}
