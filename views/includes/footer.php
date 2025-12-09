<?php
?>
</main>
<!-- Main Content End -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Alertify -->
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<!-- Global Script -->
<script>
$(document).ready(function() {
    // Configurar Alertify
    alertify.set('notifier', 'position', 'top-right');
    
    // Auto-cerrar navbar en mobile al hacer clic en un enlace
    $('.navbar-nav .nav-link').on('click', function() {
        if ($(window).width() < 992) {
            $('.navbar-collapse').collapse('hide');
        }
    });
});
</script>

<!-- Page Specific Scripts -->
<?php if (isset($pageScripts)): ?>
    <?php echo $pageScripts; ?>
<?php endif; ?>

</body>
</html>