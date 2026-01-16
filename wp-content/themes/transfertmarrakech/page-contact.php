<?php

/**
 * Template for Contact Page
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

if (! defined('ABSPATH')) {
	exit;
}

get_header();

// Get current page
$page = \TM\Utils\MetaHelper::get_current_post();
if (! $page instanceof \WP_Post) {
	get_footer();
	return;
}

// Extract page data
$title = \TM\Utils\MetaHelper::get_post_title($page);

// Contact information
$contact_email = 'contact@transfert-marrakech.com';
$contact_address = 'Boulevard el Mansour Eddahbi - Marrakech, Morocco';
$opening_hours = sprintf(
	'%s<br>%s',
	esc_html__('Monday to Sunday', 'transfertmarrakech'),
	esc_html__('24 Hours', 'transfertmarrakech')
);
?>

<main>
	<div class="contact__header">
		<h1 class="contact__title animated-title"><?php echo esc_html($title); ?></h1>
	</div>

	<div class="contact__body">
		<div class="contact__infos">
			<div class="contact__info">
				<h5 class="contact__info-title">
					<?php echo esc_html__('Address', 'transfertmarrakech'); ?>
				</h5>
				<p><?php echo wp_kses_post(nl2br(esc_html($contact_address))); ?></p>
			</div>
			<div class="contact__info">
				<h5 class="contact__info-title">
					<?php echo esc_html__('Opening Hours', 'transfertmarrakech'); ?>
				</h5>
				<p><?php echo wp_kses_post($opening_hours); ?></p>
			</div>
		</div>

		<div class="contact__form">
			<form id="contact-form" class="contact-form" method="post" novalidate>
				<?php wp_nonce_field('contact_form_submit', 'contact_form_nonce'); ?>
				<input type="hidden" name="action" value="tm_contact_form_submit">

				<div class="contact-form__field">
					<h5 class="contact-form__heading"><?php echo esc_html__('Write to Us', 'transfertmarrakech'); ?></h5>
				</div>

				<div class="contact-form__row">
					<div class="contact-form__field">
						<label class="contact-form__label" for="contact-name">
							<?php echo esc_html__('Full Name', 'transfertmarrakech'); ?>
							<span class="contact-form__required" aria-hidden="true">*</span>
						</label>
						<input
							type="text"
							id="contact-name"
							name="contact_name"
							class="contact-form__input"
							required
							aria-required="true"
							autocomplete="name">
					</div>

					<div class="contact-form__field">
						<label class="contact-form__label" for="contact-email">
							<?php echo esc_html__('Email', 'transfertmarrakech'); ?>
							<span class="contact-form__required" aria-hidden="true">*</span>
						</label>
						<input
							type="email"
							id="contact-email"
							name="contact_email"
							class="contact-form__input"
							required
							aria-required="true"
							autocomplete="email">
					</div>
				</div>

				<div class="contact-form__row">
					<div class="contact-form__field">
						<label class="contact-form__label" for="contact-phone">
							<?php echo esc_html__('Phone', 'transfertmarrakech'); ?>
						</label>
						<input
							type="tel"
							id="contact-phone"
							name="contact_phone"
							class="contact-form__input"
							autocomplete="tel">
					</div>
				</div>

				<div class="contact-form__row">
					<div class="contact-form__field">
						<label class="contact-form__label" for="contact-message">
							<?php echo esc_html__('Message', 'transfertmarrakech'); ?>
							<span class="contact-form__required" aria-hidden="true">*</span>
						</label>
						<textarea
							id="contact-message"
							name="contact_message"
							class="contact-form__textarea"
							required
							aria-required="true"
							rows="5"></textarea>
					</div>
				</div>

				<div id="contact-form-message" class="contact-form__message" style="display: none;" role="alert" aria-live="polite"></div>
				
				<div class="contact-form__submit">
					<button type="submit" class="cta primary" id="contact-submit-btn">
						<span class="cta__inner" data-label="<?php echo esc_attr__('Submit', 'transfertmarrakech'); ?>">
							<span class="cta__txt"><?php echo esc_html__('Submit', 'transfertmarrakech'); ?></span>
						</span>
					</button>
				</div>

			</form>
		</div>
	</div>
</main>

<?php
get_footer();
