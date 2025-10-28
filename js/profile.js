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
    const navLinks = document.querySelectorAll('.nav-down a');
    const themeToggleBtn = document.getElementById('themeToggle');

    // Image Upload Elements
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('imageFileInput');
    const selectBtn = document.getElementById('selectImageBtn');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageBtn = document.getElementById('removeImageBtn'); // Reference for the new button


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
    };

    const closeDetailsModal = () => {
        modal.classList.remove('is-visible');
    };

    const openImageModal = () => {
        imgModal.classList.add('is-visible');
    };

    const closeImageModal = () => {
        imgModal.classList.remove('is-visible');
    };
    
    // --- Notification Toggle ---

    function toggleNotification(toggleElement) {
        toggleElement.classList.toggle('on');
        const isNowActive = toggleElement.classList.contains('on');
        const toggleId = toggleElement.id;
        console.log(`Toggle ${toggleId} is now: ${isNowActive ? 'ON' : 'OFF'}`);
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

    // --- NEW: Remove Image Function ---
    const removeProfileImage = () => {
        // Use a custom modal instead of alert/confirm in production
        if (confirm('Are you sure you want to remove your profile photo and revert to the default?')) {
            // Create a temporary form to submit the remove action
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = './../php/profile_pic_update.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'remove';
            
            tempForm.appendChild(actionInput);
            document.body.appendChild(tempForm);
            
            // Submit the form to PHP
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
            if (toggle.id !== THEME_KEY + 'Toggle') { // Exclude the theme toggle itself
                toggle.addEventListener('click', (e) => {
                    toggleNotification(e.currentTarget);
                });
            }
        });

        // --- Profile Details Modal Listeners ---
        if(profiledtl) profiledtl.addEventListener('click', openDetailsModal);
        if(closeModalBtn) closeModalBtn.addEventListener('click', closeDetailsModal);
        
        if (modalContent) {
            modalContent.querySelector('form').addEventListener('submit', () => {
                closeDetailsModal(); 
            });
        }


        // --- Image Upload Modal Listeners ---
        if(profilepic) profilepic.addEventListener('click', openImageModal);
        if(imgCloseBtn) imgCloseBtn.addEventListener('click', closeImageModal);
        
        // Image Upload Form Submission: Allow native submission
        if(imageUploadForm) {
            imageUploadForm.addEventListener('submit', (e) => {
                console.log('Profile Image Form Submission initiated.');
            });
        }

        // NEW: Remove Image Button Listener
        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', removeProfileImage);
        }

        // Button to open file selector
        if(selectBtn) {
            selectBtn.addEventListener('click', () => fileInput.click());
        }

        // File input change
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
                    // Set the file directly to the input field
                    fileInput.files = dt.files; 
                    handleFile(file);
                }
            }, false);
        }
        
        // --- Navigation View Switching ---
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetViewId = e.target.getAttribute('data-view');
                if (targetViewId) {
                    switchContentView(targetViewId, e.target);
                }
            });
        });

        // --- Final Load ---
        loadActiveView();
    }
    
    // Initialize everything
    setupEventListeners();


    // Floating alert auto-close + manual close
    document.querySelectorAll('.alert').forEach(alert => {
        // Auto-hide after 4 seconds
        setTimeout(() => {
            alert.classList.add('hide');
        }, 4000);

        // Click on × to close immediately
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
            btn.textContent = isHidden ? '🙈' : '👁'; // swap icon
            btn.classList.toggle('active', isHidden);
        });
    });

});
