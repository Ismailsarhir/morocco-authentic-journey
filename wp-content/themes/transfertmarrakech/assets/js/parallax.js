/**
 * Image Parallax Effect with GSAP ScrollTrigger
 * Apply the .parallax class to any container with an image/video to enable parallax effect
 * Use data attributes to customize the animation (data-y, data-x, data-scale, etc.)
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Helper to parse data attribute with default value
 * @param {HTMLElement} element - The element
 * @param {string} attr - Attribute name (without 'data-' prefix)
 * @param {number|null} defaultVal - Default value
 * @returns {number|null}
 */
const getDataNumber = (element, attr, defaultVal = null) => {
  const value = element.dataset[attr];
  if (value === undefined) return defaultVal;
  const parsed = parseFloat(value);
  return isNaN(parsed) ? defaultVal : parsed;
};

/**
 * Gets configuration from element data attributes
 * @param {HTMLElement} element - The element to get config from
 * @returns {Object} Configuration object
 */
function getConfig(element) {
  const dataset = element.dataset;
  
  return {
    ease: dataset.ease || 'power1.out',
    duration: dataset.duration ? parseFloat(dataset.duration) : 1,
    disable: dataset.disable === 'true',
    trigger: dataset.trigger || element,
    endTrigger: dataset.endTrigger || element,
    start: dataset.start || 'top bottom',
    end: dataset.end || 'bottom top',
    scrub: getDataNumber(element, 'scrub', 1.5),
    scroller: dataset.scroller || null,
    y: getDataNumber(element, 'y', 100),
    x: getDataNumber(element, 'x', null),
    scale: getDataNumber(element, 'scale', null),
    hoverZoom: getDataNumber(element, 'hoverZoom', null),
  };
}

/**
 * Applies cover styles to media element
 * @param {HTMLElement} mediaElement - The media element to style
 */
function applyCoverStyles(mediaElement) {
  const coverStyles = {
    width: '100%',
    height: '100%',
    objectFit: 'cover',
    objectPosition: 'center center',
  };

  if (mediaElement.tagName === 'PICTURE') {
    const img = mediaElement.querySelector('img');
    if (img) Object.assign(img.style, coverStyles);
  } else {
    Object.assign(mediaElement.style, coverStyles);
  }
}

/**
 * Initializes parallax effect on an element
 * @param {HTMLElement} element - The container element
 */
function initParallax(element) {
  const config = getConfig(element);
  
  // Find the media element - optimized selector order (most common first)
  const mediaElement = element.querySelector('img') || 
                      element.querySelector('picture') || 
                      element.querySelector('video') || 
                      element.querySelector('iframe, .iframe');
  
  if (!mediaElement) {
    console.warn('Parallax: No media element found in', element);
    return;
  }

  // Set up container styles for cover effect
  Object.assign(element.style, {
    position: 'relative',
    overflow: 'hidden',
    width: '100%',
    height: '100%',
  });

  // Apply cover styles to media element
  applyCoverStyles(mediaElement);

  // Skip animation if disabled and no transform values
  if (config.disable && config.y === null && config.x === null && config.scale === null) {
    return;
  }

  // Build animation properties
  const animationProps = {
    ease: config.ease,
    duration: config.duration,
  };

  // Set up ScrollTrigger
  if (!config.disable) {
    const scrollTrigger = {
      trigger: config.trigger || element,
      endTrigger: config.endTrigger || element,
      start: config.start,
      end: config.end,
      scrub: config.scrub,
      anticipatePin: 1,
    };
    
    if (config.scroller) {
      scrollTrigger.scroller = config.scroller;
    }
    
    animationProps.scrollTrigger = scrollTrigger;
  }

  // Handle Y axis parallax
  if (config.y !== null) {
    const absY = Math.abs(config.y);
    const ySetProps = { height: `calc(100% + ${absY}px)` };
    if (config.y < 0) ySetProps.top = config.y;
    gsap.set(mediaElement, ySetProps);
    animationProps.y = -config.y;
  }

  // Handle X axis parallax
  if (config.x !== null) {
    const absX = Math.abs(config.x);
    const xSetProps = { width: `calc(100% + ${absX}px)` };
    if (config.x > 0) xSetProps.left = -config.x;
    gsap.set(mediaElement, xSetProps);
    animationProps.x = config.x;
  }

  // Handle scale
  if (config.scale !== null) {
    if (config.scale >= 1) {
      animationProps.scale = config.scale;
    } else {
      gsap.set(mediaElement, { scale: 1 / config.scale });
      animationProps.scale = 1;
    }
  }

  // Create and store animation
  const tween = gsap.to(mediaElement, animationProps);
  element.tween = tween;
  
  if (config.disable) {
    tween.pause();
  }

  // Hover zoom effect
  if (config.hoverZoom !== null) {
    const wrapper = document.createElement('div');
    Object.assign(wrapper.style, {
      height: '100%',
      width: '100%',
    });
    
    // Insert wrapper before mediaElement, then move mediaElement into wrapper
    // This preserves the DOM structure correctly
    if (mediaElement.parentNode) {
      mediaElement.parentNode.insertBefore(wrapper, mediaElement);
      wrapper.appendChild(mediaElement);
    } else {
      // Fallback if mediaElement has no parent (shouldn't happen, but safety check)
      element.appendChild(wrapper);
      wrapper.appendChild(mediaElement);
    }

    const zoomConfig = {
      duration: 0.5,
      ease: 'power2.out',
    };

    let initialScale = 1;
    let targetScale = config.hoverZoom;
    
    if (config.hoverZoom < 1) {
      initialScale = 1 / config.hoverZoom;
      targetScale = 1;
      gsap.set(wrapper, { scale: initialScale });
    }

    const handleEnter = () => gsap.to(wrapper, { ...zoomConfig, scale: targetScale });
    const handleLeave = () => gsap.to(wrapper, { ...zoomConfig, scale: initialScale });

    mediaElement.addEventListener('pointerenter', handleEnter);
    mediaElement.addEventListener('pointerleave', handleLeave);
    
    // Store event handlers for potential cleanup
    wrapper._parallaxHandlers = { enter: handleEnter, leave: handleLeave, element: mediaElement };
  }
}

/**
 * Initialize parallax on all elements with .parallax class
 */
function initParallaxElements() {
  const parallaxElements = document.querySelectorAll('.parallax');
  
  if (!parallaxElements.length) return;

  parallaxElements.forEach(initParallax);
  ScrollTrigger.refresh();
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initParallaxElements);
} else {
  initParallaxElements();
}

/**
 * Cleanup function (useful for SPA or dynamic content)
 */
export function cleanupParallax() {
  // Clean up ScrollTriggers
  ScrollTrigger.getAll().forEach(trigger => {
    const vars = trigger.vars;
    if (!vars?.trigger) return;

    const element = typeof vars.trigger === 'string' 
      ? document.querySelector(vars.trigger)
      : vars.trigger;
    
    if (element?.classList.contains('parallax')) {
      // Kill the tween if it exists
      if (element.tween) {
        element.tween.kill();
        delete element.tween;
      }
      
      // Clean up hover zoom event listeners if they exist
      const wrapper = element.querySelector('div');
      if (wrapper && wrapper._parallaxHandlers) {
        const handlers = wrapper._parallaxHandlers;
        if (handlers.element && handlers.enter && handlers.leave) {
          handlers.element.removeEventListener('pointerenter', handlers.enter);
          handlers.element.removeEventListener('pointerleave', handlers.leave);
        }
        delete wrapper._parallaxHandlers;
      }
      
      trigger.kill();
    }
  });
}

