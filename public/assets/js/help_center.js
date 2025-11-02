// FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                const wasActive = item.classList.contains('active');
                
                // Close all other items
                document.querySelectorAll('.faq-item').forEach(otherItem => {
                    otherItem.classList.remove('active');
                });
                
                // Toggle current item
                if (!wasActive) {
                    item.classList.add('active');
                }
            });
        });

        // Search functionality with debounce
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                const searchTerm = e.target.value.toLowerCase().trim();
                const faqItems = document.querySelectorAll('.faq-item');
                const categories = document.querySelectorAll('.faq-category');
                
                // If search is empty, show all items
                if (searchTerm === '') {
                    faqItems.forEach(item => {
                        item.style.display = '';
                        item.classList.remove('active');
                    });
                    categories.forEach(category => {
                        category.style.display = '';
                    });
                    return;
                }
                
                // Filter items based on search
                let hasResults = false;
                
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = '';
                        item.classList.add('active');
                        hasResults = true;
                    } else {
                        item.style.display = 'none';
                        item.classList.remove('active');
                    }
                });
                
                // Show/hide categories based on visible items
                categories.forEach(category => {
                    const visibleItems = Array.from(category.querySelectorAll('.faq-item')).filter(
                        item => item.style.display !== 'none'
                    );
                    category.style.display = visibleItems.length > 0 ? '' : 'none';
                });
                
                // Show no results message if needed
                if (!hasResults) {
                    const noResults = document.getElementById('noResults');
                    if (!noResults) {
                        const message = document.createElement('div');
                        message.id = 'noResults';
                        message.style.textAlign = 'center';
                        message.style.padding = '40px';
                        message.style.color = '#666';
                        message.innerHTML = '<i class="fas fa-search" style="font-size: 48px; margin-bottom: 20px; display: block;"></i><h3>No results found</h3><p>Try different keywords or browse categories above</p>';
                        document.querySelector('.faq-section').appendChild(message);
                    }
                } else {
                    const noResults = document.getElementById('noResults');
                    if (noResults) {
                        noResults.remove();
                    }
                }
            }, 300);
        });

        // Scroll to category
        function scrollToCategory(categoryId) {
            const category = document.getElementById(categoryId);
            if (category) {
                category.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Smooth scroll for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });