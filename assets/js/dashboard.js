// assets/js/dashboard.js — Chart.js graphs, live stats

document.addEventListener('DOMContentLoaded', () => {
    initLoadChart();
});

function initLoadChart() {
    const ctx = document.getElementById('loadChart');
    if (!ctx) return;

    // Fetch distribution data
    apiRequest('/api/teachers.php?action=distribution')
        .then(data => {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels || ['0-25%', '26-50%', '51-75%', '76-100%', 'Overloaded'],
                    datasets: [{
                        label: 'Teachers',
                        data: data.values || [0,0,0,0,0],
                        backgroundColor: [
                            '#10b981',
                            '#3b82f6',
                            '#f59e0b',
                            '#8b5cf6',
                            '#ef4444'
                        ],
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.raw} teacher(s)`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: '#f3f4f6' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Chart load failed:', err);
            ctx.parentElement.innerHTML = '<div class="empty-state">Unable to load chart data.</div>';
        });
}
