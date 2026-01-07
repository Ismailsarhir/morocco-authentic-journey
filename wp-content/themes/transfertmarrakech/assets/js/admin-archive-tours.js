/**
 * Admin script for Archive Tours Settings
 * Handles image upload using WordPress Media Uploader
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Image upload button
		$('.tm-archive-image-button').on('click', function(e) {
			e.preventDefault();
			
			var button = $(this);
			var targetId = button.data('target');
			var input = $('#' + targetId);
			var preview = button.siblings('.tm-archive-image-preview');
			
			// Create media frame
			var frame = wp.media({
				title: 'Sélectionner une image',
				button: {
					text: 'Utiliser cette image'
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});
			
			// When image is selected
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				input.val(attachment.id);
				
				// Update preview
				var img = $('<img>').attr({
					src: attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url,
					style: 'max-width: 300px; height: auto; display: block; margin-bottom: 10px;'
				});
				preview.html(img);
				
				// Update button text
				button.text('Changer l\'image');
				
				// Show remove button if not already present
				if (preview.siblings('.tm-archive-image-remove').length === 0) {
					var removeBtn = $('<button>')
						.attr({
							type: 'button',
							class: 'button tm-archive-image-remove',
							'data-target': targetId,
							style: 'margin-left: 10px;'
						})
						.text('Supprimer');
					button.after(removeBtn);
				}
			});
			
			// Open media frame
			frame.open();
		});
		
		// Remove image button
		$(document).on('click', '.tm-archive-image-remove', function(e) {
			e.preventDefault();
			
			var button = $(this);
			var targetId = button.data('target');
			var input = $('#' + targetId);
			var preview = button.siblings('.tm-archive-image-preview');
			var uploadBtn = button.siblings('.tm-archive-image-button');
			
			// Clear values
			input.val('');
			preview.html('');
			uploadBtn.text('Sélectionner une image');
			button.remove();
		});
	});
	
})(jQuery);

