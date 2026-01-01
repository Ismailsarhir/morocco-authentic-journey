<?php
/**
 * Template part pour la liste des véhicules (réutilisable)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array  $vehicles Tableau des véhicules avec leurs données
 * @var string $title    Titre de la section (optionnel)
 */

if (! isset($vehicles) || empty($vehicles) || ! is_array($vehicles)) {
  return;
}

// Titre par défaut si non fourni
$section_title = $title ?? __('Véhicules disponibles', 'transfertmarrakech');
?>

<div class="modules">
  <div class="module vehiclesList">
    <div class="vehiclesList__inner">
      <h2 class="vehiclesList__title animated-title">
        <?php echo esc_html($section_title); ?>
      </h2>
      <div class="wrapper">
        <div class="boxes-container">
          <?php foreach ($vehicles as $vehicle_data) :
            if (! is_array($vehicle_data)) {
              continue;
            }
            
            $vehicle_id = $vehicle_data['vehicle_id'] ?? 0;
            $thumbnail = $vehicle_data['thumbnail'] ?? '';
            $title = $vehicle_data['title'] ?? '';
      
            if (empty($thumbnail) || empty($vehicle_id)) {
              continue;
            }
          ?>
            <div class="box">
              <div class="box-content" data-title="<?php echo esc_attr($title); ?>" style="background-image: url('<?php echo esc_url($thumbnail); ?>');"></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal">
        <div class="overlay"></div>
        <div class="content">
          <h3 class="modal__title"></h3>
        </div>
      </div>
    </div>
  </div>
</div>

