/**
 * Vehicles Flip Animation with GSAP
 * 
 * @package TransfertMarrakech
 */

import { gsap } from 'gsap';
import { Flip } from 'gsap/Flip';

gsap.registerPlugin(Flip);

document.addEventListener('DOMContentLoaded', () => {
  const modal = document.querySelector('.modal');
  const modalContent = modal?.querySelector('.content');
  const modalOverlay = modal?.querySelector('.overlay');
  const modalTitle = modal?.querySelector('.modal__title');
  const boxes = gsap.utils.toArray('.boxes-container .box');
  const boxesContent = gsap.utils.toArray('.box-content');
  const body = document.body;
  let boxIndex;
  let currentBox = null;

  if (!modal || !modalContent || !modalOverlay || !modalTitle || boxes.length === 0) {
    return;
  }

  /**
   * Closes the modal and returns box to original position
   */
  const closeModal = () => {
    if (boxIndex === undefined || !currentBox) {
      return;
    }

    const state = Flip.getState(currentBox);
    boxes[boxIndex].appendChild(currentBox);
    boxIndex = undefined;
    currentBox = null;
    modalTitle.textContent = '';
    
    // Restore body scroll
    body.style.overflow = '';
    
    gsap.to([modal, modalOverlay, modalTitle], {
      autoAlpha: 0,
      ease: 'power1.inOut',
      duration: 0.35
    });
    
    Flip.from(state, {
      duration: 0.7,
      ease: 'power1.inOut',
      absolute: true,
      onComplete: () => gsap.set(currentBox, { zIndex: 'auto' })
    });
    
    gsap.set(currentBox, { zIndex: 1002 });
  };

  /**
   * Opens the modal with the clicked box
   */
  const openModal = (box, index) => {
    // Prevent body scroll
    body.style.overflow = 'hidden';
    
    const title = box.getAttribute('data-title') || '';
    modalTitle.textContent = title;
    
    const state = Flip.getState(box);
    modalContent.appendChild(box);
    boxIndex = index;
    currentBox = box;
    
    gsap.set(modal, { autoAlpha: 1 });
    gsap.set(modalTitle, { autoAlpha: 1 });
    Flip.from(state, {
      duration: 0.7,
      ease: 'power1.inOut'
    });
    gsap.to(modalOverlay, { autoAlpha: 0.65, duration: 0.35 });
  };

  // Handle box clicks
  boxesContent.forEach((box, i) => {
    box.addEventListener('click', (e) => {
      e.stopPropagation();
      
      if (boxIndex !== undefined) {
        closeModal();
      } else {
        openModal(box, i);
      }
    });
  });

  // Close modal when clicking on overlay (outside content)
  modalOverlay.addEventListener('click', closeModal);

  // Close modal when clicking on content
  modalContent.addEventListener('click', (e) => {
    // Only close if clicking directly on content, not on the box
    if (e.target === modalContent) {
      closeModal();
    }
  });

  // Close modal on ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && boxIndex !== undefined) {
      closeModal();
    }
  });
});

