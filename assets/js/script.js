document.addEventListener('DOMContentLoaded', function () {

    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (menuToggle && sidebar) {

        menuToggle.addEventListener('click', function () {

            sidebar.classList.toggle('show');

            const isOpen = sidebar.classList.contains('show');

            menuToggle.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );
        });
    }

});