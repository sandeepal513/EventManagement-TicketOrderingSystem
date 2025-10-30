// ===== checkout.js =====
// Form validation, payment processing, and cart management
// UPDATED FOR NEW TICKET_TYPES SCHEMA

let selectedTickets = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeCheckout();
    loadCartFromSession();
});

// ===== CART MANAGEMENT FUNCTIONS =====

function addToCart(ticketTypeId, ticketType, price, quantity) {
    const numQuantity = parseInt(quantity);
    
    if (numQuantity <= 0) {
        alert('Please enter a valid quantity');
        return;
    }
    
    const existing = selectedTickets.find(t => t.id === ticketTypeId);
    
    if (existing) {
        existing.quantity += numQuantity;
    } else {
        selectedTickets.push({
            id: ticketTypeId,
            type: ticketType,  // GA or VIP
            price: parseFloat(price),
            quantity: numQuantity
        });
    }
    
    // Save to sessionStorage
    sessionStorage.setItem('selectedTickets', JSON.stringify(selectedTickets));
    updateCartDisplay();
    alert(numQuantity + ' ticket(s) added to cart!');
}

function removeFromCart(ticketTypeId) {
    selectedTickets = selectedTickets.filter(t => t.id !== ticketTypeId);
    sessionStorage.setItem('selectedTickets', JSON.stringify(selectedTickets));
    updateCartDisplay();
}

function updateCartDisplay() {
    const summaryItems = document.getElementById('summaryItems');
    
    if (!summaryItems) return;
    
    summaryItems.innerHTML = '';
    
    if (selectedTickets.length === 0) {
        summaryItems.innerHTML = '<p style="color: #999; text-align: center;">No items in cart</p>';
        updateTotals([]);
        return;
    }
    
    selectedTickets.forEach(ticket => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'summary-item';
        itemDiv.innerHTML = `
            <div style="flex: 1;">
                <span>${ticket.quantity}x ${ticket.type} - $${parseFloat(ticket.price).toFixed(2)}</span><br/>
                <span style="color: #666; font-size: 12px;">Subtotal: $${(ticket.quantity * ticket.price).toFixed(2)}</span>
            </div>
            <button type="button" onclick="removeFromCart(${ticket.id})" style="background: #ff4444; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Remove</button>
        `;
        summaryItems.appendChild(itemDiv);
    });
    
    updateTotals(selectedTickets);
}

function loadCartFromSession() {
    const cartData = sessionStorage.getItem('selectedTickets');
    
    if (cartData) {
        selectedTickets = JSON.parse(cartData);
        updateCartDisplay();
    }
}

function updateTotals(tickets) {
    const TAX_RATE = 0.10;
    const PROCESSING_FEE = 10.00;
    
    let subtotal = 0;
    tickets.forEach(ticket => {
        subtotal += ticket.quantity * ticket.price;
    });
    
    const tax = subtotal * TAX_RATE;
    const total = subtotal + tax + PROCESSING_FEE;
    
    const subtotalEl = document.getElementById('subtotal');
    const taxEl = document.getElementById('tax');
    const feeEl = document.getElementById('processingFee');
    const totalEl = document.getElementById('totalAmount');
    const submitBtn = document.getElementById('submitBtn');
    
    if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
    if (taxEl) taxEl.textContent = `$${tax.toFixed(2)}`;
    if (feeEl) feeEl.textContent = `$${PROCESSING_FEE.toFixed(2)}`;
    if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
    if (submitBtn) submitBtn.textContent = `Complete Purchase - $${total.toFixed(2)}`;
}

// ===== FORM INITIALIZATION & VALIDATION =====

function initializeCheckout() {
    const form = document.getElementById('checkoutForm');
    const cardNumberInput = document.getElementById('cardNumber');
    const expiryInput = document.getElementById('expiry');
    const cvvInput = document.getElementById('cvv');
    const paymentBtns = document.querySelectorAll('.payment-btn');

    // Form submission handler
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            validateAndSubmit();
        });
    }

    // Format card number (add spaces every 4 digits)
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
            
            // Only allow numbers
            e.target.value = e.target.value.replace(/[^0-9\s]/g, '');
        });
    }

    // Format expiry date (MM/YY)
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }

    // Format CVV (only numbers, max 4 digits)
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });
    }

    // Payment method selection
    paymentBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            paymentBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

