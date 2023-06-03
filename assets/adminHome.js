import Chart from 'chart.js/auto';


new Chart(
    document.getElementById('chart_docCategoryCounts'),
    {
        type: 'pie',
        data: {
            labels: docCategoryCounts.map(row => row.category),
            datasets: [
                {
                    label: 'Count',
                    data: docCategoryCounts.map(row => row.count)
                }
            ]
        },
        responsive: true,
        maintainAspectRatio: true
    }
);
new Chart(
    document.getElementById('chart_docSportCounts'),
    {
        type: 'pie',
        data: {
            labels: docSportCounts.map(row => row.sport),
            datasets: [
                {
                    label: 'Count',
                    data: docSportCounts.map(row => row.count)
                }
            ]
        },
        responsive: true,
        maintainAspectRatio: true
    }
);