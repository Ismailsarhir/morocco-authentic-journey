/**
 * Gestion de la galerie d'images dans les meta boxes
 * 
 * @package TransfertMarrakech
 */

(function($) {
	'use strict';

	// Attend que wp.media soit disponible
	function initGallery() {
		if (typeof wp === 'undefined' || !wp.media) {
			setTimeout(initGallery, 100);
			return;
		}
		
		// Gestion du bouton "Gérer la galerie"
		$(document).on('click', '.tm-gallery-button', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var targetId = $button.data('target');
			var $input = $('#' + targetId);
			var $preview = $input.siblings('.tm-gallery-preview');
			var currentIds = $input.val() ? $input.val().split(',').filter(function(id) { 
				return id !== '' && id !== '0'; 
			}) : [];
			
			
			// Vérifie que wp.media est disponible
			if (typeof wp === 'undefined' || !wp.media) {
				alert('Erreur: Le sélecteur de médias WordPress n\'est pas disponible. Veuillez rafraîchir la page.');
				return;
			}
			
			// Crée la frame du sélecteur de médias (nouvelle instance à chaque fois)
			var galleryFrame = wp.media({
				title: 'Sélectionner les images de la galerie',
				button: {
					text: 'Utiliser ces images'
				},
				multiple: true,
				library: {
					type: 'image'
				}
			});
			
			// Définit les images déjà sélectionnées quand la frame s'ouvre
			galleryFrame.on('open', function() {
				var selection = galleryFrame.state().get('selection');
				selection.reset();
				
				// Ajoute les images déjà sélectionnées
				currentIds.forEach(function(id) {
					var attachment = wp.media.attachment(parseInt(id));
					if (attachment) {
						attachment.fetch().done(function() {
							selection.add(attachment);
						});
					}
				});
			});
			
			// Quand les images sont sélectionnées
			galleryFrame.on('select', function() {
				var selection = galleryFrame.state().get('selection');
				var ids = [];
				var previewHtml = '';
				
				selection.each(function(attachment) {
					var id = attachment.id;
					ids.push(id);
					
					// Récupère l'URL de l'image thumbnail
					var imageUrl = '';
					if (attachment.attributes.sizes && attachment.attributes.sizes.thumbnail) {
						imageUrl = attachment.attributes.sizes.thumbnail.url;
					} else if (attachment.attributes.url) {
						imageUrl = attachment.attributes.url;
					}
					
					// Construit le HTML de l'image
					previewHtml += '<div class="tm-gallery-item" style="display: inline-block; margin: 5px; position: relative;">';
					previewHtml += '<img src="' + imageUrl + '" style="width: 80px; height: 80px; object-fit: cover; display: block;" alt="">';
					previewHtml += '<button type="button" class="button-link tm-remove-image" data-id="' + id + '" style="position: absolute; top: 0; right: 0; background: rgba(0,0,0,0.7); color: white; border: none; cursor: pointer; padding: 2px 5px; font-size: 12px; line-height: 1;" title="Supprimer cette image">×</button>';
					previewHtml += '</div>';
				});
				
				// Met à jour le champ hidden
				$input.val(ids.join(','));
				
				// Met à jour la prévisualisation
				$preview.html(previewHtml);
				
			});
			
			// Ouvre la frame
			galleryFrame.open();
		});
		
		// Gestion de la suppression d'une image
		$(document).on('click', '.tm-remove-image', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var $button = $(this);
			var removeId = String($button.data('id'));
			
			// Trouve le champ input associé (cherche dans le parent le plus proche qui contient la galerie)
			var $galleryItem = $button.closest('.tm-gallery-item');
			var $preview = $galleryItem.closest('.tm-gallery-preview');
			var $input = $preview.siblings('.tm-gallery-ids');
			
			// Si on ne trouve pas avec siblings, cherche dans le parent p
			if ($input.length === 0) {
				$input = $preview.closest('p').find('.tm-gallery-ids');
			}
			
			// Si toujours pas trouvé, cherche par ID (tm_gallery)
			if ($input.length === 0) {
				$input = $('#tm_gallery');
			}
			
			if ($input.length === 0) {
				console.error('Champ input galerie non trouvé');
				return;
			}
			
			var currentIds = $input.val() ? $input.val().split(',').filter(function(id) { 
				id = String(id).trim();
				return id !== '' && id !== '0'; 
			}) : [];
			
			// Retire l'ID de la liste
			currentIds = currentIds.filter(function(id) {
				return String(id).trim() !== removeId;
			});
			
			// Met à jour le champ hidden
			var newValue = currentIds.length > 0 ? currentIds.join(',') : '';
			$input.val(newValue);
			
			// Retire l'image de la prévisualisation
			$galleryItem.fadeOut(200, function() {
				$(this).remove();
				
				// Si plus d'images, vide la prévisualisation
				if (currentIds.length === 0) {
					$preview.html('');
				}
			});
			
		});
	}
	
	// Initialise au chargement du DOM
	$(document).ready(function() {
		initGallery();
	});
	
})(jQuery);

