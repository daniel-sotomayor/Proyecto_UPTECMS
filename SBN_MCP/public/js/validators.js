/**
 * Sistema de Validaciones - SBN MCP
 * Librería de validaciones reutilizables para formularios
 * @version 1.0.0
 */

const Validators = (() => {
    'use strict';

    // Expresiones regulares comunes
    const patterns = {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        cedula: /^[VvEeJjGg]\d{6,9}$/,
        telefono: /^\+?[\d\s\-\(\)]{10,20}$/,
        numeroBien: /^\d{6,7}[A-Fa-f0-9]{3}$/,
        codigoInterno: /^\d{2}-[A-Z]{3}-\d{1,2}-\d{4}$/,
        soloLetras: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
        soloNumeros: /^\d+$/,
        decimal: /^\d+(\.\d{1,2})?$/
    };

    // Mensajes de error por defecto
    const defaultMessages = {
        required: 'Este campo es obligatorio',
        email: 'Ingrese un correo electrónico válido',
        minLength: (min) => `Mínimo ${min} caracteres requeridos`,
        maxLength: (max) => `Máximo ${max} caracteres permitidos`,
        minValue: (min) => `El valor mínimo es ${min}`,
        maxValue: (max) => `El valor máximo es ${max}`,
        pattern: 'Formato inválido',
        match: 'Los campos no coinciden',
        cedula: 'Formato de cédula inválido (Ej: V12345678)',
        numeroBien: 'Formato inválido: 6-7 dígitos + 3 caracteres hexadecimales',
        fecha: 'Fecha inválida'
    };

    /**
     * Valida si un campo es requerido
     */
    function required(value) {
        if (value === null || value === undefined) return false;
        if (typeof value === 'string') return value.trim() !== '';
        if (Array.isArray(value)) return value.length > 0;
        return true;
    }

    /**
     * Valida longitud mínima
     */
    function minLength(value, min) {
        if (!value) return false;
        return String(value).length >= min;
    }

    /**
     * Valida longitud máxima
     */
    function maxLength(value, max) {
        if (!value) return true;
        return String(value).length <= max;
    }

    /**
     * Valida rango numérico
     */
    function minValue(value, min) {
        const num = parseFloat(value);
        return !isNaN(num) && num >= min;
    }

    function maxValue(value, max) {
        const num = parseFloat(value);
        return !isNaN(num) && num <= max;
    }

    /**
     * Valida patrón regex
     */
    function pattern(value, regex) {
        if (!value) return true;
        return regex.test(String(value));
    }

    /**
     * Valida correo electrónico
     */
    function email(value) {
        if (!value) return true;
        return patterns.email.test(value);
    }

    /**
     * Valida fecha
     */
    function fecha(value) {
        if (!value) return true;
        const date = new Date(value);
        return !isNaN(date.getTime());
    }

    /**
     * Valida que la fecha no sea futura
     */
    function fechaNoFutura(value) {
        if (!value) return true;
        const date = new Date(value);
        const now = new Date();
        return date <= now;
    }

    /**
     * Valida cédula venezolana
     */
    function cedula(value) {
        if (!value) return true;
        return patterns.cedula.test(value);
    }

    /**
     * Valida número de bien del ministerio
     */
    function numeroBien(value) {
        if (!value || value === 'S/N' || value === 's/n') return true;
        return patterns.numeroBien.test(value);
    }

    /**
     * Valida que dos campos coincidan
     */
    function match(value, compareValue) {
        return value === compareValue;
    }

    /**
     * Valida fortaleza de contraseña
     */
    function passwordStrength(value) {
        if (!value) return { valid: false, score: 0, errors: ['Contraseña requerida'] };
        
        const errors = [];
        let score = 0;
        
        if (value.length >= 8) score++;
        else errors.push('Mínimo 8 caracteres');
        
        if (/[A-Z]/.test(value)) score++;
        else errors.push('Al menos una mayúscula');
        
        if (/[a-z]/.test(value)) score++;
        else errors.push('Al menos una minúscula');
        
        if (/\d/.test(value)) score++;
        else errors.push('Al menos un número');
        
        if (/[\W_]/.test(value)) score++;
        else errors.push('Al menos un carácter especial');
        
        return { valid: score >= 4, score, errors };
    }

    /**
     * Formatea mensaje de error
     */
    function getErrorMessage(rule, customMessage, ...args) {
        if (customMessage) return customMessage;
        
        const msg = defaultMessages[rule];
        if (typeof msg === 'function') {
            return msg(...args);
        }
        return msg || 'Campo inválido';
    }

    // API pública
    return {
        required,
        minLength,
        maxLength,
        minValue,
        maxValue,
        pattern,
        email,
        fecha,
        fechaNoFutura,
        cedula,
        numeroBien,
        match,
        passwordStrength,
        getErrorMessage,
        patterns
    };
})();

