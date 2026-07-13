document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-seat-grid-preview]');
    if (!button) {
        return;
    }

    const target = document.querySelector(button.dataset.seatGridPreview);
    if (target) {
        target.dispatchEvent(new CustomEvent('seat-grid-preview'));
    }
});
