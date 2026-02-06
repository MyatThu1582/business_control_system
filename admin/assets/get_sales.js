// --- Update current time ---
function updateTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute:'2-digit', second:'2-digit' });
    const timeEl = document.querySelector('#current-time .time-text');
    if(timeEl) timeEl.textContent = timeStr;
}
updateTime();
setInterval(updateTime, 1000);

// --- Chart.js setup ---
const ctx = document.getElementById('salesChart').getContext('2d');

// Create gradient for the line fill
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(78, 115, 223, 0.4)');
gradient.addColorStop(1, 'rgba(78, 115, 223, 0)');

// Initialize empty chart
let salesChart = new Chart(ctx, {
    type: 'line',
    data: { labels: [], datasets: [{
        label: 'Total Sales (MMK)',
        data: [],
        backgroundColor: gradient,
        borderColor: 'rgba(78, 115, 223, 1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 5,
        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
        pointHoverRadius: 7,
        pointHoverBackgroundColor: 'rgba(78, 115, 223, 0.9)'
    }]},
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'top', labels: { font: { size: 14 }, color: '#333' }},
            tooltip: { mode: 'index', intersect: false, backgroundColor: 'rgba(0,0,0,0.7)', titleFont: { size: 14 }, bodyFont: { size: 13 }}
        },
        interaction: { mode: 'nearest', intersect: false },
        scales: {
            x: { display: true, title: { display: true, text: 'Date', font: { size: 14 } }, grid: { display: false } },
            y: { display: true, title: { display: true, text: 'Sales Amount (MMK)', font: { size: 14 } }, beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
        }
    }
});

// --- AJAX function to fetch sales data dynamically ---
function filterSales(period, btn) {
    // Remove 'active' from all buttons
    document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));

    // Add 'active' to the clicked button
    if(btn) btn.classList.add('active');

    // Fetch sales data via AJAX
    fetch('get_sales.php?period=' + period)
        .then(res => res.json())
        .then(data => {
            salesChart.data.labels = data.labels;
            salesChart.data.datasets[0].data = data.data;
            salesChart.update();
        })
        .catch(err => console.error(err));
}


// Load default monthly data on page load
filterSales('monthly');
