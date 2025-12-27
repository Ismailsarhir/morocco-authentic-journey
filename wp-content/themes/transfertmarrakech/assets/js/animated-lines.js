/**
 * Animated Lines - Line by Line Scroll Animation with GSAP SplitText
 * 
 * @package TransfertMarrakech
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(SplitText, ScrollTrigger);

document.addEventListener('DOMContentLoaded', () => {
  const animatedLines = document.querySelectorAll('.animated-lines');
  
  if (animatedLines.length === 0) {
    return;
  }

  // Set initial opacity for animated lines
  gsap.set(animatedLines, { opacity: 1 });

  /**
   * Splits text into lines using SplitText and wraps each line in a line-wrapper div
   */
  const splitTextIntoLines = (element) => {
    // Only split if not already split (prevents re-splitting on re-render)
    if (element.querySelector('.line')) {
      return;
    }

    SplitText.create(element, {
      type: 'words,lines',
      linesClass: 'line',
      autoSplit: true,
      onSplit: (instance) => {
        // Wrap each line in a line-wrapper div with overflow hidden
        instance.lines.forEach((line) => {
          const lineWrapper = document.createElement('div');
          lineWrapper.classList.add('line-wrapper');
          lineWrapper.style.overflow = 'hidden';
          line.parentNode.insertBefore(lineWrapper, line);
          lineWrapper.appendChild(line);
        });
      }
    });
  };

  /**
   * Animates lines using GSAP fromTo animation
   */
  const animateLines = (element) => {
    const lines = element.querySelectorAll('.line');
    
    if (lines.length === 0) {
      return;
    }
    
    // Animate lines from 145% y position to 0
    gsap.fromTo(lines, {
      y: '145%'
    }, {
      y: '0',
      stagger: 0.15,
      duration: 0.9,
      ease: 'circ.out',
      scrollTrigger: {
        trigger: element,
        start: 'top 75%',
        toggleActions: 'play none none none',
        once: true,
        markers: false
      }
    });
  };

  // Wait for fonts to load before splitting text
  document.fonts.ready.then(() => {
    // Initialize all animated lines
    animatedLines.forEach((animatedLine) => {
      splitTextIntoLines(animatedLine);
      animateLines(animatedLine);
    });
  });
});

