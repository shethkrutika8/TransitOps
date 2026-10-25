// Shared client-side validation for TransitOps forms.
const FormValidation = {
    messages: {
        valueMissing: 'Please complete this field.',
        typeMismatch: 'Enter a value in the correct format.',
        patternMismatch: 'Use the requested format.',
        tooShort: 'This value is too short.',
        tooLong: 'This value is too long.',
        rangeUnderflow: 'The value is below the allowed minimum.',
        rangeOverflow: 'The value is above the allowed maximum.',
        stepMismatch: 'Enter a value using the allowed increments.',
        badInput: 'Enter a valid value.',
        fileSize: 'The selected file is too large.',
        fileType: 'Choose a file type allowed for this field.',
        dateOrder: 'This date must be after the related date.'
    },

    messageFor: function(field) {
        const validity = field.validity;
        if (field.required && (field.type === 'checkbox' ? !field.checked : field.value.trim() === '')) {
            return this.messages.valueMissing;
        }
        if (validity.typeMismatch) {
            return field.type === 'email' ? 'Enter a valid email address.' : this.messages.typeMismatch;
        }
        if (validity.patternMismatch) return this.messages.patternMismatch;
        if (validity.tooShort) return this.messages.tooShort;
        if (validity.tooLong) return this.messages.tooLong;
        if (validity.rangeUnderflow) return `Enter a value of at least ${field.min}.`;
        if (validity.rangeOverflow) return `Enter a value no greater than ${field.max}.`;
        if (validity.stepMismatch) return this.messages.stepMismatch;
        if (validity.badInput) return this.messages.badInput;

        if (field.type === 'file' && field.files && field.files.length > 0) {
            const file = field.files[0];
            const maxBytes = Number(field.dataset.maxBytes || 0);
            if (maxBytes > 0 && file.size > maxBytes) return this.messages.fileSize;

            const accepts = (field.getAttribute('accept') || '')
                .split(',')
                .map(type => type.trim().toLowerCase())
                .filter(Boolean);
            if (accepts.length > 0) {
                const fileName = file.name.toLowerCase();
                const matches = accepts.some(type => {
                    if (type.endsWith('/*')) return file.type.toLowerCase().startsWith(type.slice(0, -1));
                    if (type.startsWith('.')) return fileName.endsWith(type);
                    return file.type.toLowerCase() === type;
                });
                if (!matches) return this.messages.fileType;
            }
        }

        const maxFromSelector = field.dataset.maxFrom;
        if (maxFromSelector && field.value) {
            const source = field.form.querySelector(maxFromSelector);
            const sourceOption = source && source.options ? source.options[source.selectedIndex] : null;
            const maximum = sourceOption ? Number(sourceOption.dataset.capacity) : NaN;
            if (Number.isFinite(maximum) && Number(field.value) > maximum) {
                return `This value cannot exceed the selected vehicle capacity of ${maximum}.`;
            }
        }

        const afterSelector = field.dataset.after;
        if (afterSelector && field.value) {
            const relatedField = field.form.querySelector(afterSelector);
            if (relatedField && relatedField.value && field.value <= relatedField.value) {
                return this.messages.dateOrder;
            }
        }

        const differentSelector = field.dataset.differentFrom;
        if (differentSelector && field.value) {
            const relatedField = field.form.querySelector(differentSelector);
            if (relatedField && field.value.trim().toLocaleLowerCase() === relatedField.value.trim().toLocaleLowerCase()) {
                return 'Choose a value different from the related field.';
            }
        }

        return '';
    },

    feedbackFor: function(field) {
        const id = field.id || field.name;
        if (!id) return null;

        let feedback = field.form.querySelector(`[data-validation-for="${CSS.escape(id)}"]`);
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'validation-feedback';
            feedback.dataset.validationFor = id;
            feedback.id = `${id}-validation-message`;
            const group = field.closest('.input-group');
            const anchor = group || field;
            anchor.insertAdjacentElement('afterend', feedback);
        }

        const describedBy = (field.getAttribute('aria-describedby') || '')
            .split(/\s+/)
            .filter(Boolean);
        if (!describedBy.includes(feedback.id)) {
            describedBy.push(feedback.id);
            field.setAttribute('aria-describedby', describedBy.join(' '));
        }
        return feedback;
    },

    validateField: function(field) {
        if (!(field instanceof HTMLElement) || field.disabled || field.type === 'hidden') return true;

        const message = this.messageFor(field);
        field.setCustomValidity(message);

        const feedback = this.feedbackFor(field);
        if (feedback) {
            feedback.textContent = message;
            feedback.hidden = message === '';
        }

        field.classList.toggle('is-invalid', message !== '');
        field.classList.toggle('is-valid', message === '' && field.value !== '');
        field.setAttribute('aria-invalid', message === '' ? 'false' : 'true');
        return message === '';
    },

    validateForm: function(form) {
        if (!(form instanceof HTMLFormElement)) return false;

        const fields = Array.from(form.elements).filter(field =>
            field instanceof HTMLElement &&
            typeof field.willValidate === 'boolean' &&
            field.willValidate
        );
        const firstInvalid = fields.find(field => !this.validateField(field));

        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.reportValidity();
            return false;
        }
        return true;
    },

    attach: function(form) {
        if (!(form instanceof HTMLFormElement) || form.dataset.validationAttached === 'true') return;
        form.dataset.validationAttached = 'true';
        form.noValidate = true;

        form.addEventListener('input', event => {
            if (event.target instanceof HTMLElement) this.validateField(event.target);
        });
        form.addEventListener('change', event => {
            if (event.target instanceof HTMLElement) this.validateField(event.target);
            form.querySelectorAll('[data-after]').forEach(field => this.validateField(field));
            form.querySelectorAll('[data-max-from], [data-different-from]').forEach(field => this.validateField(field));
        });
        form.addEventListener('submit', event => {
            if (!this.validateForm(form)) event.preventDefault();
        }, true);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(form => FormValidation.attach(form));
});
