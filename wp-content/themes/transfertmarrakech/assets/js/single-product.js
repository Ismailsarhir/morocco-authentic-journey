/**
 * Single Product Page Animation
 * 
 * Scroll-triggered animations for single product pages (tours, transferts, etc.)
 * 
 * @package TransfertMarrakech
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    // Cache DOM elements
    const elements = {
      product: document.querySelector('main.product'),
      title: document.querySelector('.product__title'),
      card: document.querySelector('.product__card'),
      productBody: document.querySelector('.product-body'),
      banner: document.querySelector('.banner'),
      prefooterWrapper: document.querySelector('.prefooter__wrapper'),
    };

    // Early return if essential elements are missing
    if (!elements.product || !elements.title) {
      return;
    }

    const mediaQuery = window.matchMedia('(min-width: 768px)');
    const isMobile = () => !mediaQuery.matches; // Function to check mobile status dynamically
    const triggers = {
      title: null,
      card: null,
      bannerVisible: null,
      bannerLight: null,
    };

    // On mobile, skip ScrollTrigger entirely to prevent scroll interference
    // ScrollTrigger can cause scroll position to reset on mobile devices
    if (isMobile()) {
      // Don't initialize ScrollTrigger on mobile - use native scroll instead
      ScrollTrigger.config({
        autoRefreshEvents: '',
        ignoreMobileResize: true,
      });
    }

    // Kill specific ScrollTriggers by trigger element
    const killTriggersByElement = (element) => {
      if (!element) return;
      ScrollTrigger.getAll().forEach(trigger => {
        if (trigger.trigger === element || (trigger.vars?.trigger === element)) {
          trigger.kill();
        }
      });
    };

    // Setup desktop-only animations
    const setupDesktopAnimations = () => {
      if (!mediaQuery.matches) return;
      if (isMobile()) return; // Double check - don't run on mobile

      ScrollTrigger.refresh();

      // Kill existing triggers
      killTriggersByElement(elements.title);
      killTriggersByElement(elements.card);

      // Title animation
      gsap.set(elements.title, {
        x: 0,
        y: 0,
        scale: 1,
        transformOrigin: 'center center',
      });

      triggers.title = gsap.to(elements.title, {
        xPercent: 0,
        yPercent: -400,
        scale: 4,
        ease: 'none',
        scrollTrigger: {
          trigger: elements.product,
          start: 'top top',
          end: 'bottom top',
          scrub: 1,
          invalidateOnRefresh: true,
          anticipatePin: 1, // Improves performance on mobile
        },
      });

      // Card animation
      if (elements.card) {
        gsap.set(elements.card, { x: 0, y: 0 });

        triggers.card = gsap.to(elements.card, {
          y: -500,
          ease: 'none',
          scrollTrigger: {
            trigger: elements.product,
            start: 'top top',
            end: 'bottom top',
            scrub: 1,
            invalidateOnRefresh: true,
            anticipatePin: 1, // Improves performance on mobile
          },
        });
      }
    };

    // Setup banner visibility animation (mobile & desktop)
    const setupBannerVisibility = () => {
      if (!elements.productBody || !elements.banner) return;

      killTriggersByElement(elements.productBody);

      const placesListBlock = document.querySelector('.placesList');

      // On mobile, use IntersectionObserver instead of ScrollTrigger to avoid scroll interference
      if (isMobile()) {
        const observerOptions = {
          root: null,
          rootMargin: '0px',
          threshold: 0.1,
        };

        const handleIntersection = (entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              elements.banner.classList.add('is-visible');
            } else {
              elements.banner.classList.remove('is-visible');
            }
          });
        };

        const observer = new IntersectionObserver(handleIntersection, observerOptions);
        observer.observe(elements.productBody);

        if (placesListBlock) {
          const placesObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                elements.banner.classList.remove('is-visible');
              } else {
                elements.banner.classList.add('is-visible');
              }
            });
          }, observerOptions);
          placesObserver.observe(placesListBlock);
        }

        return;
      }

      // Desktop: Use ScrollTrigger
      triggers.bannerVisible = ScrollTrigger.create({
        trigger: elements.productBody,
        start: 'top bottom',
        onEnter: () => elements.banner.classList.add('is-visible'),
        onLeaveBack: () => elements.banner.classList.remove('is-visible'),
      });

      // Setup ScrollTrigger to hide banner when placesList is in view
      if (placesListBlock) {
        ScrollTrigger.create({
          trigger: placesListBlock,
          start: 'top bottom',
          end: 'bottom top',
          onEnter: () => elements.banner.classList.remove('is-visible'),
          onLeave: () => elements.banner.classList.add('is-visible'),
          onEnterBack: () => elements.banner.classList.remove('is-visible'),
          onLeaveBack: () => elements.banner.classList.add('is-visible'),
        });
      }
    };

    // Setup banner light class animation (mobile & desktop)
    const setupBannerLight = () => {
      if (!elements.prefooterWrapper || !elements.banner) return;

      killTriggersByElement(elements.prefooterWrapper);

      const checkBannerInPrefooter = () => {
        const bannerRect = elements.banner.getBoundingClientRect();
        const prefooterRect = elements.prefooterWrapper.getBoundingClientRect();
        const isInside = bannerRect.top >= prefooterRect.top && 
                        bannerRect.bottom <= prefooterRect.bottom;

        elements.banner.classList.toggle('is-light', isInside);
      };

      // On mobile, use scroll event listener instead of ScrollTrigger
      if (isMobile()) {
        let ticking = false;
        const handleScroll = () => {
          if (!ticking) {
            window.requestAnimationFrame(() => {
              checkBannerInPrefooter();
              ticking = false;
            });
            ticking = true;
          }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        // Initial check
        checkBannerInPrefooter();
        return;
      }

      // Desktop: Use ScrollTrigger
      triggers.bannerLight = ScrollTrigger.create({
        trigger: elements.prefooterWrapper,
        start: 'top bottom',
        end: 'bottom top',
        onEnter: checkBannerInPrefooter,
        onLeave: () => elements.banner.classList.remove('is-light'),
        onEnterBack: checkBannerInPrefooter,
        onLeaveBack: () => elements.banner.classList.remove('is-light'),
        onUpdate: (self) => {
          if (self.isActive) checkBannerInPrefooter();
        },
      });
    };

    // Main setup function
    const setupAnimations = () => {
      setupDesktopAnimations();
      setupBannerVisibility();
      setupBannerLight();
    };

    // Cleanup function
    const cleanup = () => {
      Object.values(triggers).forEach(trigger => {
        if (trigger) trigger.kill();
      });
      Object.keys(triggers).forEach(key => {
        triggers[key] = null;
      });
    };

    // Initial setup
    setupAnimations();

    // Handle media query changes
    mediaQuery.addEventListener('change', () => {
      cleanup();
      if (!isMobile() && mediaQuery.matches) {
        ScrollTrigger.refresh();
      }
      setupAnimations();
    });

    // Handle window resize with debounce
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        cleanup();
        // Only refresh ScrollTrigger on desktop
        if (!isMobile() && mediaQuery.matches) {
          ScrollTrigger.refresh();
        }
        setupAnimations();
      }, 250);
    });

  }, 1500);
});

