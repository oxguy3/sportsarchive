import Chart from 'chart.js/auto';

var jsData = JSON.parse(document.getElementById('jsData').textContent);

new Chart(
    document.getElementById('chart_docCategoryCounts'),
    {
        type: 'pie',
        data: {
            labels: jsData.docCategoryCounts.map(row => row.category),
            datasets: [
                {
                    label: 'Count',
                    data: jsData.docCategoryCounts.map(row => row.count)
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
            labels: jsData.docSportCounts.map(row => row.sport),
            datasets: [
                {
                    label: 'Count',
                    data: jsData.docSportCounts.map(row => row.count)
                }
            ]
        },
        responsive: true,
        maintainAspectRatio: true
    }
);