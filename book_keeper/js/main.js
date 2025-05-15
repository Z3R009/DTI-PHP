/**
 * Book Keeper Main JavaScript File
 * Handles sidebar toggling, dropdown menus, and other UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap components
    initBootstrapComponents();
    
    // Setup sidebar toggle
    setupSidebarToggle();
    
    // Setup dropdown menu functionality
    setupDropdownMenus();
});

/**
 * Initialize Bootstrap components
 */
function initBootstrapComponents() {
    // Initialize all dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Initialize all collapse elements
    var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
    collapseElementList.map(function(collapseEl) {
        return new bootstrap.Collapse(collapseEl, {
            toggle: false
        });
    });
    
    // Initialize tooltips if any
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Setup sidebar toggle functionality
 */
function setupSidebarToggle() {
    // Toggle sidebar on button click
    const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', function() {
            document.body.classList.toggle('toggle-sidebar');
        });
    }
    
    // Add active class to current page in sidebar
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link, .sidebar-nav .nav-content a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
            
            // If the active link is in a collapsible menu, expand that menu
            const parent = link.closest('.nav-content');
            if (parent && parent.classList.contains('collapse')) {
                const bsCollapse = new bootstrap.Collapse(parent);
                bsCollapse.show();
                
                // Update the parent toggle button
                const parentToggle = document.querySelector(`[data-bs-target="#${parent.id}"]`);
                if (parentToggle) {
                    parentToggle.classList.remove('collapsed');
                }
            }
        }
    });
}

/**
 * Setup dropdown menu functionality
 */
function setupDropdownMenus() {
    // Profile dropdown menu
    const profileDropdown = document.querySelector('.nav-item.dropdown.pe-3 .nav-link');
    if (profileDropdown) {
        profileDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = bootstrap.Dropdown.getInstance(this);
            if (!dropdown) {
                new bootstrap.Dropdown(this).toggle();
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const dropdownMenus = document.querySelectorAll('.dropdown-menu.show');
        dropdownMenus.forEach(menu => {
            const dropdown = menu.closest('.dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'));
                if (bsDropdown) {
                    bsDropdown.hide();
                }
            }
        });
    });
} 