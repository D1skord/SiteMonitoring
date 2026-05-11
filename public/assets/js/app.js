document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('tableSearch');
    const table = document.getElementById('sitesTable');

    if (searchInput && table) {
        searchInput.addEventListener('keyup', (e) => {
            const searchText = e.target.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    }
});