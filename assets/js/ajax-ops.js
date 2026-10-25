// TransitOps AJAX & Table Operations Helper

const AjaxOps = {
    // Show SweetAlert2 Toast notification
    toast: function(message, type = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        Toast.fire({
            icon: type,
            title: message
        });
    },

    // Global loading spinner helper
    showSpinner: function() {
        const spinner = document.getElementById('global-spinner');
        if (spinner) spinner.classList.remove('d-none');
    },

    hideSpinner: function() {
        const spinner = document.getElementById('global-spinner');
        if (spinner) spinner.classList.add('d-none');
    },

    // Perform database delete operation with SweetAlert confirmation
    deleteConfirm: function(url, id, callback) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d94368',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                AjaxOps.showSpinner();
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    AjaxOps.hideSpinner();
                    if (data.success) {
                        AjaxOps.toast(data.message || 'Record deleted successfully.', 'success');
                        if (typeof callback === 'function') {
                            callback(data);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        Swal.fire('Error!', data.error || 'Failed to delete record.', 'error');
                    }
                })
                .catch(err => {
                    AjaxOps.hideSpinner();
                    Swal.fire('Error!', 'A system error occurred. Please try again.', 'error');
                });
            }
        });
    },

    // Handle AJAX Form Submissions (supports files automatically)
    submitForm: function(formElement, successCallback, errorCallback) {
        if (!formElement) {
            throw new Error('Cannot submit form: form element was not found.');
        }
        FormValidation.attach(formElement);

        formElement.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!FormValidation.validateForm(formElement)) return;
            
            // Disable submit buttons to prevent double click
            const submitBtn = formElement.querySelector('[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            }
            
            AjaxOps.showSpinner();
            const formData = new FormData(formElement);
            
            fetch(formElement.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                return response.json().catch(() => {
                    throw new Error('The server returned an invalid response. Please try again.');
                }).then(data => {
                    if (!response.ok) {
                        throw new Error(data.error || 'Please correct the highlighted fields.');
                    }
                    return data;
                });
            })
            .then(data => {
                AjaxOps.hideSpinner();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }

                if (data.success) {
                    AjaxOps.toast(data.message || 'Data saved successfully.', 'success');
                    if (typeof successCallback === 'function') {
                        successCallback(data);
                    } else {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    Swal.fire('Validation Error', data.error || 'Please check your input values.', 'warning');
                    if (typeof errorCallback === 'function') {
                        errorCallback(data);
                    }
                }
            })
            .catch(err => {
                AjaxOps.hideSpinner();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                Swal.fire('Operation Failed', err.message || 'A server error occurred.', 'error');
                if (typeof errorCallback === 'function') {
                    errorCallback(err);
                }
            });
        });
    },

    // Standard DataTable Initialization wrapper
    initTable: function(selector, options = {}) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        const defaultOptions = {
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ records",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "<i class='fas fa-chevron-right'></i>",
                    previous: "<i class='fas fa-chevron-left'></i>"
                }
            }
        };

        const mergedOptions = { ...defaultOptions, ...options };
        
        // Destroy existing instance to avoid error re-init
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
        }

        const table = $(selector).DataTable(mergedOptions);
        
        // Listen to themeChanged event to apply adjustments if any
        window.addEventListener('themeChanged', function() {
            // DataTables handles colors inside CSS stylesheets mostly, but we trigger a redraw to adjust styling
            table.draw();
        });

        return table;
    },

    // Load data from URL into form fields for editing
    editLoad: function(url, callback) {
        AjaxOps.showSpinner();
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            AjaxOps.hideSpinner();
            if (data.success) {
                callback(data.data);
            } else {
                Swal.fire('Error', data.error || 'Failed to fetch record details.', 'error');
            }
        })
        .catch(err => {
            AjaxOps.hideSpinner();
            Swal.fire('Error', 'Unable to connect to server.', 'error');
        });
    }
};
