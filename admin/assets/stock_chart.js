document.addEventListener('DOMContentLoaded', () => {
    loadStockOverview();
});

function loadStockOverview() {
    fetch('get_stock_overview.php')
        .then(response => response.json())
        .then(result => {
            const ctxStock = document.getElementById('stockChart').getContext('2d');

            new Chart(ctxStock, {
                type: 'bar', // vertical bars by default
                data: {
                    labels: result.labels,
                    datasets: [{
                        label: 'Stock Balance',
                        data: result.data,
                        backgroundColor: result.colors,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Balance: ${context.raw} pcs`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(200,200,200,0.15)' },
                            title: { display: true, text: 'Items', font: { size: 14 } }
                        },
                        y: {
                            grid: { color: 'rgba(200,200,200,0.15)' },
                            title: { display: true, text: 'Stock Balance', font: { size: 14 } }
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Stock data error:', err));
}
