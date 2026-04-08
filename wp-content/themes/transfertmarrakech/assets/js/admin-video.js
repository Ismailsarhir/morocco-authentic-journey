/**
 * Gestion de la sélection de vidéo dans les meta boxes
 * 
 * @package TransfertMarrakech
 */

(function($) {
	'use strict';

	// Attend que wp.media soit disponible
	function initVideo() {
		if (typeof wp === 'undefined' || !wp.media) {
			setTimeout(initVideo, 100);
			return;
		}
		
		// Gestion du bouton "Sélectionner une vidéo"
		$(document).on('click', '.tm-video-button', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var targetId = $button.data('target');
			var $input = $('#' + targetId);
			var $preview = $input.siblings('.tm-video-preview');
			var currentId = $input.val() ? parseInt($input.val()) : 0;
			
			// Vérifie que wp.media est disponible
			if (typeof wp === 'undefined' || !wp.media) {
				console.error('wp.media not available');
				alert('Error: WordPress media selector is not available. Please refresh the page.');
				return;
			}
			
			// Crée la frame du sélecteur de médias
			var videoFrame = wp.media({
				title: 'Select Video',
				button: {
					text: 'Use this video'
				},
				multiple: false
			});
			
			// Définit la vidéo déjà sélectionnée quand la frame s'ouvre
			videoFrame.on('open', function() {
				var selection = videoFrame.state().get('selection');
				selection.reset();
				
				// Ajoute la vidéo déjà sélectionnée
				if (currentId > 0) {
					var attachment = wp.media.attachment(currentId);
					if (attachment) {
						attachment.fetch().done(function() {
							selection.add(attachment);
						});
					}
				}
			});
			
			// Quand la vidéo est sélectionnée
			videoFrame.on('select', function() {
				var selection = videoFrame.state().get('selection');
				var attachment = selection.first().toJSON();
				
				if (!attachment) {
					alert('Please select a file.');
					return;
				}
				
				// Vérifie que c'est bien une vidéo (par type ou mime type)
				var isVideo = attachment.type === 'video' || 
				             (attachment.mime && attachment.mime.indexOf('video/') === 0);
				
				if (!isVideo) {
					alert('Please select a video file. The selected file is not a video.');
					return;
				}
				
				var videoId = attachment.id;
				var videoUrl = attachment.url;
				var videoTitle = attachment.title || attachment.filename || 'Video';
				
				// Met à jour le champ hidden
				$input.val(videoId);
				
				// Met à jour la prévisualisation
				var previewHtml = '<div class="tm-video-item" style="position: relative;">';
				previewHtml += '<video src="' + videoUrl + '" controls style="max-width: 100%; max-height: 200px; display: block;" preload="metadata"></video>';
				previewHtml += '<p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">' + videoTitle + '</p>';
				previewHtml += '</div>';
				
				$preview.html(previewHtml);
				
				// Affiche le bouton "Remove Video" si nécessaire
				var $removeButton = $button.siblings('.tm-remove-video');
				if ($removeButton.length === 0) {
					$button.after('<button type="button" class="button tm-remove-video" data-target="' + targetId + '">Remove Video</button>');
				} else {
					$removeButton.show();
				}
			});
			
			// Ouvre la frame
			try {
				videoFrame.open();
			} catch (error) {
				console.error('Error opening media frame:', error);
				alert('Error opening media library. Please refresh the page and try again.');
			}
		});
		
		// Gestion de la suppression de la vidéo
		$(document).on('click', '.tm-remove-video', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var targetId = $button.data('target');
			var $input = $('#' + targetId);
			var $preview = $input.siblings('.tm-video-preview');
			
			// Vide le champ hidden
			$input.val('');
			
			// Vide la prévisualisation
			$preview.html('');
			
			// Cache le bouton "Remove Video"
			$button.hide();
		});
	}
	
	// Initialise au chargement du DOM
	$(document).ready(function() {
		initVideo();
	});
	
	// Également initialise après que WordPress media soit chargé
	if (typeof wp !== 'undefined' && wp.media) {
		initVideo();
	}
	
})(jQuery);
