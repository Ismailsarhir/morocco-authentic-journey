<?php
/**
 * Header class for rendering the site header
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Utils\HeaderHelper;

/**
 * Classe pour gérer le rendu du header
 */
class Header {
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var Header|null
	 */
	private static ?Header $instance = null;
	
	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		// Constructor privé pour pattern Singleton
	}
	
	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return Header
	 */
	public static function get_instance(): Header {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Rend le header complet
	 * 
	 * @return void
	 */
	public function render(): void {
		?>
		<header class="header">
			<?php $this->render_logo(); ?>
			<div class="header__right">
				<?php $this->render_header_actions(); ?>
			</div>
			<?php $this->render_mobile_nav(); ?>
		</header>
		<?php $this->render_search(); ?>
		<?php
	}
	
	/**
	 * Rend le logo
	 * 
	 * @param string $link_class Classe CSS pour le lien (par défaut: 'header__logo-link')
	 * @param string $img_class Classe CSS pour l'image (par défaut: 'header__logo')
	 * @return void
	 */
	public function render_logo( string $link_class = 'header__logo-link', string $img_class = 'header__logo' ): void {
		$logo_url = HeaderHelper::get_logo_url();
		$home_url = home_url( '/' );
		?>
		<a href="<?php echo esc_url( $home_url ); ?>" class="<?php echo esc_attr( $link_class ); ?>">
			<img src="<?php echo esc_url( $logo_url ); ?>" class="<?php echo esc_attr( $img_class ); ?>" alt="<?php bloginfo( 'name' ); ?>">
		</a>
		<?php
	}
	
	/**
	 * Rend les actions du header (recherche, portail agents, menu toggle)
	 * 
	 * @return void
	 */
	private function render_header_actions(): void {
		$search_icon_url = HeaderHelper::get_search_icon_url();
		$agent_portal_url = HeaderHelper::get_agent_portal_url();
		?>
		<button class="header__search-toggle" aria-label="<?php esc_attr_e( 'Recherche', 'transfertmarrakech' ); ?>">
			<img src="<?php echo esc_url( $search_icon_url ); ?>" alt="<?php esc_attr_e( 'Recherche', 'transfertmarrakech' ); ?>">
		</button>

		<a 
			target="_blank" 
			href="<?php echo \esc_url( $agent_portal_url ); ?>" 
			class="cta in_desktop is-fixed is-small"
			rel="noopener noreferrer"
		>
			<span class="cta__inner" data-label="<?php \esc_attr_e( 'Réservez Maintenant', 'transfertmarrakech' ); ?>">
				<span class="cta__txt">
					<?php \esc_html_e( 'Réservez Maintenant', 'transfertmarrakech' ); ?>
				</span>
			</span>
		</a>

		<button class="header__menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'transfertmarrakech' ); ?>">
			<span></span>
			<span></span>
			<span></span>
		</button>
		<?php
	}
	
	/**
	 * Rend la navigation mobile depuis le menu WordPress (main-header)
	 * 
	 * @return void
	 */
	private function render_mobile_nav(): void {
		if ( ! \has_nav_menu( 'main-header' ) ) {
			return;
		}
		?>
		<nav class="nav">
			<div class="nav__inner">
				<?php
				\wp_nav_menu( [
					'theme_location' => 'main-header',
					'container'      => false,
					'fallback_cb'    => false,
				] );
				?>
			</div>
		</nav>
		<?php
	}
	
	/**
	 * Rend le formulaire de recherche optimisé
	 * 
	 * @return void
	 */
	private function render_search(): void {
		$search_url = \home_url( '/?s=' );
		?>
		<search class="search" data-lenis-prevent="" role="search">
			<div class="search__inner">
				<?php $this->render_search_form(); ?>
			</div>
			<button 
				type="button" 
				class="search__close" 
				aria-label="<?php \esc_attr_e( 'Close search', 'transfertmarrakech' ); ?>"
			>
				<span></span>
			</button>
		</search>
		<?php
	}
	
	/**
	 * Rend le formulaire de recherche (réutilisable)
	 * 
	 * @param string $input_id ID unique pour l'input (par défaut: 'search-input')
	 * @return void
	 */
	public function render_search_form( string $input_id = 'search-input' ): void {
		$search_url = \home_url( '/?s=' );
		?>
		<form 
			class="ais-SearchBox-form" 
			action="<?php echo \esc_url( $search_url ); ?>" 
			method="get" 
			role="search"
			novalidate
		>
			<label class="search-bar__label results">
				<div class="search-bar__label-title">
					<?php \esc_html_e( 'Rechercher', 'transfertmarrakech' ); ?>
				</div>
				<div class="instantsearch search-bar__label-value">
					<div class="ais-SearchBox">
						<input 
							class="ais-SearchBox-input" 
							type="search" 
							name="s"
							id="<?php echo \esc_attr( $input_id ); ?>"
							placeholder="<?php \esc_attr_e( 'Une région, une ville...', 'transfertmarrakech' ); ?>" 
							autocomplete="off" 
							autocorrect="off" 
							autocapitalize="off" 
							spellcheck="false" 
							maxlength="512" 
							aria-label="<?php \esc_attr_e( 'Search', 'transfertmarrakech' ); ?>"
							required
						>
						<button 
							class="ais-SearchBox-submit" 
							type="submit" 
							aria-label="<?php \esc_attr_e( 'Submit the search query', 'transfertmarrakech' ); ?>"
						>
							<?php $this->render_search_icon(); ?>
						</button>
					</div>
				</div>
			</label>
		</form>
		<?php
	}
	
	/**
	 * Rend l'icône de recherche SVG
	 * 
	 * @return void
	 */
	public function render_search_icon(): void {
		?>
		<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="none" aria-hidden="true">
			<circle cx="11.3137" cy="11.3137" r="7" transform="rotate(-45 11.3137 11.3137)" stroke="#0A254D" stroke-width="2" stroke-linejoin="round"/>
			<path d="M21.2115 22.6274C21.602 23.0179 22.2352 23.0179 22.6257 22.6274C23.0162 22.2369 23.0162 21.6037 22.6257 21.2132L21.2115 22.6274ZM15.5546 16.9705L21.2115 22.6274L22.6257 21.2132L16.9688 15.5563L15.5546 16.9705Z" fill="#0A254D"/>
		</svg>
		<?php
	}
}
