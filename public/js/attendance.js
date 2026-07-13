document.addEventListener('change', (event) => {
    if (!event.target.matches('[data-attendance-select-all]')) {
        return;
    }

    document.querySelectorAll(event.target.dataset.attendanceSelectAll).forEach((input) => {
        input.checked = event.target.checked;
    });
});
