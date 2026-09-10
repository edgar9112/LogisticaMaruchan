import Chart from 'chart.js/auto';

const COLORS = [
    '#0d6efd', '#6f42c1', '#d63384', '#fd7e14', '#ffc107', '#198754',
    '#20c997', '#0dcaf0', '#6c757d', '#dc3545', '#6610f2', '#adb5bd',
];

const palette = (n) => Array.from({ length: n }, (_, i) => COLORS[i % COLORS.length]);

document.addEventListener('DOMContentLoaded', () => {
    const data = window.dashboardData;
    if (!data) {
        return;
    }

    renderDoughnut('ordersByStatusChart', data.ordersByStatus);
    renderDoughnut('shipmentsByStatusChart', data.shipmentsByStatus);
    renderLine('ordersLast14DaysChart', data.ordersLast14Days);
    renderHorizontalBar('incidentsByTypeChart', data.incidentsByType);
});

function renderDoughnut(canvasId, items) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    const totals = items.map((item) => item.total);
    if (totals.every((total) => total === 0)) {
        canvas.parentElement.innerHTML = '<p class="text-muted mb-0">Sin datos registrados.</p>';

        return;
    }

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: items.map((item) => item.label),
            datasets: [{
                data: totals,
                backgroundColor: palette(items.length),
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12 },
                },
            },
        },
    });
}

function renderLine(canvasId, items) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: items.map((item) => item.date),
            datasets: [{
                label: 'Pedidos',
                data: items.map((item) => item.total),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.15)',
                fill: true,
                tension: 0.3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                },
            },
        },
    });
}

function renderHorizontalBar(canvasId, items) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return;
    }

    const totals = items.map((item) => item.total);
    if (totals.every((total) => total === 0)) {
        canvas.parentElement.innerHTML = '<p class="text-muted mb-0">Sin incidencias registradas.</p>';

        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: items.map((item) => item.label),
            datasets: [{
                label: 'Incidencias',
                data: totals,
                backgroundColor: palette(items.length),
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                },
            },
        },
    });
}