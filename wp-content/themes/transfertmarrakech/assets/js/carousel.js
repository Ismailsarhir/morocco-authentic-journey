/**
 * Carousel functionality for destinations
 * Includes Swiper carousel and horizontal parallax effect
 * Loaded only on home page
 */

import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';

document.addEventListener("DOMContentLoaded", () => {
  // ============================================
  // Destinations Carousel
  // ============================================
  const destinationsCarousel = document.querySelector('.carrousel__inner.swiper');
  
  if (destinationsCarousel) {
    new Swiper(destinationsCarousel, {
      modules: [Navigation],
      slidesPerView: 3.1,
      spaceBetween: 20,
      parallax: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      breakpoints: {
        320: {
          slidesPerView: 1.1,
          spaceBetween: 10,
        },
        768: {
          slidesPerView: 2.1,
          spaceBetween: 15,
        },
        1024: {
          slidesPerView: 3.1,
          spaceBetween: 20,
        },
        1400: {
          slidesPerView: 3.1,
          spaceBetween: 20,
        },
      },
    });
  }
});

