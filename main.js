// Sidebar toggle: collapse/expand
const toggleSidebar = document.getElementById('toggle-sidebar');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');

let collapsed = false;
toggleSidebar.addEventListener('click', function() {
    collapsed = !collapsed;
    sidebar.classList.toggle('collapsed', collapsed);
    mainContent.classList.toggle('sidebar-collapsed', collapsed);
});

// Responsive sidebar for mobile/tablet
function checkWidth() {
    if (window.innerWidth <= 992) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
        collapsed = true;
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
        collapsed = false;
    }
}
window.addEventListener('load', checkWidth);
window.addEventListener('resize', checkWidth);

// User Profile dropdown
const userProfile = document.getElementById('user-profile');
const dropdownMenu = document.getElementById('dropdown-menu');
userProfile.addEventListener('click', function(e) {
    e.stopPropagation();
    dropdownMenu.classList.toggle('show');
});
document.addEventListener('click', function() {
    dropdownMenu.classList.remove('show');
});