function validateAndSubmit() {
    // Check if cart is empty
    if (selectedTickets.length === 0) {
        alert('Your cart is empty! Please select tickets.');
        return false;
    }

    // Get form values
    const fullName = document.getElementById('fullName')?.value?.trim();
    const email = document.getElementById('email')?.value?.trim();
    const phone = document.getElementById('phone')?.value?.trim();
    const address = document.getElementById('address')?.value?.trim();
    const city = document.getElementById('city')?.value?.trim();
    const zip = document.getElementById('zip')?.value?.trim();
    const cardName = document.getElementById('cardName')?.value?.trim();
    const cardNumber = document.getElementById('cardNumber')?.value?.replace(/\s/g, '');
    const expiry = document.getElementById('expiry')?.value?.trim();
    const cvv = document.getElementById('cvv')?.value?.trim();

    // Validation rules
    const errors = [];

    // Required fields
    if (!fullName) errors.push('Full name is required');
    if (!email) errors.push('Email is required');
    if (!address) errors.push('Street address is required');
    if (!city) errors.push('City is required');
    if (!zip) errors.push('ZIP code is required');
    if (!cardName) errors.push('Cardholder name is required');
    if (!cardNumber) errors.push('Card number is required');
    if (!expiry) errors.push('Expiry date is required');
    if (!cvv) errors.push('CVV is required');

    // Email validation
    if (email && !isValidEmail(email)) {
        errors.push('Please enter a valid email address');
    }

    // Expiry date validation
    if (expiry && !isValidExpiry(expiry)) {
        errors.push('Please enter a valid expiry date (MM/YY)');
    }

    // CVV validation
    if (cvv && (cvv.length < 3 || cvv.length > 4)) {
        errors.push('CVV must be 3-4 digits');
    }

    // Show errors if any
    if (errors.length > 0) {
        alert('Please fix the following errors:\n\n' + errors.join('\n'));
        return false;
    }

    // Add cart data to form before submission
    const cartInput = document.getElementById('cartData');
    if (cartInput) {
        cartInput.value = JSON.stringify(selectedTickets);
    }

    // If validation passes, submit form
    showLoadingState();
    document.getElementById('checkoutForm').submit();
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidCardNumber(cardNumber) {
    // Luhn algorithm validation
    let sum = 0;
    let isEven = false;

    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i), 10);

        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }

        sum += digit;
        isEven = !isEven;
    }

    return sum % 10 === 0 && cardNumber.length >= 13;
}

function isValidExpiry(expiry) {
    const regex = /^\d{2}\/\d{2}$/;
    if (!regex.test(expiry)) return false;

    const [month, year] = expiry.split('/');
    const monthNum = parseInt(month, 10);
    const yearNum = parseInt('20' + year, 10);

    // Check month is valid
    if (monthNum < 1 || monthNum > 12) return false;

    // Check expiry is not in past
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1;

    if (yearNum < currentYear) return false;
    if (yearNum === currentYear && monthNum < currentMonth) return false;

    return true;
}

function showLoadingState() {
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing... Please wait';
        submitBtn.style.opacity = '0.6';
    }
}

function hideLoadingState() {
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        const total = calculateTotal();
        submitBtn.disabled = false;
        submitBtn.textContent = `Complete Purchase - $${total}`;
        submitBtn.style.opacity = '1';
    }
}

function calculateTotal() {
    const TAX_RATE = 0.10;
    const PROCESSING_FEE = 10.00;
    
    let subtotal = 0;
    selectedTickets.forEach(ticket => {
        subtotal += ticket.quantity * ticket.price;
    });
    
    const tax = subtotal * TAX_RATE;
    const total = subtotal + tax + PROCESSING_FEE;
    
    return total.toFixed(2);
}

// Prevent form submission on Enter key in card fields
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && 
        (e.target.id === 'cardNumber' || 
         e.target.id === 'expiry' || 
         e.target.id === 'cvv')) {
        e.preventDefault();
    }
});