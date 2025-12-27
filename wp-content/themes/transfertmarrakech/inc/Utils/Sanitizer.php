<?php
/**
 * Classe utilitaire pour la sanitization
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Utils;

/**
 * Classe pour centraliser les fonctions de sanitization
 */
class Sanitizer {
	
	/**
	 * Sanitize un prix (décimal)
	 * 
	 * @param mixed $value Valeur à sanitizer
	 * @return string Prix formaté avec 2 décimales
	 */
	public static function sanitize_price( $value ): string {
		$float = \floatval( $value );
		return \number_format( $float, 2, '.', '' );
	}
	
	/**
	 * Sanitize une galerie (array d'IDs)
	 * 
	 * @param mixed $value Valeur à sanitizer
	 * @return array Array d'IDs d'attachments
	 */
	public static function sanitize_gallery( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}
		return \array_map( '\absint', $value );
	}
	
	/**
	 * Sanitize des IDs de posts
	 * 
	 * @param mixed $value Valeur à sanitizer
	 * @return array Array d'IDs de posts
	 */
	public static function sanitize_post_ids( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}
		return \array_map( '\absint', $value );
	}
	
	/**
	 * Sanitize un entier positif
	 * 
	 * @param mixed $value Valeur à sanitizer
	 * @return int Entier positif
	 */
	public static function sanitize_positive_int( $value ): int {
		return \absint( $value );
	}
	
	/**
	 * Sanitize un booléen
	 * 
	 * @param mixed $value Valeur à sanitizer
	 * @return bool
	 */
	public static function sanitize_boolean( $value ): bool {
		return \rest_sanitize_boolean( $value );
	}
}

