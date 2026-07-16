<script>
    document.addEventListener('DOMContentLoaded', () => {
        const baseButtonClass = 'group inline-flex h-9 items-center gap-2 rounded-md border px-3 text-xs font-black shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2';
        const importButtonClass = `${baseButtonClass} border-emerald-200 bg-emerald-50 text-emerald-800 hover:border-emerald-300 hover:bg-emerald-600 hover:text-white focus:ring-emerald-500`;
        const exportButtonClass = `${baseButtonClass} ui-action border-cyan-200 bg-cyan-700 text-white focus:ring-cyan-500`;
        const iconClass = 'grid h-5 w-5 place-items-center rounded bg-white/80 text-current shadow-sm transition group-hover:bg-white/20';

        const buttonIcon = (path) => {
            const span = document.createElement('span');
            span.className = iconClass;
            span.innerHTML = `<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">${path}</svg>`;
            return span;
        };

        const cleanText = (value) => (value || '')
            .replace(/\s+/g, ' ')
            .replace(/\u00a0/g, ' ')
            .trim();

        const csvCell = (value) => {
            const text = cleanText(value);
            return /[",\n]/.test(text) ? `"${text.replace(/"/g, '""')}"` : text;
        };

        const tableTitle = (table, index) => {
            const heading = table.closest('section, div')?.querySelector('h1, h2, h3, h4')?.textContent;
            return cleanText(heading || document.title || `table-${index + 1}`)
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '') || `table-${index + 1}`;
        };

        const exportTable = (table, index) => {
            const rows = Array.from(table.querySelectorAll('tr'))
                .map((row) => Array.from(row.children)
                    .filter((cell) => ! cell.matches('[data-export-ignore]'))
                    .map((cell) => csvCell(cell.innerText))
                    .join(','))
                .filter(Boolean);

            if (rows.length === 0) {
                return;
            }

            const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = `${tableTitle(table, index)}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        };

        const ensureHorizontalScroll = (table) => {
            const existingWrapper = table.closest('.overflow-x-auto, [data-table-scroll-wrapper="true"]');

            table.classList.add('w-full');
            table.style.width = '100%';
            table.style.minWidth = 'max(100%, max-content)';

            if (existingWrapper) {
                existingWrapper.style.maxWidth = '100%';
                existingWrapper.style.overflowX = 'auto';
                existingWrapper.style.webkitOverflowScrolling = 'touch';
                return existingWrapper;
            }

            const cardWrapper = table.closest('.overflow-hidden');

            if (cardWrapper && cardWrapper.contains(table)) {
                cardWrapper.style.maxWidth = '100%';
                cardWrapper.style.overflowX = 'auto';
                cardWrapper.style.webkitOverflowScrolling = 'touch';
                cardWrapper.dataset.tableScrollWrapper = 'true';
                return cardWrapper;
            }

            const scrollWrapper = document.createElement('div');
            scrollWrapper.className = 'w-full overflow-x-auto';
            scrollWrapper.dataset.tableScrollWrapper = 'true';
            scrollWrapper.style.maxWidth = '100%';
            scrollWrapper.style.webkitOverflowScrolling = 'touch';

            table.parentNode?.insertBefore(scrollWrapper, table);
            scrollWrapper.appendChild(table);

            return scrollWrapper;
        };

        document.querySelectorAll('table').forEach((table, index) => {
            const wrapper = ensureHorizontalScroll(table);

            if (table.dataset.tools === 'off' || table.dataset.tableToolsReady === 'true') {
                return;
            }

            table.dataset.tableToolsReady = 'true';

            const toolbar = document.createElement('div');
            toolbar.className = 'mb-3 flex flex-wrap items-center justify-end gap-2';
            const enableImport = table.dataset.importEnabled === 'true';

            const importInput = document.createElement('input');
            importInput.type = 'file';
            importInput.accept = '.csv,text/csv';
            importInput.className = 'hidden';

            const importButton = document.createElement('button');
            importButton.type = 'button';
            importButton.className = importButtonClass;
            importButton.append(
                buttonIcon('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0-12 4 4m-4-4-4 4M5 15v4h14v-4"/>'),
                document.createTextNode('Import CSV')
            );
            importButton.addEventListener('click', () => importInput.click());

            importInput.addEventListener('change', () => {
                const file = importInput.files?.[0];
                if (! file) {
                    return;
                }

                table.dispatchEvent(new CustomEvent('table:import-selected', {
                    bubbles: true,
                    detail: { file, table },
                }));

                alert('CSV selected. This table is ready for a module import handler.');
                importInput.value = '';
            });

            const exportButton = document.createElement('button');
            exportButton.type = 'button';
            exportButton.className = exportButtonClass;
            exportButton.append(
                buttonIcon('<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V9m0 12 4-4m-4 4-4-4M5 5h14v4H5z"/>'),
                document.createTextNode('Export CSV')
            );
            exportButton.addEventListener('click', () => exportTable(table, index));

            if (enableImport) {
                toolbar.append(importButton, importInput);
            }

            toolbar.append(exportButton);

            wrapper.parentNode?.insertBefore(toolbar, wrapper);
        });
    });
</script>
