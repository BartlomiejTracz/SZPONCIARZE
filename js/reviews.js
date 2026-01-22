// Add Review
let currentRating = 0;

// Obsługa wybierania gwiazdek
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-star');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = this.getAttribute('data-rating');

            // Wizualne zaznaczenie gwiazdek
            stars.forEach(s => {
                if (parseInt(s.getAttribute('data-rating')) <= currentRating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });

            const msg = document.getElementById('ratingMessage');
            if(msg) msg.innerText = "Twoja ocena: " + currentRating + "/5";
        });
    });
});

// Funkcja wysyłająca recenzję
function addReview() {
    const textarea = document.getElementById('reviewTextarea');
    const movieId = new URLSearchParams(window.location.search).get('id');

    if (currentRating === 0) {
        alert("Proszę najpierw ocenić film gwiazdkami!");
        return;
    }

    if (textarea.value.trim().length < 3) {
        alert("Treść recenzji jest za krótka!");
        return;
    }

    const formData = new FormData();
    formData.append('id_filmu', movieId);
    formData.append('ocena', currentRating);
    formData.append('tresc', textarea.value);

    fetch('add-review.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload(); // Odświeżamy, aby pokazać nową recenzję
            } else {
                alert("Błąd: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Wystąpił błąd podczas wysyłania.");
        });
}

// Delete Review
function deleteReview(idRecenzji, idFilmu) {
    if (!confirm('Czy na pewno chcesz trwale usunąć tę recenzję?')) {
        return;
    }

    const formData = new FormData();
    formData.append('id_recenzji', idRecenzji);
    formData.append('id_filmu', idFilmu);

    fetch('delete-review.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Animacja zniknięcia elementu z listy przed odświeżeniem
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Wystąpił błąd podczas usuwania.');
        });
}

// Create Review
function createReviewCard(author, date, content, helpful, rating) {
    const initials = author.split(' ').map(n => n[0]).join('').toUpperCase();

    return `
        <div class="review-card" style="animation: slideIn 0.3s ease;">
            <div class="review-header">
                <div class="review-author">
                    <div class="author-avatar">${initials}</div>
                    <div>
                        <div class="author-name">${author}</div>
                        <div class="review-date">${date}</div>
                    </div>
                </div>
                <div class="review-rating">
                    <span class="star-icon">★</span>
                    <span>${rating}/5</span>
                </div>
            </div>
            <div class="review-content">
                ${content}
            </div>
            <div class="review-actions">
                <button class="review-action-btn">👍 Pomocne (${helpful})</button>
                <button class="review-action-btn delete-review" onclick="deleteReview(this)" title="Usuń recenzję (tylko administrator)">
                    🗑️ Usuń
                </button>
            </div>
        </div>
    `;
}

// Animacje
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

const ratingStars = document.querySelectorAll('.rating-stars-inline .rating-star');
const ratingMessage = document.getElementById('ratingMessage');
let selectedRating = 0;

if (ratingStars.length > 0) {
    ratingStars.forEach((star, index) => {
        star.addEventListener('mouseenter', () => {
            highlightStars(index + 1);
        });

        star.addEventListener('click', () => {
            selectedRating = index + 1;
            highlightStars(selectedRating);

            // TODO: Zapisz ocenę do backend
            console.log('Ocena:', selectedRating);
        });
    });

    const ratingContainer = document.querySelector('.rating-stars-inline');
    if (ratingContainer) {
        ratingContainer.addEventListener('mouseleave', () => {
            highlightStars(selectedRating);
            if (selectedRating === 0 && ratingMessage) {
                ratingMessage.textContent = '';
            }
        });
    }
}

// Funkcja do podświetlania gwiazdek
function highlightStars(count) {
    ratingStars.forEach((star, index) => {
        if (index < count) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.getAttribute('data-rating');
        const allStars = document.querySelectorAll('.rating-star');

        allStars.forEach(s => {
            if (s.getAttribute('data-rating') <= rating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });

        document.getElementById('ratingMessage').innerText = `Wybrano ocenę: ${rating}/5`;
    });
});