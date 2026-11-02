<footer>
				<div class="footer-inner">
					<div class="pull-left">
					<span class="text-bold text-uppercase">MedTech Hospital Management System</span>
					</div>
					<div class="pull-right">
						<span class="go-top"><i class="ti-angle-up"></i></span>
					</div>
				</div>
			</footer>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var currentPage = window.location.pathname.split('/').pop().toLowerCase();
    var currentModule = new URLSearchParams(window.location.search).get('module');
    document.querySelectorAll('#sidebar .main-navigation-menu a[href]').forEach(function (link) {
        if (link.pathname.split('/').pop().toLowerCase() === currentPage && (!link.dataset.module || link.dataset.module === currentModule)) {
            link.setAttribute('aria-current', 'page');
            var item = link.closest('li');
            if (item) item.classList.add('active');
            var group = link.closest('.sub-menu');
            if (group && group.parentElement) group.parentElement.classList.add('active', 'open');
        }
    });
});
</script>