/**
 * FormValidator - Clase para validar formularios completos
 */
class FormValidator {
    constructor(formId, rules = {}) {
        this.form = document.getElementById(formId);
        this.rules = rules;
        this.errors = {};
        this.validators = Validators;
        
        if (this.form) {
            this.init();
        }
    }

    init() {
        // Validación en tiempo real
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearError(field));
        });

        // Validación al enviar
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) {
                e.preventDefault();
                this.showSummary();
            }
        });
    }

    validateField(field) {
        const name = field.name;
        const rules = this.rules[name];
        
        if (!rules) return true;

        const value = field.value;
        let isValid = true;
        let errorMsg = '';

        for (const [rule, param] of Object.entries(rules)) {
            switch (rule) {
                case 'required':
                    if (param && !this.validators.required(value)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('required', rules.messages?.required);
                    }
                    break;
                case 'minLength':
                    if (!this.validators.minLength(value, param)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('minLength', rules.messages?.minLength, param);
                    }
                    break;
                case 'maxLength':
                    if (!this.validators.maxLength(value, param)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('maxLength', rules.messages?.maxLength, param);
                    }
                    break;
                case 'min':
                    if (!this.validators.minValue(value, param)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('minValue', rules.messages?.min, param);
                    }
                    break;
                case 'max':
                    if (!this.validators.maxValue(value, param)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('maxValue', rules.messages?.max, param);
                    }
                    break;
                case 'email':
                    if (param && !this.validators.email(value)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('email', rules.messages?.email);
                    }
                    break;
                case 'pattern':
                    if (!this.validators.pattern(value, param)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('pattern', rules.messages?.pattern);
                    }
                    break;
                case 'cedula':
                    if (param && !this.validators.cedula(value)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('cedula', rules.messages?.cedula);
                    }
                    break;
                case 'numeroBien':
                    if (param && !this.validators.numeroBien(value)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('numeroBien', rules.messages?.numeroBien);
                    }
                    break;
                case 'match':
                    const compareField = this.form.querySelector(`[name="${param}"]`);
                    if (compareField && !this.validators.match(value, compareField.value)) {
                        isValid = false;
                        errorMsg = this.validators.getErrorMessage('match', rules.messages?.match);
                    }
                    break;
            }

            if (!isValid) break;
        }

        if (isValid) {
            this.showValid(field);
        } else {
            this.showError(field, errorMsg);
        }

        return isValid;
    }

    validate() {
        this.errors = {};
        let isFormValid = true;

        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            if (!this.validateField(field)) {
                isFormValid = false;
                this.errors[field.name] = true;
            }
        });

        return isFormValid;
    }

    showError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        // Buscar o crear mensaje de error
        let errorEl = field.parentElement.querySelector('.validation-feedback');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'validation-feedback invalid';
            field.parentElement.appendChild(errorEl);
        }
        errorEl.textContent = message;
    }

    showValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const errorEl = field.parentElement.querySelector('.validation-feedback');
        if (errorEl) {
            errorEl.textContent = '';
        }
    }

    clearError(field) {
        field.classList.remove('is-invalid');
        const errorEl = field.parentElement.querySelector('.validation-feedback');
        if (errorEl) {
            errorEl.textContent = '';
        }
    }

    showSummary() {
        const firstInvalid = this.form.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        if (window.Toast) {
            Toast.error('Por favor corrija los errores en el formulario');
        }
    }

    getData() {
        const formData = new FormData(this.form);
        const data = {};
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        return data;
    }
}

// Exportar para uso global
window.Validators = Validators;
window.FormValidator = FormValidator;
