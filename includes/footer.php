<?php
/**
 * GV Florida Fleet Management System
 * Footer Component
 */
?>

</div> <!-- End main-content -->

<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- SweetAlert2 for better alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom JavaScript -->
<script>
$(document).ready(function() {
    // Sidebar toggle functionality
    $('#sidebarToggle').click(function() {
        $('#sidebar').toggleClass('collapsed');
        $('.main-content').toggleClass('sidebar-collapsed');
        
        // Store sidebar state in localStorage
        const isCollapsed = $('#sidebar').hasClass('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });
    
    // Restore sidebar state from localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        $('#sidebar').addClass('collapsed');
        $('.main-content').addClass('sidebar-collapsed');
    }
    
    // Mobile sidebar toggle
    $(window).resize(function() {
        if ($(window).width() <= 768) {
            $('#sidebar').addClass('collapsed');
            $('.main-content').addClass('sidebar-collapsed');
        }
    });
    
    // Initialize DataTables with default settings
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: <?= RECORDS_PER_PAGE ?>,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "Search records:",
                lengthMenu: "Show _MENU_ records per page",
                info: "Showing _START_ to _END_ of _TOTAL_ records",
                infoEmpty: "No records available",
                infoFiltered: "(filtered from _MAX_ total records)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: "No data available in table",
                zeroRecords: "No matching records found"
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -1 }
            ]
        });
    }
    
    // Form validation and AJAX submission
    $('form.ajax-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        submitBtn.prop('disabled', true);
        
        // Serialize form data
        const formData = new FormData(this);
        
        // Submit via AJAX
        $.ajax({
            url: form.attr('action') || window.location.href,
            type: form.attr('method') || 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Operation completed successfully',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    // Close modal if exists
                    form.closest('.modal').modal('hide');
                    
                    // Reload page or update table
                    if (response.reload) {
                        setTimeout(() => window.location.reload(), 1500);
                    } else if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 1500);
                    } else {
                        // Refresh DataTable if exists
                        if ($.fn.DataTable && $('.data-table').length) {
                            $('.data-table').DataTable().ajax.reload();
                        }
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'An error occurred',
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error!',
                    text: 'Please check your connection and try again.',
                });
            },
            complete: function() {
                // Restore button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Confirm delete operations
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        
        const url = $(this).attr('href');
        const itemName = $(this).data('name') || 'this item';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${itemName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Status update functions
    window.updateStatus = function(table, id, status, callback) {
        $.ajax({
            url: 'api/update_status.php',
            type: 'POST',
            data: {
                table: table,
                id: id,
                status: status,
                csrf_token: '<?= $_SESSION['csrf_token'] ?>'
            },
            success: function(response) {
                if (response.success) {
                    if (callback) callback(response);
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Refresh page after delay
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Failed to update status. Please try again.'
                });
            }
        });
    };
    
    // Export functions
    window.exportTable = function(format, table) {
        const form = $('<form>', {
            method: 'POST',
            action: 'api/export.php'
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'format',
            value: format
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'table',
            value: table
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'csrf_token',
            value: '<?= $_SESSION['csrf_token'] ?>'
        }));
        
        $('body').append(form);
        form.submit();
        form.remove();
    };
    
    // Real-time notifications (placeholder for future WebSocket implementation)
    window.checkNotifications = function() {
        $.ajax({
            url: 'api/notifications.php',
            type: 'GET',
            success: function(response) {
                if (response.notifications && response.notifications.length > 0) {
                    // Update notification badge
                    $('.notification-badge').text(response.notifications.length);
                    
                    // Show new notifications
                    response.notifications.forEach(function(notification) {
                        if (notification.type === 'urgent') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Urgent Alert',
                                text: notification.message,
                                confirmButtonText: 'Acknowledge'
                            });
                        }
                    });
                }
            }
        });
    };
    
    // Check notifications every 30 seconds
    setInterval(checkNotifications, 30000);
    
    // Print functionality
    window.printPage = function() {
        window.print();
    };
    
    // Initialize any additional page-specific scripts
    if (typeof initPageScripts === 'function') {
        initPageScripts();
    }
});

// Global utility functions
window.formatCurrency = function(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

window.formatDate = function(dateString, includeTime = false) {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };
    
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    
    return date.toLocaleDateString('en-US', options);
};

window.showLoading = function(element) {
    $(element).addClass('loading');
};

window.hideLoading = function(element) {
    $(element).removeClass('loading');
};

// CSRF token for AJAX requests
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
            xhr.setRequestHeader("X-CSRFToken", '<?= $_SESSION['csrf_token'] ?>');
        }
    }
});

// Session timeout warning
let sessionWarningShown = false;
setInterval(function() {
    const sessionTimeout = <?= SESSION_TIMEOUT ?> * 1000; // Convert to milliseconds
    const lastActivity = <?= $_SESSION['last_activity'] ?> * 1000; // Convert to milliseconds
    const timeLeft = sessionTimeout - (Date.now() - lastActivity);
    
    // Show warning 5 minutes before session expires
    if (timeLeft < 300000 && !sessionWarningShown) {
        sessionWarningShown = true;
        Swal.fire({
            icon: 'warning',
            title: 'Session Expiring',
            text: 'Your session will expire in 5 minutes. Please save your work.',
            confirmButtonText: 'Extend Session'
        }).then((result) => {
            if (result.isConfirmed) {
                // Ping server to extend session
                $.ajax({
                    url: 'api/extend_session.php',
                    type: 'POST',
                    success: function() {
                        sessionWarningShown = false;
                    }
                });
            }
        });
    }
}, 60000); // Check every minute
</script>

<?php if (isset($additionalScripts)): ?>
    <?= $additionalScripts ?>
<?php endif; ?>

<!-- Footer -->
<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted">
                    &copy; <?= date('Y') ?> <?= COMPANY_NAME ?>. All rights reserved.
                </span>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">
                    <?= APP_NAME ?> v<?= APP_VERSION ?> | 
                    <a href="#" class="text-decoration-none">Support</a> |
                    <a href="#" class="text-decoration-none">Documentation</a>
                </span>
            </div>
        </div>
    </div>
</footer>

</body>
</html>