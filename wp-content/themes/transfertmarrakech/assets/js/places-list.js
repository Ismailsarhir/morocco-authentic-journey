/**
 * PlacesList - Sticky Cards Animation with GSAP ScrollTrigger
 * 
 * Creates smooth sticky card animation for itinerary places.
 * 
 * @package TransfertMarrakech
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

document.addEventListener('DOMContentLoaded', () => {
  // Check if mobile - disable ScrollTrigger pinning on mobile to prevent scroll interference
  const isMobile = window.matchMedia('(max-width: 767px)').matches;
  
  if (isMobile) {
    // On mobile, skip ScrollTrigger pinning which causes scroll issues
    // Cards will display normally without sticky/pin effects
    return;
  }

  const scrollTriggers = [];

  // Handle all placesList sections (both tours and circuit days)
  // Each .placesList section should have independent card animations
  const placesListSections = document.querySelectorAll('.placesList');
  
  placesListSections.forEach((placesListSection) => {
    const cardsSection = placesListSection.querySelector('.cards');
    if (!cardsSection) return;

    // Get cards only within this specific placesList section
    const cards = gsap.utils.toArray(placesListSection.querySelectorAll('.card'));
    if (cards.length === 0) return;

    const lastCard = cards[cards.length - 1];

    // Animate each card in this section independently
    cards.forEach((card, index) => {
      const cardInner = card.querySelector('.card-inner');
      if (!cardInner) return;

      const scrollConfig = {
        trigger: card,
        start: 'top 35%',
        endTrigger: lastCard,
        end: 'top 30%',
      };

      // Pin the card
      scrollTriggers.push(
        ScrollTrigger.create({
          ...scrollConfig,
          pin: true,
          pinSpacing: false,
        })
      );
      
      scrollTriggers.push(
        gsap.to(cardInner, {
          y: `-${(cards.length - index) * 14}vh`,
          ease: 'none',
          scrollTrigger: {
            ...scrollConfig,
            scrub: true,
          },
        }).scrollTrigger
      );
    });
  });

  // Cleanup on page unload
  window.addEventListener('beforeunload', () => {
    scrollTriggers.forEach(trigger => trigger?.kill());
    if (!isMobile) {
      ScrollTrigger.refresh();
    }
  });
});
