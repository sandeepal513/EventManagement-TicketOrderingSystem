document.addEventListener('DOMContentLoaded', () => {

    // --- 1. CONSTANTS AND DOM REFERENCES ---
    const DARK_MODE_CLASS = 'dark-mode';
    const THEME_KEY = 'theme';
    const ACTIVE_VIEW_KEY = 'activeView';

    // Modals and Triggers
    const profiledtl = document.getElementById('profiledetails');
    const profilepic = document.getElementById('profilepic');
    const imgModal = document.getElementById('editProfileimageModal'); 
    const imgCloseBtn = document.getElementById('closeImageModalBtn');
    const modal = document.getElementById('editProfileModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const modalContent = document.querySelector('.edit-profile-content');
    const imageUploadForm = document.getElementById('imageUploadForm');

    // Navigation and Content
    const contentViews = document.querySelectorAll('.content-view');
    // Selects all links in the nav menu
    const navLinks = document.querySelectorAll('.nav-down a'); 
    const themeToggleBtn = document.getElementById('themeToggle');

    // Image Upload Elements
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('imageFileInput');
    const selectBtn = document.getElementById('selectImageBtn');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageBtn = document.getElementById('removeImageBtn');


    // --- 2. CORE FUNCTIONS ---

    // --- Theme & Persistence Functions ---
    
    /**
     * Applies the theme class to the HTML element and syncs the toggle button's position.
     * @param {string} theme - 'dark' or 'light'.
     */
    function applyTheme(theme) {
        const html = document.documentElement;
        const isDark = (theme === 'dark');

        html.classList.toggle(DARK_MODE_CLASS, isDark);

        // Sync toggle switch position
        if (themeToggleBtn) {
            themeToggleBtn.classList.toggle('on', isDark);
        }
    }

    /**
     * Toggles between dark and light themes and saves to localStorage.
     */
    function toggleTheme() {
        const html = document.documentElement;
        const isCurrentlyDark = html.classList.contains(DARK_MODE_CLASS);
        const newTheme = isCurrentlyDark ? 'light' : 'dark';

        localStorage.setItem(THEME_KEY, newTheme);
        applyTheme(newTheme);
    }

    /**
     * Loads saved theme from localStorage on startup.
     */
    function loadTheme() {
        const savedTheme = localStorage.getItem(THEME_KEY);
        const initialTheme = (savedTheme === 'dark') ? 'dark' : 'light';
        applyTheme(initialTheme);
    }


    // --- View Switching Functions ---

    function saveActiveView(viewId) {
        localStorage.setItem(ACTIVE_VIEW_KEY, viewId);
    }

    function loadActiveView() {
        const savedViewId = localStorage.getItem(ACTIVE_VIEW_KEY) || 'profileDetailsView';
        const targetLink = Array.from(navLinks).find(link => link.getAttribute('data-view') === savedViewId);
        
        // If a visible link is found, simulate a click to activate the view.
        if (targetLink) {
            targetLink.click(); 
        } else {
            // Ensure the default view is active if the saved one is hidden (due to role restrictions)
            switchContentView('profileDetailsView', document.getElementById('profileDetailsLink'));
        }
    }
    
    const switchContentView = (targetViewId, clickedLink) => {
        contentViews.forEach(view => view.classList.remove('active'));
        navLinks.forEach(link => link.classList.remove('active-nav'));

        const targetView = document.getElementById(targetViewId);
        if (targetView && clickedLink) {
            targetView.classList.add('active');
            clickedLink.classList.add('active-nav');
            saveActiveView(targetViewId);
        }
    };

    // --- Modal Functions ---

    const openDetailsModal = () => {
        modal.classList.add('is-visible');
        // Clear the flag when opening manually
        sessionStorage.removeItem('modalShouldReopen');
    };

    const closeDetailsModal = () => {
        modal.classList.remove('is-visible');
    };

    const openImageModal = () => {
        imgModal.classList.add('is-visible');
        // Clear the flag when opening manually
        sessionStorage.removeItem('imageModalShouldReopen');
    };

    const closeImageModal = () => {
        imgModal.classList.remove('is-visible');
    };
    
    // --- Check if modals should reopen after error ---
    const checkAndReopenModal = () => {
        // Check if there's an error or success message displayed
        const errorAlert = document.querySelector('.alert-error');
        const successAlert = document.querySelector('.alert-success');
        
        // Get the flags
        const shouldReopenDetails = sessionStorage.getItem('modalShouldReopen');
        const shouldReopenImage = sessionStorage.getItem('imageModalShouldReopen');
        
        // Only reopen if there's an ERROR (not success)
        if (errorAlert && shouldReopenDetails === 'true') {
            openDetailsModal();
        }
        
        if (errorAlert && shouldReopenImage === 'true') {
            openImageModal();
        }
        
        // Always clear the flags after checking (whether we reopened or not)
        sessionStorage.removeItem('modalShouldReopen');
        sessionStorage.removeItem('imageModalShouldReopen');
    };

    // --- Notification Toggle ---
    function toggleNotification(toggleElement) {
        toggleElement.classList.toggle('on');
        const isNowActive = toggleElement.classList.contains('on');
        const toggleType = toggleElement.getAttribute('data-toggle-type');
        
        if (!toggleType) return; // Skip if no toggle type (like theme toggle)
        
        // Send update to server
        fetch('/EMS_version_01/EventManagement-TicketOrderingSystem/app/controllers/notification_toggle_update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                toggle_type: toggleType,
                value: isNowActive
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`${toggleType} updated: ${isNowActive ? 'ON' : 'OFF'}`);
            } else {
                // Revert toggle on failure
                toggleElement.classList.toggle('on');
                console.error('Failed to update notification preference:', data.message);
            }
        })
        .catch(error => {
            // Revert toggle on error
            toggleElement.classList.toggle('on');
            console.error('Error updating notification preference:', error);
        });
    }

    // --- Image Upload Logic ---
    
    /**
     * Handles file processing, updating the image preview.
     * @param {File} file - The image file selected or dropped.
     */
    const handleFile = (file) => {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            console.log(`Selected file: ${file.name}`);
        } else {
            console.error("Please select a valid image file (JPG or PNG).");
        }
    };

    // --- Delete Profile Image with Confirmation ---
    const deleteProfileImage = (event) => {
        event.preventDefault(); // Prevent the default link action
        event.stopPropagation(); // Stop the event from bubbling up
        
        // Create and show custom confirmation modal
        showDeleteConfirmationModal();
    };

    // --- Show Advanced Delete Confirmation Modal ---
    const showDeleteConfirmationModal = () => {
        // Create modal HTML
        const confirmModal = document.createElement('div');
        confirmModal.className = 'delete-confirm-modal';
        confirmModal.innerHTML = `
            <div class="delete-confirm-content">
                <div class="delete-confirm-icon">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h2>Delete Profile Picture?</h2>
                <p>Are you sure you want to delete your profile picture? This action cannot be undone and your picture will be replaced with the default image.</p>
                <div class="delete-confirm-buttons">
                    <button class="cancel-delete-btn" id="cancelDeleteBtn">Cancel</button>
                    <button class="confirm-delete-btn" id="confirmDeleteBtn">Yes, Delete</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(confirmModal);
        
        // Trigger animation
        setTimeout(() => confirmModal.classList.add('is-visible'), 10);
        
        // Handle Cancel button
        document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
            closeDeleteConfirmationModal(confirmModal);
        });
        
        // Handle Confirm button
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            closeDeleteConfirmationModal(confirmModal);
            // Proceed with deletion
            window.location.href = '/EMS_version_01/EventManagement-TicketOrderingSystem/app/controllers/profile_pic_delete.php?confirm=delete';
        });
        
        // Handle click outside modal to close
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                closeDeleteConfirmationModal(confirmModal);
            }
        });
    };

    // --- Close Delete Confirmation Modal ---
    const closeDeleteConfirmationModal = (modal) => {
        modal.classList.remove('is-visible');
        setTimeout(() => modal.remove(), 300); // Remove after animation
    };

    // --- Remove Image Function (keeping for backward compatibility) ---
    const removeProfileImage = () => {
        if (confirm('Are you sure you want to remove your profile photo and revert to the default?')) {
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = '/EMS_version_01/EventManagement-TicketOrderingSystem/app/controllers/profile_pic_update.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'remove';
            
            tempForm.appendChild(actionInput);
            document.body.appendChild(tempForm);
            
            tempForm.submit();
        }
    };


    // --- 3. EVENT LISTENERS SETUP ---

    function setupEventListeners() {
        
        // --- Initialization ---
        loadTheme();

        // --- Theme Toggle ---
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', toggleTheme);
        }

        // --- Notification Toggles ---
        document.querySelectorAll('.not-b').forEach(toggle => {
            if (toggle.id !== THEME_KEY + 'Toggle') {
                toggle.addEventListener('click', (e) => {
                    toggleNotification(e.currentTarget);
                });
            }
        });

        // --- Profile Details Modal Listeners ---
        if(profiledtl) profiledtl.addEventListener('click', openDetailsModal);
        if(closeModalBtn) closeModalBtn.addEventListener('click', closeDetailsModal);
        
        // --- Set flag before form submission ---
        if (modalContent) {
            const detailsForm = modalContent.querySelector('form');
            if (detailsForm) {
                detailsForm.addEventListener('submit', () => {
                    // Set a flag in sessionStorage to know we should reopen the modal if there's an error
                    sessionStorage.setItem('modalShouldReopen', 'true');
                    // Don't close the modal here - let it close naturally on redirect
                });
            }
        }


        // --- Image Upload Modal Listeners ---
        if(profilepic) profilepic.addEventListener('click', openImageModal);
        if(imgCloseBtn) imgCloseBtn.addEventListener('click', closeImageModal);
        
        // --- Set flag before image form submission ---
        if(imageUploadForm) {
            imageUploadForm.addEventListener('submit', (e) => {
                // Set a flag in sessionStorage to know we should reopen the modal if there's an error
                sessionStorage.setItem('imageModalShouldReopen', 'true');
                console.log('Profile Image Form Submission initiated.');
            });
        }

        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', removeProfileImage);
        }
        
        // --- Delete Profile Image Confirmation ---
        if(selectBtn) {
            selectBtn.addEventListener('click', () => fileInput.click());
        }

        if(fileInput) {
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFile(e.target.files[0]);
                }
            });
        }

        // Drag and Drop Listeners
        if(dropZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('highlight'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('highlight'), false);
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const file = dt.files[0];
                if (file) {
                    fileInput.files = dt.files; 
                    handleFile(file);
                }
            }, false);
        }
        
        // --- Navigation View Switching ---
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Get the target view ID
                const targetViewId = e.target.getAttribute('data-view');
                
                // ONLY prevent default if the link has a data-view attribute
                if (targetViewId) {
                    e.preventDefault();
                    switchContentView(targetViewId, e.target);
                }
            });
        });

        // --- Final Load ---
        loadActiveView();
        
        // --- Check if modal should reopen after page load ---
        checkAndReopenModal();
    }
    
    // Initialize everything
    setupEventListeners();


    // Floating alert auto-close + manual close
    document.querySelectorAll('.alert').forEach(alert => {
        // Auto-hide after 4 seconds
        setTimeout(() => {
            alert.classList.add('hide');
        }, 4000);

        alert.addEventListener('click', (e) => {
            if (e.target === alert || e.target.textContent === '×') {
                alert.classList.add('hide');
            }
        });
    });


    // Show/Hide Password toggle logic
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            btn.textContent = isHidden ? '🙈' : '👁️'; // swap icon
            btn.classList.toggle('active', isHidden);
        });
    });

});