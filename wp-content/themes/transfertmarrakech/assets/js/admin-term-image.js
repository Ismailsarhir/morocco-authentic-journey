/**
 * Script pour gérer l'upload d'image pour les termes
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		var frame;
		var $container = $('.tm-term-image-container');
		
		if (!$container.length) {
			return;
		}

		// Bouton d'upload
		$container.on('click', '.tm-upload-image-button', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $preview = $container.find('.tm-term-image-preview img');
			var $input = $container.find('#tm_term_image');
			var $removeButton = $container.find('.tm-remove-image-button');

			// Si le frame existe déjà, on l'ouvre
			if (frame) {
				frame.open();
				return;
			}

			// Crée le frame
			frame = wp.media({
				title: 'Choisir une image',
				button: {
					text: 'Utiliser cette image'
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			// Quand une image est sélectionnée
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				
				$input.val(attachment.id);
				$preview.attr('src', attachment.url).show();
				$removeButton.show();
			});

			// Ouvre le frame
			frame.open();
		});

		// Bouton de suppression
		$container.on('click', '.tm-remove-image-button', function(e) {
			e.preventDefault();
			
			var $preview = $container.find('.tm-term-image-preview img');
			var $input = $container.find('#tm_term_image');
			var $removeButton = $(this);

			$input.val('');
			$preview.hide().attr('src', '');
			$removeButton.hide();
		});
	});

})(jQuery);

