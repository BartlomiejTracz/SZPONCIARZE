// Add Review
function addReview() {
    const textarea = document.getElementById('reviewTextarea');
    const reviewText = textarea.value.trim();

    if (reviewText === '') {
        alert('Napisz swoją recenzję!');
        return;
    }

    console.log('Nowa recenzja:', reviewText, 'Ocena:', selectedRating);

    const reviewsList = document.getElementById('reviewsList');

    const emptyState = reviewsList.querySelector('.reviews-empty');
    if (emptyState) {
        emptyState.remove();
    }

    const newReview = createReviewCard('Ty', 'Teraz', reviewText, 0, selectedRating); // ✅ ZMIENIONE: Dodano selectedRating
    reviewsList.insertAdjacentHTML('afterbegin', newReview);

    textarea.value = '';

    selectedRating = 0;
    highlightStars(0);
    if (ratingMessage) {
        ratingMessage.textContent = '';
    }

    alert('Recenzja została dodana!');
}

// Delete Review
function deleteReview(button) {
    if (confirm('Czy na pewno chcesz usunąć tę recenzję?')) {
        const reviewCard = button.closest('.review-card');
        reviewCard.style.opacity = '0';
        reviewCard.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            reviewCard.remove();
            const reviewsList = document.getElementById('reviewsList');
            if (reviewsList.children.length === 0) {
                reviewsList.innerHTML = `
                    <div class="reviews-empty">
                        <div class="reviews-empty-icon">💬</div>
                        <p>Brak recenzji. Bądź pierwszą osobą, która doda recenzję!</p>
                    </div>
                `;
            }
        }, 300);

        // TODO: Wysłać żądanie usunięcia do backendu
        console.log('Recenzja usunięta');
    }
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