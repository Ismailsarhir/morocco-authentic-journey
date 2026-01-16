/**
 * Contact Form Handler
 * Handles AJAX submission for contact form
 *
 * @package TransfertMarrakech
 * @since 1.0.0
 */

(function() {
	'use strict';

	const ContactForm = {
		/**
		 * Flag to prevent double submission
		 */
		isSubmitting: false,

		/**
		 * Initialize the contact form
		 */
		init: function() {
			const form = document.getElementById('contact-form');
			if (!form) return;

			form.addEventListener('submit', this.handleSubmit.bind(this));
		},

		/**
		 * Get DOM elements
		 */
		getElements: function() {
			const form = document.getElementById('contact-form');
			if (!form) return null;

			return {
				form: form,
				submitBtn: form.querySelector('#contact-submit-btn'),
				buttonText: form.querySelector('.cta__txt'),
				messageDiv: document.getElementById('contact-form-message')
			};
		},

		/**
		 * Handle form submission
		 */
		handleSubmit: function(e) {
			e.preventDefault();

			// Prevent double submission
			if (this.isSubmitting) {
				return;
			}

			const elements = this.getElements();
			if (!elements) return;

			// Set submitting flag
			this.isSubmitting = true;

			// Reset form state
			this.resetForm(elements);

			// Disable submit button
			this.setLoadingState(elements, true);

			// Prepare form data
			const formData = new FormData(elements.form);
			formData.append('action', 'tm_contact_form_submit');

			// Send AJAX request
			fetch(tmContactForm.ajaxUrl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			})
				.then(response => response.json())
				.then(data => this.handleResponse(data, elements))
				.catch(error => this.handleError(error, elements))
				.finally(() => {
					this.setLoadingState(elements, false);
					this.isSubmitting = false;
				});
		},

		/**
		 * Reset form state
		 */
		resetForm: function(elements) {
			// Reset message
			elements.messageDiv.style.display = 'none';
			elements.messageDiv.className = 'contact-form__message';
			elements.messageDiv.innerHTML = '';

			// Remove error classes
			elements.form.querySelectorAll('.contact-form__input--error').forEach(input => {
				input.classList.remove('contact-form__input--error');
			});

			// Remove all error messages (including any duplicates)
			elements.form.querySelectorAll('.contact-form__error').forEach(error => {
				error.remove();
			});

			// Clear browser validation messages
			elements.form.querySelectorAll('input, textarea').forEach(field => {
				field.setCustomValidity('');
			});
		},

		/**
		 * Set loading state
		 */
		setLoadingState: function(elements, isLoading) {
			elements.submitBtn.disabled = isLoading;
			if (elements.buttonText) {
				elements.buttonText.textContent = isLoading 
					? tmContactForm.sendingText 
					: tmContactForm.submitText;
			}
		},

		/**
		 * Handle successful response
		 */
		handleResponse: function(data, elements) {
			if (data.success) {
				this.showMessage(elements, data.data.message, 'success');
				elements.form.reset();
			} else {
				// Always show error message in contact-form__message div
				this.showMessage(elements, data.data.message, 'error');
				// Also show field-specific errors
				this.showFieldErrors(elements, data.data.errors || {});
			}
		},

		/**
		 * Handle error
		 */
		handleError: function(error, elements) {
			this.showMessage(elements, tmContactForm.errorText, 'error');
		},

		/**
		 * Show message
		 */
		showMessage: function(elements, message, type) {
			elements.messageDiv.className = `contact-form__message contact-form__message--${type}`;
			elements.messageDiv.innerHTML = `<p>${this.escapeHtml(message)}</p>`;
			elements.messageDiv.style.display = 'block';
			this.scrollToElement(elements.messageDiv);
		},

		/**
		 * Show field-specific errors
		 */
		showFieldErrors: function(elements, errors) {
			Object.keys(errors).forEach(fieldName => {
				const field = elements.form.querySelector(`[name="${fieldName}"]`);
				if (!field) return;

				field.classList.add('contact-form__input--error');
				
				// Find the field container (contact-form__field)
				const fieldContainer = field.closest('.contact-form__field');
				if (!fieldContainer) return;

				// Remove any existing error messages for this field
				const existingErrors = fieldContainer.querySelectorAll('.contact-form__error');
				existingErrors.forEach(error => error.remove());
				
				// Create new error message
				const errorMsg = document.createElement('span');
				errorMsg.className = 'contact-form__error';
				errorMsg.textContent = errors[fieldName];
				fieldContainer.appendChild(errorMsg);

				// Set custom validity to prevent browser validation message
				field.setCustomValidity(errors[fieldName]);
			});

			// Scroll to first error
			const firstError = elements.form.querySelector('.contact-form__input--error');
			if (firstError) {
				this.scrollToElement(firstError, 'center');
				firstError.focus();
			}
		},

		/**
		 * Scroll to element
		 */
		scrollToElement: function(element, block = 'nearest') {
			element.scrollIntoView({
				behavior: 'smooth',
				block: block
			});
		},

		/**
		 * Escape HTML to prevent XSS
		 */
		escapeHtml: function(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => ContactForm.init());
	} else {
		ContactForm.init();
	}
})();
