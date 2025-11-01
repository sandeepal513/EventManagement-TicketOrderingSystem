// ========================================
// EVENT DETAILS PAGE - JAVASCRIPT
// ========================================

// Page animation completion
setTimeout(() => {
  document.getElementById('page').classList.add('done');
  document.documentElement.classList.add('done');
  document.body.classList.add('done');
  
  // Initialize scroll animations after page loads
  initScrollAnimations();
}, 900);

// ========================================
// FADE IN ON SCROLL FUNCTIONALITY
// ========================================
function initScrollAnimations() {
  const faders = document.querySelectorAll('.fade-in-section, .fade-in-left, .fade-in-right, .stagger-animation');
  
  const appearOptions = {
    threshold: 0.15,
    rootMargin: "0px 0px -50px 0px"
  };

  const appearOnScroll = new IntersectionObserver(function(entries, appearOnScroll) {
    entries.forEach(entry => {
      if (!entry.isIntersecting) {
        return;
      } else {
        entry.target.classList.add('is-visible');
        appearOnScroll.unobserve(entry.target);
      }
    });
  }, appearOptions);

  faders.forEach(fader => {
    appearOnScroll.observe(fader);
  });
}

// ========================================
// TICKET QUANTITY MANAGEMENT
// ========================================
document.querySelectorAll('.ticket-item').forEach(item => {
  const minusBtn = item.querySelector('.minus');
  const plusBtn = item.querySelector('.plus');
  const input = item.querySelector('.quantity-input');
  const max = parseInt(input.getAttribute('max'));

  // Decrease quantity
  minusBtn.addEventListener('click', () => {
    let val = parseInt(input.value);
    if (val > 0) {
      input.value = val - 1;
      updateTicketSelection(item, input.value > 0);
      updateTotal();
    }
  });

  // Increase quantity
  plusBtn.addEventListener('click', () => {
    let val = parseInt(input.value);
    if (val < max) {
      input.value = val + 1;
      updateTicketSelection(item, true);
      updateTotal();
    }
  });
});

// Update ticket visual selection state
function updateTicketSelection(item, selected) {
  if (selected) {
    item.classList.add('selected');
  } else {
    item.classList.remove('selected');
  }
}

// Update total amount calculation
function updateTotal() {
  let total = 0;
  let hasTickets = false;

  document.querySelectorAll('.quantity-input').forEach(input => {
    const quantity = parseInt(input.value);
    const price = parseFloat(input.getAttribute('data-price'));
    if (quantity > 0) {
      total += quantity * price;
      hasTickets = true;
    }
  });

  const totalSection = document.getElementById('totalSection');
  const totalAmount = document.getElementById('totalAmount');
  const checkoutBtn = document.getElementById('checkoutBtn');

  if (hasTickets) {
    totalSection.style.display = 'flex';
    totalAmount.textContent = '£' + total.toFixed(2);
    checkoutBtn.disabled = false;
  } else {
    totalSection.style.display = 'none';
    checkoutBtn.disabled = true;
  }
}

// ========================================
// CAROUSEL FUNCTIONALITY
// ========================================
const carousel = document.querySelector('.carousel');
const prevBtn = document.querySelector('.carousel-btn.prev');
const nextBtn = document.querySelector('.carousel-btn.next');

if (carousel && prevBtn && nextBtn) {
  nextBtn.addEventListener('click', () => {
    carousel.scrollBy({ left: 270, behavior: 'smooth' });
  });

  prevBtn.addEventListener('click', () => {
    carousel.scrollBy({ left: -270, behavior: 'smooth' });
  });
}

// ========================================
// FORM VALIDATION
// ========================================
document.getElementById('ticketForm')?.addEventListener('submit', function(e) {
  let hasSelection = false;
  document.querySelectorAll('.quantity-input').forEach(input => {
    if (parseInt(input.value) > 0) {
      hasSelection = true;
    }
  });

  if (!hasSelection) {
    e.preventDefault();
    alert('Please select at least one ticket before adding to cart.');
  }
});