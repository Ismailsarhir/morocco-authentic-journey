/**
 * Single Tours Page Animation
 * 
 * Scroll-triggered animations for single tour pages
 * 
 * @package TransfertMarrakech
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

document.addEventListener('DOMContentLoaded', () => {
  // Wait a bit for the page to be fully loaded
  setTimeout(() => {
    const product = document.querySelector('main.product');
    const title = document.querySelector('.product__title');
    const card = document.querySelector('.product__card');
    console.log('Product title found');
    
    if (!product || !title) {
      console.log('Product or title not found');
      return;
    }

    // Create a media query for desktop (min-width: 768px)
    const mediaQuery = window.matchMedia('(min-width: 768px)');
    
    const setupAnimations = () => {
      // Kill any existing ScrollTriggers for these elements
      ScrollTrigger.getAll().forEach(trigger => {
        if (trigger.trigger === title || (trigger.vars && trigger.vars.trigger === title)) {
          trigger.kill();
        }
        if (card && (trigger.trigger === card || (trigger.vars && trigger.vars.trigger === card))) {
          trigger.kill();
        }
      });

      if (mediaQuery.matches) {
        // Refresh ScrollTrigger to recalculate positions
        ScrollTrigger.refresh();
        
        // Set initial state: translate(0px, 0px) - no scale, no translate percentage
        gsap.set(title, {
          x: 0,
          y: 0,
          scale: 1,
          transformOrigin: 'center center'
        });
        
        // Animate title on scroll based on product element scroll position
        // Final state: translate(0%, -200%) scale(2, 2)
        gsap.to(title, {
          xPercent: 0,
          yPercent: -400,
          scale: 4,
          ease: 'none', // Linear easing for smooth scroll-linked animation
          scrollTrigger: {
            trigger: product,
            start: 'top top',
            end: 'bottom top',
            scrub: 1, // Smooth scrubbing (1 second lag for smoother feel)
            invalidateOnRefresh: true
          }
        });

        // Animate product card on scroll
        if (card) {
          // Set initial state: translate(0px, 0px)
          gsap.set(card, {
            x: 0,
            y: 0
          });

          // Final state: translate(0px, -300px)
          gsap.to(card, {
            y: -500,
            ease: 'none', // Linear easing for smooth scroll-linked animation
            scrollTrigger: {
              trigger: product,
              start: 'top top',
              end: 'bottom top',
              scrub: 1, // Smooth scrubbing (1 second lag for smoother feel)
              invalidateOnRefresh: true
            }
          });
        }
      }
    };

    // Setup animations initially
    setupAnimations();

    // Re-setup animations when media query changes
    mediaQuery.addEventListener('change', () => {
      ScrollTrigger.refresh();
      setupAnimations();
    });

    // Refresh on window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        ScrollTrigger.refresh();
        setupAnimations();
      }, 250);
    });
  }, 1500);
});

