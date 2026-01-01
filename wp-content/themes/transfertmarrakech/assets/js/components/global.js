/**
 * Global JavaScript functionality
 * Optimized for performance
 */

document.addEventListener("DOMContentLoaded", () => {
  const html = document.documentElement;
  
  // ============================================
  // Menu Toggle Functionality
  // ============================================
  const menuToggle = document.querySelector('.header__menu-toggle');
  const header = document.querySelector('.header');

  if (menuToggle && header) {
    menuToggle.addEventListener('click', () => {
      header.classList.toggle('nav-open');
      html.classList.toggle('lenis-stopped');
    });
  }

  // ============================================
  // Search Functionality
  // ============================================
  const search = document.querySelector('.search');
  
  if (search) {
    const searchToggle = document.querySelector('.header__search-toggle');
    const searchClose = document.querySelector('.search__close');
    const searchInput = document.getElementById('search-input');
    const searchForm = document.querySelector('.ais-SearchBox-form');

    /**
     * Opens the search modal
     */
    const openSearch = () => {
      search.classList.add('is-open');
      html.classList.add('lenis-stopped');
      
      // Focus input after transition
      setTimeout(() => {
        searchInput?.focus();
      }, 100);
    };

    /**
     * Closes the search modal
     */
    const closeSearch = () => {
      search.classList.remove('is-open');
      html.classList.remove('lenis-stopped');
      searchInput && (searchInput.value = '');
    };

    // Open search
    searchToggle?.addEventListener('click', (e) => {
      e.preventDefault();
      openSearch();
    });

    // Close search
    searchClose?.addEventListener('click', (e) => {
      e.preventDefault();
      closeSearch();
    });

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && search.classList.contains('is-open')) {
        closeSearch();
      }
    });

    // Close when clicking outside search inner
    search.addEventListener('click', (e) => {
      if (e.target === search) {
        closeSearch();
      }
    });

    // Handle form submission
    if (searchForm && searchInput) {
      searchForm.addEventListener('submit', (e) => {
        if (searchInput.value.trim().length < 2) {
          e.preventDefault();
          return false;
        }
      });
    }
  }

  // ============================================
  // Header Hide/Show on Scroll
  // Optimized with requestAnimationFrame and throttling
  // ============================================
  const headerElement = document.querySelector('.header');
  
  if (headerElement) {
    let lastScrollTop = 0;
    const scrollThreshold = 10;
    let ticking = false;

    const handleScroll = () => {
      if (ticking) return;
      
      ticking = true;
      window.requestAnimationFrame(() => {
        // Don't hide header if menu is open
        if (headerElement.classList.contains('nav-open')) {
          ticking = false;
          return;
        }

        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollDiff = Math.abs(currentScrollTop - lastScrollTop);

        // Only process if scrolled more than 5px
        if (scrollDiff < 5) {
          ticking = false;
          return;
        }

        // Update header visibility
        if (currentScrollTop > lastScrollTop && currentScrollTop > scrollThreshold) {
          // Scrolling down - hide header
          headerElement.classList.add('is-hidden');
          headerElement.classList.remove('is-visible');
        } else if (currentScrollTop < lastScrollTop) {
          // Scrolling up - show header
          headerElement.classList.remove('is-hidden');
          headerElement.classList.add('is-visible');
        }

        // If at top of page, always show header
        if (currentScrollTop <= scrollThreshold) {
          headerElement.classList.remove('is-hidden', 'is-visible');
        }

        lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
        ticking = false;
      });
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
  }
});