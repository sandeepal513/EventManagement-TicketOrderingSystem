function loadEvents(category = '', sort = '', date_start = '', location = '', maxPrice = '', search = '', date_end = '') {
    console.log('loadEvents called with:', {category, sort, date_start, location, maxPrice, search, date_end});
    var url = '../../../app/controllers/event_list_controller.php';
    var params = [];

    if (category) params.push('category=' + encodeURIComponent(category));
    if (sort) params.push('sort=' + encodeURIComponent(sort));
    if (date_start) params.push('date_start=' + encodeURIComponent(date_start));
    if (date_end) params.push('date_end=' + encodeURIComponent(date_end));
    if (location) params.push('location=' + encodeURIComponent(location));
    if (maxPrice) params.push('maxPrice=' + encodeURIComponent(maxPrice));
    if (search) params.push('search=' + encodeURIComponent(search));

    if (params.length > 0) {
        url += '?' + params.join('&');
    }

    console.log('Fetching URL:', url);
    fetch(url)
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(html => {
        console.log('Received HTML length:', html.length);
        document.querySelector('.events').innerHTML = html;
    })
    .catch(error => console.error('Error fetching events:', error));

    }



// Load all events on page load
document.addEventListener('DOMContentLoaded', function() {
    loadEvents(); // load all
});

// Category filter
document.getElementById('category-filter').addEventListener('change', function() {
    loadEvents(this.value);
});

// Sort buttons
document.getElementById('a-z').addEventListener('click', function() {
    loadEvents('', 'a-z');
});
document.getElementById('date').addEventListener('click', function() {
    loadEvents('', 'date');
});
document.getElementById('pop').addEventListener('click', function() {
    loadEvents('', 'pop');
});

// Price slider functionality (commented out as elements don't exist)
// const priceSlider = document.getElementById('price-slider');
// const maxPriceInput = document.getElementById('max-price');
// const maxPriceLabel = document.getElementById('max-price-label');

// if (priceSlider) {
//     priceSlider.addEventListener('input', function() {
//         maxPriceInput.value = this.value;
//         maxPriceLabel.textContent = this.value;
//     });
// }

// Date-only search function (commented out as element doesn't exist)
// document.getElementById('date-input').addEventListener('change', function() {
//     const selectedDate = this.value;
//     if (selectedDate) {
//         loadEvents('', '', selectedDate); // Only date filter
//     }
// });

// **Changed/New Area: Location-only filter**
document.getElementById('location-input').addEventListener('change', function() {
    const location = this.value.trim();
    if (location) {
        loadEvents('', '', '', location); // Only location filter
    }
});

// Unified search button (works separately and combined)
    document.getElementById('search-button').addEventListener('click', function() {
        const selectedDate = document.getElementById('date-start-input').value;
        const endDate = document.getElementById('date-end-input').value;
        const location = document.getElementById('location-input').value;
        const category = document.getElementById('category-filter').value;
        // const maxPrice = document.getElementById('max-price').value; // commented out as element doesn't exist

        loadEvents(category, '', selectedDate, location, '', '', endDate);
    });



// main search bar
 document.getElementById('main-search').addEventListener('click', function() {
        const query = document.getElementById('search-input').value.trim();
        if (query) {
            loadEvents('', '', '', '', '', query);
        } else {
            alert("Please enter an event name.");
        }
    });