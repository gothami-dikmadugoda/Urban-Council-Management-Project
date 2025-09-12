    </div><!-- /.container (opened in header) -->
    
    <footer class="footer mt-5 py-3 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="h5">Receptionist Dashboard</h2>
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Reception Management System</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h2 class="h5">Accessibility</h2>
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="#" class="text-white text-decoration-none" data-bs-toggle="modal" data-bs-target="#accessibilityModal">
                                Accessibility Statement
                            </a>
                        </li>
                        <li class="list-inline-item">|</li>
                        <li class="list-inline-item">
                            <a href="#" class="text-white text-decoration-none" data-bs-toggle="modal" data-bs-target="#accessibilityHelpModal">
                                Help
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Accessibility Statement Modal -->
    <div class="modal fade" id="accessibilityModal" tabindex="-1" aria-labelledby="accessibilityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accessibilityModalLabel">Accessibility Statement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Our Commitment to Accessibility</h6>
                    <p>The Receptionist Dashboard is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.</p>
                    
                    <h6>Conformance Status</h6>
                    <p>The Web Content Accessibility Guidelines (WCAG) defines requirements for designers and developers to improve accessibility for people with disabilities. It defines three levels of conformance: Level A, Level AA, and Level AAA. The Receptionist Dashboard is partially conformant with WCAG 2.1 level AA. Partially conformant means that some parts of the content do not fully conform to the accessibility standard.</p>
                    
                    <h6>Accessibility Features</h6>
                    <ul>
                        <li>Screen reader compatibility</li>
                        <li>Keyboard navigation</li>
                        <li>High contrast mode</li>
                        <li>Text resizing</li>
                        <li>Animation control</li>
                        <li>ARIA attributes</li>
                    </ul>
                    
                    <h6>Feedback</h6>
                    <p>We welcome your feedback on the accessibility of the Receptionist Dashboard. Please let us know if you encounter accessibility barriers.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FullCalendar JS (Only loaded on calendar page) -->
    <?php if (isset($use_calendar) && $use_calendar) : ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <?php endif; ?>
    
    <!-- Custom JS -->
    <script src="js/scripts.js"></script>
    
    <!-- Accessibility JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Keyboard navigation enhancement
        document.addEventListener('keydown', function(e) {
            // Alt + 1-7: Navigate to main menu items
            if (e.altKey && e.key >= '1' && e.key <= '7') {
                e.preventDefault();
                const index = parseInt(e.key) - 1;
                const menuItems = document.querySelectorAll('#navbarMain .nav-link');
                if (menuItems[index]) {
                    menuItems[index].click();
                }
            }
            
            // Alt + S: Focus search (if exists)
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                const searchInput = document.querySelector('input[type="search"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Alt + H: Go to home
            if (e.altKey && e.key === 'h') {
                e.preventDefault();
                window.location.href = 'index.php';
            }
            
            // Alt + A: Open accessibility panel
            if (e.altKey && e.key === 'a') {
                e.preventDefault();
                new bootstrap.Offcanvas(document.getElementById('accessibilityOffcanvas')).show();
            }
        });
        
        // Load and apply saved accessibility settings
        function loadAccessibilitySettings() {
            const savedSettings = localStorage.getItem('accessibilitySettings');
            
            if (savedSettings) {
                const settings = JSON.parse(savedSettings);
                
                // Set form values in offcanvas
                document.getElementById('quickFontSize').value = settings.fontSize || 'normal';
                document.getElementById('quickContrastMode').value = settings.contrastMode || 'normal';
                document.getElementById('quickAnimationSetting').value = settings.animationSetting || 'enabled';
                document.getElementById('screenReaderOptimized').checked = settings.screenReaderOptimized || false;
                
                // Apply settings
                applyAccessibilitySettings();
            }
        }
        
        // Apply accessibility settings to the page
        function applyAccessibilitySettings() {
            const settings = JSON.parse(localStorage.getItem('accessibilitySettings')) || {
                fontSize: 'normal',
                contrastMode: 'normal',
                animationSetting: 'enabled',
                screenReaderOptimized: false
            };
            
            // Apply font size
            document.body.classList.remove('font-large', 'font-x-large');
            if (settings.fontSize === 'large') {
                document.body.classList.add('font-large');
                document.body.style.fontSize = '1.2rem';
            } else if (settings.fontSize === 'x-large') {
                document.body.classList.add('font-x-large');
                document.body.style.fontSize = '1.4rem';
            } else {
                document.body.style.fontSize = '';
            }
            
            // Apply contrast
            document.body.classList.toggle('high-contrast', settings.contrastMode === 'high-contrast');
            
            // Apply animation settings
            document.body.classList.remove('no-animations', 'reduced-animations');
            if (settings.animationSetting === 'disabled') {
                document.body.classList.add('no-animations');
            } else if (settings.animationSetting === 'reduced') {
                document.body.classList.add('reduced-animations');
            }
            
            // Apply screen reader optimizations
            document.body.classList.toggle('sr-optimized', settings.screenReaderOptimized);
            
            // If screen reader optimized, add more descriptive text to certain elements
            if (settings.screenReaderOptimized) {
                enhanceForScreenReaders();
            }
            
            // Announce changes to screen readers
            announceToScreenReader('Accessibility settings have been applied.');
        }
        
        // Save accessibility settings
        document.getElementById('saveQuickAccessibilitySettings').addEventListener('click', function() {
            const fontSize = document.getElementById('quickFontSize').value;
            const contrastMode = document.getElementById('quickContrastMode').value;
            const animationSetting = document.getElementById('quickAnimationSetting').value;
            const screenReaderOptimized = document.getElementById('screenReaderOptimized').checked;
            
            // Save to localStorage
            localStorage.setItem('accessibilitySettings', JSON.stringify({
                fontSize,
                contrastMode,
                animationSetting,
                screenReaderOptimized
            }));
            
            // Apply settings
            applyAccessibilitySettings();
            
            // Close the offcanvas
            const offcanvasElement = document.getElementById('accessibilityOffcanvas');
            const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
            offcanvas.hide();
            
            // Show confirmation
            announceToScreenReader('Accessibility settings have been saved and applied.');
        });
        
        // Function to enhance page elements for screen readers
        function enhanceForScreenReaders() {
            // Add more descriptive labels to icons
            document.querySelectorAll('i.bi').forEach(icon => {
                if (!icon.nextElementSibling) {
                    const parentText = icon.parentElement.textContent.trim();
                    if (parentText) {
                        icon.setAttribute('aria-label', parentText);
                    }
                }
            });
            
            // Enhance table cells with more context
            document.querySelectorAll('table').forEach(table => {
                const headers = Array.from(table.querySelectorAll('th')).map(th => th.textContent.trim());
                
                table.querySelectorAll('tbody tr').forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (headers[index] && !cell.getAttribute('data-label')) {
                            cell.setAttribute('data-label', headers[index]);
                        }
                    });
                });
            });
        }
        
        // Function to make announcements to screen readers
        function announceToScreenReader(message) {
            const announcer = document.getElementById('sr-announcements');
            if (announcer) {
                announcer.textContent = message;
                
                // Clear after a delay to allow for re-announcing the same message
                setTimeout(() => {
                    announcer.textContent = '';
                }, 3000);
            }
        }
        
        // Load settings on page load
        loadAccessibilitySettings();
    });
    </script>
</body>
</html>