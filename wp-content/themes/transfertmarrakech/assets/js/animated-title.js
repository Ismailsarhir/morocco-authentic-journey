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
   * Splits text into lines, words, and characters
   * Structure: .line > .word > .char
   */
  const splitTextIntoChars = (element) => {
    const text = element.textContent.trim();
    
    element.innerHTML = '';
    
    // Split text into lines (by newlines or by detecting natural line breaks)
    // For now, we'll split by actual newlines, but you can add logic to detect line breaks
    const lines = text.split(/\n/).filter(line => line.trim().length > 0);
    
    // If no newlines, treat entire text as one line
    if (lines.length === 0) {
      lines.push(text);
    }
    
    lines.forEach((lineText) => {
      // Create line wrapper
      const lineSpan = document.createElement('div');
      lineSpan.className = 'line';
      lineSpan.style.display = 'block';
      lineSpan.style.textAlign = 'center';
      lineSpan.style.width = '100%';
      
      // Split line into words (by spaces)
      const words = lineText.trim().split(/\s+/).filter(word => word.length > 0);
      
      words.forEach((word, wordIndex) => {
        // Create word wrapper
        const wordSpan = document.createElement('span');
        wordSpan.className = 'word';
        wordSpan.style.display = 'inline-block';
        
        // Split word into characters
        word.split('').forEach((char) => {
          const charSpan = document.createElement('span');
          charSpan.className = 'char';
          charSpan.style.display = 'inline-block';
          charSpan.textContent = char;
          wordSpan.appendChild(charSpan);
        });
        
        lineSpan.appendChild(wordSpan);
        
        // Add space between words (except after last word)
        if (wordIndex < words.length - 1) {
          lineSpan.appendChild(document.createTextNode(' '));
        }
      });
      
      element.appendChild(lineSpan);
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

