// Handle category filter button clicks
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        button.classList.add('active');

        // Placeholder for filtering logic
        const category = button.getAttribute('data-category');
        console.log(`Filtering events by: ${category}`);
        // Add logic to filter events based on category
    });
});

// Smooth scroll for navigation links
document.querySelectorAll('.nav-links a, .footer-links a').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId && targetId.startsWith('#')) {
            document.querySelector(targetId).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Simple animation for event cards on scroll
const eventCards = document.querySelectorAll('.event-card');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = 1;
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.2 });

eventCards.forEach(card => {
    card.style.opacity = 0;
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s, transform 0.5s';
    observer.observe(card);
});