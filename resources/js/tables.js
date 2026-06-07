// Tables functionality (sorting, filtering, bulk actions)

class TableManager {
    constructor(tableId) {
        this.table = document.getElementById(tableId);
        if (!this.table) return;

        this.selectAllCheckbox = this.table.querySelector('.select-all');
        this.rowCheckboxes = this.table.querySelectorAll('.row-checkbox');

        this.init();
    }

    init() {
        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.addEventListener('change', (e) => this.toggleAll(e.target.checked));
        }

        this.rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => this.updateSelectAllState());
        });
    }

    toggleAll(checked) {
        this.rowCheckboxes.forEach(cb => {
            cb.checked = checked;
        });
    }

    updateSelectAllState() {
        const allChecked = Array.from(this.rowCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(this.rowCheckboxes).some(cb => cb.checked);

        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.checked = allChecked;
            this.selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }

    // Example sort function (simple client-side sort)
    sortTable(columnIndex, type = 'string') {
        const tbody = this.table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        const sortedRows = rows.sort((a, b) => {
            const aVal = a.children[columnIndex].textContent.trim();
            const bVal = b.children[columnIndex].textContent.trim();

            if (type === 'number') {
                return parseFloat(aVal) - parseFloat(bVal);
            }
            return aVal.localeCompare(bVal);
        });

        // Toggle sort order logic would go here (asc/desc)

        tbody.innerHTML = '';
        sortedRows.forEach(row => tbody.appendChild(row));
    }
}
