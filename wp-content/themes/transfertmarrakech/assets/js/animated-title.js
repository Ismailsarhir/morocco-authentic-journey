/**
 * Animated Title - Character by Character Scroll Animation with GSAP
 * 
 * @package TransfertMarrakech
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

document.addEventListener('DOMContentLoaded', () => {
  const animatedTitles = document.querySelectorAll('.animated-title');
  
  if (animatedTitles.length === 0) {
    return;
  }

  /**
   * Splits text into characters and wraps each in a span with 'char' class
   */
  const splitTextIntoChars = (element) => {
    const text = element.textContent.trim();
    
    element.innerHTML = '';
    
    // Split text into individual characters, preserving spaces
    text.split('').forEach((char) => {
      const charSpan = document.createElement('span');
      charSpan.className = 'char';
      charSpan.style.display = 'inline-block';
      charSpan.textContent = char === ' ' ? '\u00A0' : char; // Use non-breaking space for regular spaces
      element.appendChild(charSpan);
    });
  };

  /**
   * Animates characters using GSAP fromTo animation
   */
  const animateTitle = (element) => {
    const chars = element.querySelectorAll('.char');
    
    if (chars.length === 0) {
      return;
    }
    
    // Determine ScrollTrigger start position
    const offset = element.dataset.offset;
    const startPosition = offset 
      ? `top+=${offset} bottom` 
      : 'top 75%';
    
    // Animate characters from 145% y position to 0
    gsap.fromTo(chars, {
      y: '145%'
    }, {
      y: '0',
      stagger: 0.03,
      duration: 0.8,
      ease: 'circ.out',
      scrollTrigger: {
        trigger: element,
        start: startPosition,
        toggleActions: 'play none none none',
        once: true,
        markers: false
      }
    });
  };

  // Initialize all animated titles
  animatedTitles.forEach((title) => {
    // Only split if not already split (prevents re-splitting on re-render)
    if (!title.querySelector('.char')) {
      splitTextIntoChars(title);
      animateTitle(title);
    }
  });
});

