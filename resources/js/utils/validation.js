export const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export const PASSWORD_RULES = [
    { key: 'length', label: 'Ít nhất 8 ký tự', test: (value) => value.length >= 8 },
    { key: 'lowercase', label: 'Chữ in thường (a-z)', test: (value) => /[a-z]/.test(value) },
    { key: 'special', label: 'Ký tự đặc biệt (!@#$...)', test: (value) => /[^A-Za-z0-9]/.test(value) },
    { key: 'uppercase', label: 'Chữ in hoa (A-Z)', test: (value) => /[A-Z]/.test(value) },
    { key: 'number', label: 'Số (0-9)', test: (value) => /[0-9]/.test(value) },
];

export const AUTH_MESSAGES = {
    emailRequired: 'Vui lòng nhập email.',
    emailInvalid: 'Email không hợp lệ.',
    passwordRequired: 'Vui lòng nhập mật khẩu.',
    passwordWeak: 'Mật khẩu chưa đáp ứng đủ yêu cầu bảo mật.',
    passwordMismatch: 'Mật khẩu xác nhận không khớp.',
    usernameRequired: 'Vui lòng nhập họ tên.',
};

export function isValidEmail(email) {
    const value = (email ?? '').trim();
    return value.length > 0 && EMAIL_PATTERN.test(value);
}

export function getPasswordChecks(password = '') {
    return PASSWORD_RULES.map((rule) => ({
        ...rule,
        met: rule.test(password),
    }));
}

export function isStrongPassword(password = '') {
    return PASSWORD_RULES.every((rule) => rule.test(password));
}

export function emailError(email) {
    const value = (email ?? '').trim();
    if (!value) return AUTH_MESSAGES.emailRequired;
    if (!isValidEmail(value)) return AUTH_MESSAGES.emailInvalid;
    return '';
}

export function passwordRequiredError(password) {
    if (!(password ?? '').length) return AUTH_MESSAGES.passwordRequired;
    return '';
}

export function strongPasswordError(password) {
    const required = passwordRequiredError(password);
    if (required) return required;
    if (!isStrongPassword(password)) return AUTH_MESSAGES.passwordWeak;
    return '';
}

export function passwordConfirmError(password, confirm) {
    const required = passwordRequiredError(confirm);
    if (required) return required;
    if (password !== confirm) return AUTH_MESSAGES.passwordMismatch;
    return '';
}

export function usernameError(username) {
    if (!(username ?? '').trim()) return AUTH_MESSAGES.usernameRequired;
    return '';
}

export function firstAuthFieldError(fields) {
    for (const field of fields) {
        if (!field) continue
        const error = field.validate()
        if (error) return error
    }
    return ''
}
