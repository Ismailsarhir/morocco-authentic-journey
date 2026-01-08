<?php get_header(); ?>

<?php
// Affiche le Hero si disponible
\TM\Core\Hero::get_instance()->render();
?>

<?php
// Affiche le carrousel des destinations si disponible
\TM\Core\DestinationsCarousel::get_instance()->render();
?>

<?php
// Affiche la liste des tours vedettes si disponible
\TM\Core\ToursList::get_instance()->render();
?>

<?php
// Affiche la section Featured Text si disponible
\TM\Core\FeaturedText::get_instance()->render();
?>

<?php
// Affiche la liste des circuits vedettes si disponible
\TM\Core\CircuitsList::get_instance()->render();
?>

<?php
// Affiche la liste des véhicules vedettes si disponible
\TM\Core\VehiclesList::get_instance()->render();
?>

<?php
// Affiche la liste des transferts vedettes si disponible
\TM\Core\TransfersList::get_instance()->render();
?>

<?php get_footer(); ?>
