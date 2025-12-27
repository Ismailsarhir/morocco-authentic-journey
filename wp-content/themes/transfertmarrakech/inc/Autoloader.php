<?php
/**
 * Autoloader PSR-4 simplifié pour le namespace TM\
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM;

/**
 * Classe Autoloader pour charger automatiquement les classes du namespace TM\
 */
class Autoloader {
	
	/**
	 * Chemin de base du namespace
	 * 
	 * @var string
	 */
	private string $base_path;
	
	/**
	 * Namespace de base
	 * 
	 * @var string
	 */
	private string $namespace;
	
	/**
	 * Constructeur
	 * 
	 * @param string $base_path Chemin vers le dossier inc/
	 * @param string $namespace Namespace de base (ex: 'TM')
	 */
	public function __construct( string $base_path, string $namespace = 'TM' ) {
		$this->base_path = rtrim( $base_path, '/' ) . '/';
		$this->namespace = $namespace;
	}
	
	/**
	 * Enregistre l'autoloader
	 * 
	 * @return void
	 */
	public function register(): void {
		spl_autoload_register( [ $this, 'load_class' ] );
	}
	
	/**
	 * Charge une classe si elle appartient au namespace
	 * 
	 * @param string $class_name Nom complet de la classe avec namespace
	 * @return void
	 */
	public function load_class( string $class_name ): void {
		// Vérifie si la classe appartient au namespace
		if ( strpos( $class_name, $this->namespace . '\\' ) !== 0 ) {
			return;
		}
		
		// Supprime le namespace de base
		$relative_class = substr( $class_name, strlen( $this->namespace . '\\' ) );
		
		// Convertit les backslashes en slashes et ajoute .php
		$file_path = $this->base_path . str_replace( '\\', '/', $relative_class ) . '.php';
		
		// Charge le fichier s'il existe
		if ( \file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
}

