<footer class="footer-full text-center mt-auto py-3 bg-white border-top shadow-sm">
    <div class="small font-weight-bold text-secondary">
        Copyright &copy; <?= date('Y'); ?>
        <a href="https://robbyilham.com/" target="_blank" class="text-primary font-weight-bold">
            by
        </a>
        IT Development IHBS
    </div>
</footer>

</main> <!-- tutup main dari template.php -->

</div> <!-- row -->
</div> <!-- container -->

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {

    // AUTO CLOSE SIDEBAR MOBILE SAAT KLIK MENU
    $('.sidebar a').on('click', function() {
        if ($(window).width() < 768) {
            $('#sidebarMenu').removeClass('show');
            $('#sidebarOverlay').hide();
        }
    });

    // TUTUP SIDEBAR SAAT RESIZE KE DESKTOP
    $(window).resize(function() {
        if ($(window).width() >= 768) {
            $('#sidebarMenu').removeClass('show');
            $('#sidebarOverlay').hide();
        }
    });

});
</script>

<style>
.footer-full {
    width: 100%;
    position: relative;
    z-index: 10;
}

.footer-full a:hover {
    text-decoration: none;
}

@media(max-width:767px) {
    .footer-full {
        font-size: 13px;
        padding: 12px;
    }
}
</style>

</body>

</html>