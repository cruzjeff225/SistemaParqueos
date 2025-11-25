$(document).ready(function() {
    
    // Alertify configurations
    alertify.set('notifier', 'position', 'top-right');
    
    // show/hide password toggle
    $('#togglePassword').click(function() {
        const input = $('#password');
        const icon = $('#toggleIcon');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Submit login form
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        
        // Obtener datos
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const remember = $('#remember').is(':checked');
        
        // Validate empty fields
        if (!email || !password) {
            mostrarAlerta('Por favor completa todos los campos');
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            mostrarAlerta('Ingresa un correo electrónico válido');
            return;
        }
        
        // Validate length of password
        if (password.length < 6) {
            mostrarAlerta('La contraseña debe tener al menos 6 caracteres');
            return;
        }
        
        // Show loading state
        activarLoading(true);
        
        // AJAX request
        $.ajax({
            url: '../controllers/authController.php',
            type: 'POST',
            data: {
                action: 'login',
                email: email,
                password: password,
                remember: remember ? 1 : 0
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Successful login
                    alertify.success(response.message);
                    
                    // RRedirect after 1 second
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1000);
                } else {
                    // Failed login
                    mostrarAlerta(response.message);
                    activarLoading(false);
                    
                    // Cleatr password field and focus
                    $('#password').val('').focus();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                console.error('Response:', xhr.responseText);
                
                let mensaje = 'Error al conectar con el servidor';
                
                // Get message from response if available
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        mensaje = response.message;
                    }
                } catch (e) {
                    // Or use default message
                }
                
                mostrarAlerta(mensaje);
                activarLoading(false);
            }
        });
    });
    
    // Show alert message
    function mostrarAlerta(mensaje) {
        const html = `
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alert-container').html(html);
        
        // Auto close after 5 seconds
        setTimeout(function() {
            $('#alert-container .alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Activate/deactivate loading state
    function activarLoading(activo) {
        const btn = $('#btnLogin');
        const btnText = $('#btnText');
        const btnSpinner = $('#btnSpinner');
        const inputs = $('#loginForm input, #loginForm button[type="button"]');
        
        if (activo) {
            btn.prop('disabled', true);
            btnText.text('Iniciando sesión...');
            btnSpinner.removeClass('d-none');
            inputs.prop('disabled', true);
        } else {
            btn.prop('disabled', false);
            btnText.text('Iniciar Sesión');
            btnSpinner.addClass('d-none');
            inputs.prop('disabled', false);
        }
    }
    
    // Clear alerts on input
    $('#username, #password').on('input', function() {
        $('#alert-container').empty();
    });
    
    // Focus on username field on load
    $('#username').focus();
    
});