// app.js - handles chart rendering
function renderLineChart(ctx, labels, data) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Balance over 30 days',
                data: data,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78,115,223,0.08)',
                fill: true
            }]
        },
        options: {responsive:true}
    });
}

function renderPieChart(ctx, labels, data, colors) {
    new Chart(ctx, {
        type: 'pie',
        data: {labels: labels, datasets:[{data: data, backgroundColor: colors}]},
        options: {responsive:true}
    });
}
