<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('table').forEach((table) => {
            if (table.dataset.srno === 'off' || table.querySelector(':scope > thead th[data-srno-column]')) {
                return;
            }

            const headerRow = table.querySelector(':scope > thead tr');
            const bodyRows = Array.from(table.querySelectorAll(':scope > tbody > tr'));

            if (! headerRow || bodyRows.length === 0) {
                return;
            }

            const firstHeader = headerRow.querySelector('th, td');
            const firstHeaderText = firstHeader?.textContent?.trim().toLowerCase() ?? '';

            if (['sr no', 'sr. no', 'srno', 's.no', 's no', '#'].includes(firstHeaderText)) {
                return;
            }

            const srHeader = document.createElement('th');
            srHeader.dataset.srnoColumn = 'true';
            srHeader.scope = 'col';
            srHeader.textContent = 'Sr No';
            srHeader.className = firstHeader?.className || 'px-4 py-3 text-left';
            srHeader.classList.add('whitespace-nowrap');
            headerRow.prepend(srHeader);

            let serialNumber = Number.parseInt(table.dataset.srnoStart || '1', 10);

            bodyRows.forEach((row) => {
                const cells = Array.from(row.children);

                if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                    cells[0].colSpan = cells[0].colSpan + 1;
                    return;
                }

                const srCell = document.createElement('td');
                srCell.textContent = serialNumber;
                srCell.className = cells[0]?.className || 'px-4 py-3';
                srCell.classList.add('whitespace-nowrap', 'font-medium', 'text-slate-500');
                row.prepend(srCell);
                serialNumber += 1;
            });
        });
    });
</script>
