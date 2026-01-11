<?php
/**
 * Template part pour le carrousel des destinations
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $destinations Tableau des destinations avec leurs données
 */

if ( ! isset( $destinations ) || empty( $destinations ) ) {
	return;
}

$destinations_url = \home_url( '/destinations' );
?>

<div class="blue-bg">
	<div class="modules">
		<section class="module carrousel">
			<div class="carrousel__inner swiper">
				<div class="carrousel__top">
					<h2 class="carrousel__title is-animated">
						<?php \esc_html_e( 'Destinations', 'transfertmarrakech' ); ?>
					</h2>
					<div class="carrousel__nav">
						<button 
							type="button"
							class="cta primary left is-arrow swiper-button-prev in_desktop" 
							aria-label="<?php \esc_attr_e( 'Previous', 'transfertmarrakech' ); ?>"
						>
						</button>
						<button 
							type="button"
							class="cta primary right is-arrow swiper-button-next in_desktop"
							aria-label="<?php \esc_attr_e( 'Next', 'transfertmarrakech' ); ?>"
						>
						</button>
					</div>
				</div>
				<div class="swiper-wrapper">
					<?php foreach ( $destinations as $destination ) : 
						$term = $destination['term'];
						$image = $destination['image'];
						$name = $destination['name'];
						$url = $destination['url'];
						$image_alt = $destination['image_alt'];
					?>
						<div class="carrousel__slide swiper-slide">
							<a href="<?php echo \esc_url( $url ); ?>">
								<?php if ( $image ) : ?>
									<div class="carrousel__slide-img">
										<picture>
											<?php if ( ! empty( $image['srcset'] ) ) : ?>
												<source 
													srcset="<?php echo \esc_attr( $image['srcset'] ); ?>" 
													media="(max-width: 1400px)"
												>
											<?php endif; ?>
											<img 
												src="<?php echo \esc_url( $image['url'] ); ?>" 
												<?php if ( ! empty( $image['srcset'] ) ) : ?>
													srcset="<?php echo \esc_attr( $image['srcset'] ); ?>"
												<?php endif; ?>
												alt="<?php echo \esc_attr( $image_alt ); ?>" 
												decoding="async" 
												loading="lazy" 
												fetchpriority="low"
											>
										</picture>
									</div>
								<?php endif; ?>
								<h5>
									<?php echo \esc_html( $name ); ?>
								</h5>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</div>
</div>

