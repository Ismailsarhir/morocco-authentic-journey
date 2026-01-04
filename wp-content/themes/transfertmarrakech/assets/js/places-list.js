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
  const cardsSection = document.querySelector('.placesList .cards');
  if (!cardsSection) return;

  const cards = gsap.utils.toArray('.placesList .card');
  if (cards.length === 0) return;

  const lastCard = cards[cards.length - 1];
  const scrollTriggers = [];

  // Animate each card (excluding last card to avoid overlapping pins)
  cards.slice(0, -1).forEach((card, index) => {
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

    // Animate card inner element
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

  // Cleanup on page unload
  window.addEventListener('beforeunload', () => {
    scrollTriggers.forEach(trigger => trigger?.kill());
    ScrollTrigger.refresh();
  });
});
