/**
 * FINORA - Personal Finance Intelligence
 * Front-end Interactive Controller & Chart Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Theme Management (Light / Dark)
    initThemeManager();

    // 2. Mobile Sidebar Navigation
    initMobileSidebar();

    // 3. Dynamic Transaction Form Controls
    initTransactionForm();

    // 4. Safe Delete Confirmations
    initDeleteConfirmations();

    // 5. Initialize Financial Charts (if canvas elements exist)
    initFinancialCharts();
});

/**
 * Handles Dark / Light theme toggling and local persistence
 */
function initThemeManager() {
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    const currentTheme = localStorage.getItem('finora_theme') || document.documentElement.getAttribute('data-theme') || 'light';

    applyTheme(currentTheme);

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('finora_theme', newTheme);

            // Notify server if user is logged in
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                fetch('/settings/preferences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfMeta.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `theme=${encodeURIComponent(newTheme)}&_csrf_token=${encodeURIComponent(csrfMeta.getAttribute('content'))}`
                }).catch(() => {});
            }
        });
    });
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const icons = document.querySelectorAll('.theme-toggle-btn i');
    icons.forEach(icon => {
        if (theme === 'dark') {
            icon.className = 'bi bi-sun-fill text-warning';
        } else {
            icon.className = 'bi bi-moon-stars text-secondary';
        }
    });

    // If charts exist, update their grid colors
    if (window.cashflowChartInstance) {
        updateChartTheme(window.cashflowChartInstance, theme);
    }
}

/**
 * Mobile sidebar drawer and backdrop
 */
function initMobileSidebar() {
    const toggleBtn = document.querySelector('.mobile-sidebar-toggle');
    const sidebar = document.querySelector('.app-sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');

    if (!toggleBtn || !sidebar || !backdrop) return;

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    });

    backdrop.addEventListener('click', () => {
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
    });
}

/**
 * Dynamic transaction type & category filtering
 */
function initTransactionForm() {
    const typeSelect = document.querySelector('#tx_type');
    const categoryContainer = document.querySelector('#tx_category_container');
    const destAccountContainer = document.querySelector('#tx_dest_account_container');
    const categorySelect = document.querySelector('#tx_category');

    if (!typeSelect) return;

    const updateVisibility = () => {
        const val = typeSelect.value;
        if (val === 'transfer') {
            if (categoryContainer) categoryContainer.style.display = 'none';
            if (destAccountContainer) destAccountContainer.style.display = 'block';
        } else {
            if (categoryContainer) categoryContainer.style.display = 'block';
            if (destAccountContainer) destAccountContainer.style.display = 'none';

            // Filter options in category select
            if (categorySelect) {
                const options = categorySelect.querySelectorAll('option[data-type]');
                options.forEach(opt => {
                    if (opt.getAttribute('data-type') === val) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            }
        }
    };

    typeSelect.addEventListener('change', updateVisibility);
    updateVisibility();
}

/**
 * Confirmation dialog for state-altering actions
 */
function initDeleteConfirmations() {
    const deleteForms = document.querySelectorAll('.form-delete-confirm');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const message = form.getAttribute('data-confirm-message') || 'Are you sure you want to delete this record? This action cannot be undone.';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Chart.js financial charts initialization
 */
function initFinancialCharts() {
    if (typeof Chart === 'undefined') return;

    // Set Chart defaults
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;

    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
    const textColor = isDark ? '#94A3B8' : '#64748B';

    // 1. Cashflow Trend Line Chart (Dashboard)
    const cashflowCanvas = document.getElementById('cashflowChart');
    if (cashflowCanvas && window.finoraCashflowData) {
        const labels = window.finoraCashflowData.map(d => d.label);
        const incomeData = window.finoraCashflowData.map(d => d.income);
        const expenseData = window.finoraCashflowData.map(d => d.expense);

        window.cashflowChartInstance = new Chart(cashflowCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Income (Rs.)',
                        data: incomeData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#FFFFFF',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Expenses (Rs.)',
                        data: expenseData,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#EF4444',
                        pointBorderColor: '#FFFFFF',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            color: textColor,
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += 'Rs. ' + Number(context.parsed.y).toLocaleString('en-PK', {minimumFractionDigits: 2});
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            callback: function(val) {
                                if (val >= 1000000) return 'Rs. ' + (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000) return 'Rs. ' + (val / 1000).toFixed(0) + 'k';
                                return 'Rs. ' + val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Expense Category Doughnut Chart (Dashboard & Reports)
    const categoryCanvas = document.getElementById('categoryDoughnutChart');
    if (categoryCanvas && window.finoraCategoryData) {
        const catLabels = window.finoraCategoryData.map(c => c.category_name);
        const catAmounts = window.finoraCategoryData.map(c => parseFloat(c.total_amount));
        const catColors = window.finoraCategoryData.map(c => c.category_color || '#3B82F6');

        if (catAmounts.length === 0) {
            // Handle empty state gracefully
            categoryCanvas.parentElement.innerHTML = `
                <div class="empty-state py-4">
                    <i class="bi bi-pie-chart text-secondary fs-1 mb-2 d-block"></i>
                    <p class="text-secondary small mb-0">No expense records found for this period.</p>
                </div>
            `;
        } else {
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{
                        data: catAmounts,
                        backgroundColor: catColors,
                        borderWidth: 2,
                        borderColor: isDark ? '#141C2B' : '#FFFFFF',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 14
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: Rs. ${Number(val).toLocaleString('en-PK', {minimumFractionDigits: 2})} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}

function updateChartTheme(chart, theme) {
    const isDark = theme === 'dark';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
    const textColor = isDark ? '#94A3B8' : '#64748B';

    if (chart.options.scales) {
        if (chart.options.scales.x) {
            chart.options.scales.x.grid.color = gridColor;
            chart.options.scales.x.ticks.color = textColor;
        }
        if (chart.options.scales.y) {
            chart.options.scales.y.grid.color = gridColor;
            chart.options.scales.y.ticks.color = textColor;
        }
    }
    if (chart.options.plugins && chart.options.plugins.legend) {
        chart.options.plugins.legend.labels.color = textColor;
    }
    chart.update();
}
