// Add Review
function addReview() {
    const textarea = document.getElementById('reviewTextarea');
    const reviewText = textarea.value.trim();

    if (reviewText === '') {
        alert('Napisz swoją recenzję!');
        return;
    }

    // TODO: Wysłać do backendu
    console.log('Nowa recenzja:', reviewText);

    // Mockup - dodaj recenzję do listy
    const reviewsList = document.getElementById('reviewsList');
    const newReview = createReviewCard('Ty', 'Teraz', reviewText, 0);
    reviewsList.insertAdjacentHTML('afterbegin', newReview);

    // Wyczyść textarea
    textarea.value = '';

    alert('Recenzja została dodana!');
}

// Delete Review (tylko admin)
function deleteReview(button) {
    if (confirm('Czy na pewno chcesz usunąć tę recenzję?')) {
        const reviewCard = button.closest('.review-card');
        reviewCard.style.opacity = '0';
        reviewCard.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            reviewCard.remove();
        }, 300);

        // TODO: Wysłać żądanie usunięcia do backendu
        console.log('Recenzja usunięta');
    }
}

// Create Review Card HTML
function createReviewCard(author, date, content, helpful) {
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
                    <span>-/10</span>
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

// Animation keyframes (dodaj do CSS)
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