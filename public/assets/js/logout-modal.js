// ============================================
// LOGOUT MODAL FUNCTIONALITY
// ============================================

/**
 * Main logout function - called when user clicks logout link
 */
const logoutUser = (event) => {
    event.preventDefault();
    event.stopPropagation();
    showLogoutModal();
};

/**
 * Create and display the logout confirmation modal
 */
const showLogoutModal = () => {
    // Remove any existing logout modal
    const existingModal = document.querySelector('.logout-modal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal structure
    const modal = document.createElement('div');
    modal.className = 'logout-modal';
    modal.innerHTML = `
        <div class="logout-modal-content">
            <div class="logout-modal-inner">
                <div class="logout-icon"></div>
                <div class="logout-modal-header">
                    <h3>Confirm Logout</h3>
                </div>
                <div class="logout-modal-body">
                    <p>Are you sure you want to log out of your account?</p>
                </div>
                <div class="logout-modal-footer">
                    <button type="button" class="logout-cancel-btn">Cancel</button>
                    <a href="${BASE_URL}app/controllers/logout.php" class="logout-confirm-btn">Logout</a>
                </div>
            </div>
        </div>
    `;

    // Append modal to body
    document.body.appendChild(modal);

    // Show modal with animation
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });

    // Attach event listeners
    attachModalEvents(modal);
};

/**
 * Attach event listeners to modal elements
 */
const attachModalEvents = (modal) => {
    const cancelBtn = modal.querySelector('.logout-cancel-btn');
    
    // Cancel button click
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeLogoutModal(modal);
        });
    }

    // Click outside modal to close
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeLogoutModal(modal);
        }
    });

    // Escape key to close
    const escapeHandler = (e) => {
        if (e.key === 'Escape') {
            closeLogoutModal(modal);
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
};

/**
 * Close and remove the logout modal
 */
const closeLogoutModal = (modal) => {
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.remove();
    }, 300);
};