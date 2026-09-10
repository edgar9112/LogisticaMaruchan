document.addEventListener('DOMContentLoaded', function () {
    // Sidebar toggle (mobile)
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const toggleBtn = document.getElementById('sidebar-toggle');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // Auto-dismiss alerts
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        alert.classList.add('alert-auto-dismiss');
        setTimeout(function () {
            alert.style.transition = 'all 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });

    // Animated counters
    document.querySelectorAll('[data-count-to]').forEach(function (el) {
        const target = parseInt(el.getAttribute('data-count-to'), 10);
        if (isNaN(target)) return;

        let current = 0;
        const duration = 800;
        const step = Math.max(1, Math.floor(target / (duration / 16)));
        const timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString();
        }, 16);
    });

    // Smooth page transitions
    document.querySelectorAll('a[href]').forEach(function (link) {
        if (link.hostname !== window.location.hostname) return;
        if (link.getAttribute('data-no-transition')) return;
        if (link.closest('form')) return;
    });

    // Confirm delete buttons
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Focus search on Ctrl+K or /
    document.addEventListener('keydown', function (e) {
        if ((e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) ||
            (e.ctrlKey && e.key === 'k')) {
            e.preventDefault();
            const searchInput = document.querySelector('.search-wrapper input, input[name="search"]');
            if (searchInput) searchInput.focus();
        }
    });
});
