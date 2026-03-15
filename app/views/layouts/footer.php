<!-- FOOTER.PHP
 Location: vetapp/app/views/layouts/footer.php
  -->

<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <p class="mb-0 text-secondary" style="letter-spacing: 1px; font-size: 0.9rem;">
            Creado por: <span class="text-white fw-bold text-uppercase">Christian Rodriguez Rivadeneira</span>
        </p>
        <p class="mb-0 mt-1 small text-white-50">
            &copy; <?= date("Y"); ?> | Todos los derechos reservados
        </p>
    </div>
</footer>

<script src="<?= BASE_URL ?>js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- ******************* librería SweetAlert2 ******************* -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- ******************* mostrar mensaje success usando SweetAlert2 ******************* -->
<?php
if (isset($_SESSION['success'])):
    ?>

    <script>

        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '<?= $_SESSION['success'] ?>',
            confirmButtonColor: '#3085d6'
        });

    </script>

    <?php
    // ******************* eliminar mensaje después de mostrarlo *******************
    unset($_SESSION['success']);
endif;
?>

<!-- ******************* mostrar mensaje de error ******************* -->
<?php
if (isset($_SESSION['error'])):
    ?>

    <script>

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= $_SESSION['error'] ?>',
            confirmButtonColor: '#d33'
        });

    </script>

    <?php
    unset($_SESSION['error']);
endif;
?>


</body>

</html>