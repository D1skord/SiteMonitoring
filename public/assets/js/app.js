document.addEventListener('DOMContentLoaded', () => {
    const userDropdownToggle = document.querySelector('.js-user-dropdown-toggle');
    const userDropdownMenu = document.querySelector('.js-user-dropdown-menu');

    if (!userDropdownToggle || !userDropdownMenu) {
        return;
    }

    const closeUserDropdown = () => {
        userDropdownToggle.classList.remove('show');
        userDropdownToggle.setAttribute('aria-expanded', 'false');
        userDropdownMenu.classList.remove('show');
    };

    userDropdownToggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        const isOpen = userDropdownMenu.classList.contains('show');
        userDropdownToggle.classList.toggle('show', !isOpen);
        userDropdownToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        userDropdownMenu.classList.toggle('show', !isOpen);
    });

    document.addEventListener('click', (event) => {
        if (!userDropdownMenu.contains(event.target)) {
            closeUserDropdown();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeUserDropdown();
        }
    });
});

// Call the dataTables jQuery plugin
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#dataTable').DataTable();
    }
});
