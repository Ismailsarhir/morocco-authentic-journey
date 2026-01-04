/**
 * LinksList - Toggle functionality for accordion-style lists
 * 
 * @package TransfertMarrakech
 */

document.addEventListener('DOMContentLoaded', () => {
  // Event delegation for linksList toggle functionality
  document.addEventListener('click', (e) => {
    const link = e.target.closest('.linksList__link');
    if (!link) return;
    
    e.preventDefault();
    const wrapper = link.closest('.linksList__link-wrapper');
    if (wrapper) {
      wrapper.classList.toggle('is-open');
    }
  });
});

