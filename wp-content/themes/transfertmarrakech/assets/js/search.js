/**
 * Search page functionality
 * Optimized for performance with efficient DOM operations
 */

(function() {
  'use strict';

  // Early exit if not on search page
  if (!document.body.classList.contains('search-body') && !document.body.classList.contains('search')) {
    return;
  }

  // Initialize only when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  function init() {
    animateResultsOnScroll();
    handleSearchFormSubmit();
  }

  /**
   * Animate results cards on scroll using Intersection Observer
   * Optimized with single observer instance
   */
  function animateResultsOnScroll() {
    const results = document.querySelectorAll('.tour-card, .circuit-card, .transfert-card');
    
    if (results.length === 0) {
      return;
    }

    const observerOptions = {
      root: null,
      rootMargin: '0px 0px -50px 0px',
      threshold: 0.1,
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          // Unobserve after animation to improve performance
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // Initialize cards with hidden state and observe
    results.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = `opacity 0.5s ease-out ${index * 0.05}s, transform 0.5s ease-out ${index * 0.05}s`;
      observer.observe(card);
    });
  }

  /**
   * Smooth scroll to results when submitting search form
   * Only if form is submitted from header/hero
   */
  function handleSearchFormSubmit() {
    const forms = document.querySelectorAll('.ais-SearchBox-form');
    
    forms.forEach((form) => {
      form.addEventListener('submit', (e) => {
        const input = form.querySelector('input[type="search"]');
        if (input && input.value.trim().length >= 2) {
          // Only scroll if we're already on the search page
          if (document.body.classList.contains('search-body') || document.body.classList.contains('search')) {
            // Delay to ensure page is processing
            requestAnimationFrame(() => {
              const searchHero = document.querySelector('.search-hero');
              if (searchHero) {
                const heroHeight = searchHero.offsetHeight;
                window.scrollTo({
                  top: heroHeight,
                  behavior: 'smooth',
                });
              }
            });
          }
        }
      }, { passive: true });
    });
  }
})();
