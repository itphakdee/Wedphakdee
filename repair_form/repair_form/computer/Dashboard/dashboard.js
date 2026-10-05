(function () {
    'use strict';

    var data = window.PDCDashboardData || {};

    function fallback(id) {
        var canvas = document.getElementById(id);
        var box = document.querySelector('[data-chart-fallback="' + id + '"]');
        if (canvas) canvas.style.display = 'none';
        if (box) box.style.display = 'block';
    }

    function formatMoney(value) {
        return Number(value || 0).toLocaleString('th-TH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function baseOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 650 },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 16,
                        color: '#607783',
                        font: { size: 10, family: 'Tahoma, Arial, sans-serif' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 52, 72, .96)',
                    titleFont: { size: 11 },
                    bodyFont: { size: 10 },
                    padding: 10,
                    cornerRadius: 8
                }
            }
        };
    }

    function createDoughnut(id, source, colors, cutout) {
        var canvas = document.getElementById(id);
        if (!canvas || !window.Chart || !source) {
            fallback(id);
            return;
        }

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: source.labels || [],
                datasets: [{
                    data: source.data || [],
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: Object.assign(baseOptions(), {
                cutout: cutout || '66%'
            })
        });
    }

    function createSystemChart() {
        var canvas = document.getElementById('systemChart');
        var source = data.systems;
        if (!canvas || !window.Chart || !source) {
            fallback('systemChart');
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: source.labels || [],
                datasets: [{
                    label: 'จำนวนงาน',
                    data: source.data || [],
                    backgroundColor: '#4f9691',
                    borderRadius: 7,
                    borderSkipped: false,
                    maxBarThickness: 34
                }]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { display: false },
                    tooltip: baseOptions().plugins.tooltip
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#758894', font: { size: 9 }, maxRotation: 25, minRotation: 0 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#82929c', font: { size: 9 } },
                        grid: { color: 'rgba(120,145,155,.12)' }
                    }
                }
            })
        });
    }

    function createTrendChart() {
        var canvas = document.getElementById('trendChart');
        if (!canvas || !window.Chart || !data.monthlyJobs || !data.monthlyCosts) {
            fallback('trendChart');
            return;
        }

        new Chart(canvas, {
            data: {
                labels: data.monthlyJobs.labels || [],
                datasets: [
                    {
                        type: 'bar',
                        label: 'จำนวนงาน',
                        data: data.monthlyJobs.data || [],
                        backgroundColor: 'rgba(40,111,159,.78)',
                        borderRadius: 7,
                        borderSkipped: false,
                        maxBarThickness: 30,
                        yAxisID: 'y'
                    },
                    {
                        type: 'line',
                        label: 'ค่าใช้จ่าย (บาท)',
                        data: data.monthlyCosts.data || [],
                        borderColor: '#0d7770',
                        backgroundColor: 'rgba(13,119,112,.10)',
                        fill: true,
                        tension: .34,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#0d7770',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: Object.assign(baseOptions(), {
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#758894', font: { size: 9 } }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        ticks: { precision: 0, color: '#758894', font: { size: 9 } },
                        grid: { color: 'rgba(120,145,155,.12)' },
                        title: { display: true, text: 'จำนวนงาน', color: '#81929c', font: { size: 9 } }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            color: '#758894',
                            font: { size: 9 },
                            callback: function (value) { return formatMoney(value); }
                        },
                        title: { display: true, text: 'ค่าใช้จ่าย (บาท)', color: '#81929c', font: { size: 9 } }
                    }
                }
            })
        });
    }

    function createTechnicianChart() {
        var canvas = document.getElementById('technicianChart');
        var source = data.technicians;
        if (!canvas || !window.Chart || !source) {
            fallback('technicianChart');
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: source.labels || [],
                datasets: [{
                    label: 'จำนวนงาน',
                    data: source.data || [],
                    backgroundColor: '#5caaa4',
                    borderRadius: 7,
                    borderSkipped: false,
                    maxBarThickness: 25
                }]
            },
            options: Object.assign(baseOptions(), {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: baseOptions().plugins.tooltip
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#758894', font: { size: 9 } },
                        grid: { color: 'rgba(120,145,155,.12)' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#617986', font: { size: 9 } }
                    }
                }
            })
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Chart) {
            ['statusChart','priorityChart','systemChart','trendChart','technicianChart'].forEach(fallback);
            return;
        }

        Chart.defaults.font.family = 'Tahoma, Arial, sans-serif';
        Chart.defaults.color = '#657b87';

        createDoughnut('statusChart', data.status, ['#d8a23a','#2a8d88','#2b986a','#9aa7ae'], '66%');
        createDoughnut('priorityChart', data.priority, ['#65a984','#d8a23a','#c75a57','#aab4ba'], '66%');
        createSystemChart();
        createTrendChart();
        createTechnicianChart();
    });
})();
