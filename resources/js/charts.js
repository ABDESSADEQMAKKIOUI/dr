import Chart from 'chart.js/auto';

// Chart Initialization Helper
window.initChart = (canvasId, type, data, options = {}) => {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...options
        }
    });
};

// Example usage:
// initChart('revenueChart', 'line', { ... });
