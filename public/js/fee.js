document.addEventListener('input', (event) => {
    const form = event.target.closest('[data-fee-calculator]');
    if (!form) {
        return;
    }

    const gross = Number(form.querySelector('[name="gross_amount"]')?.value || 0);
    const concession = Number(form.querySelector('[name="concession_amount"]')?.value || 0);
    const scholarship = Number(form.querySelector('[name="scholarship_amount"]')?.value || 0);
    const target = form.querySelector('[data-net-payable]');

    if (target) {
        target.textContent = Math.max(0, gross - concession - scholarship).toFixed(2);
    }
});
