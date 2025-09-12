/**
 * Main JavaScript for Reception Dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Active nav link
    const currentUrl = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(function(link) {
        const href = link.getAttribute('href');
        if (href === currentUrl || (currentUrl === '' && href === 'index.php')) {
            link.classList.add('active');
        }
    });
    
    // Initialize date pickers for all date inputs
    const dateInputs = document.querySelectorAll('input[type="datetime-local"]');
    dateInputs.forEach(function(input) {
        // No initialization needed for HTML5 datetime-local inputs
        // Just make sure they have the correct format if we're pre-filling them
        if (input.value) {
            const date = new Date(input.value);
            input.value = date.toISOString().slice(0, 16);
        }
    });
    
    // Search functionality for tables
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(function(input) {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const targetId = this.getAttribute('data-target');
            const tableBody = document.querySelector('#' + targetId + ' tbody');
            const rows = tableBody.querySelectorAll('tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.indexOf(searchTerm) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Filter by status
    const statusFilters = document.querySelectorAll('.status-filter');
    statusFilters.forEach(function(filter) {
        filter.addEventListener('click', function(e) {
            e.preventDefault();
            
            const status = this.getAttribute('data-status');
            const targetId = this.getAttribute('data-target');
            const tableBody = document.querySelector('#' + targetId + ' tbody');
            const rows = tableBody.querySelectorAll('tr');
            
            // Update active filter
            document.querySelectorAll('.status-filter').forEach(function(f) {
                f.classList.remove('active');
            });
            this.classList.add('active');
            
            // Filter rows
            if (status === 'all') {
                rows.forEach(function(row) {
                    row.style.display = '';
                });
            } else {
                rows.forEach(function(row) {
                    const statusCell = row.querySelector('.status-badge');
                    if (statusCell && statusCell.textContent.toLowerCase() === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });
    
    // Print button functionality
    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});