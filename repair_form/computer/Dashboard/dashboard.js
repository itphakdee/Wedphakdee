/* ==========================================
   Dashboard Repair System
   ========================================== */

// เก็บ Object ของ Chart
let statusChart = null;
let repairChart = null;
let monthChart = null;
let technicianChart = null;
let costChart = null;

// โหลดข้อมูลครั้งแรก
document.addEventListener("DOMContentLoaded", function () {
    loadStatusChart();
    loadRepairChart();
    loadMonthChart();
    loadTechnicianChart();
    loadCostChart();
    loadLatestTable();

    // Refresh ทุก 30 วินาที
    setInterval(function () {
        loadStatusChart();
        loadRepairChart();
        loadMonthChart();
        loadTechnicianChart();
        loadCostChart();
        loadLatestTable();
    }, 30000);
});


/* ==========================================
   สถานะงาน
========================================== */

function loadStatusChart() {

    fetch("api_status.php")
        .then(res => res.json())
        .then(data => {

            const ctx = document.getElementById("statusChart");

            if (!ctx) return;

            if (statusChart) {
                statusChart.destroy();
            }

            statusChart = new Chart(ctx, {

                type: "doughnut",

                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: [
                            "#ffc107",
                            "#28a745",
                            "#dc3545",
                            "#17a2b8",
                            "#6c757d"
                        ]
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom"
                        }
                    }
                }

            });

        })
        .catch(err => console.error("Status Chart :", err));

}


/* ==========================================
   ประเภทการซ่อม
========================================== */

function loadRepairChart() {

    fetch("api_repair_type.php")
        .then(res => res.json())
        .then(data => {

            const ctx = document.getElementById("repairChart");

            if (!ctx) return;

            if (repairChart) {
                repairChart.destroy();
            }

            repairChart = new Chart(ctx, {

                type: "pie",

                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: [
                            "#17a2b8",
                            "#dc3545",
                            "#ffc107",
                            "#28a745"
                        ]
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom"
                        }
                    }
                }

            });

        })
        .catch(err => console.error("Repair Chart :", err));

}


/* ==========================================
   จำนวนงานรายเดือน
========================================== */

function loadMonthChart() {

    fetch("api_month.php")
        .then(res => res.json())
        .then(data => {

            const ctx = document.getElementById("monthChart");

            if (!ctx) return;

            if (monthChart) {
                monthChart.destroy();
            }

            monthChart = new Chart(ctx, {

                type: "bar",

                data: {

                    labels: data.labels,

                    datasets: [{
                        label: "จำนวนงาน",

                        data: data.data,

                        backgroundColor: "#0051ff",

                        borderRadius: 10

                    }]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {
                            display: false
                        }

                    },

                    scales: {

                        y: {

                            beginAtZero: true

                        }

                    }

                }

            });

        })
        .catch(err => console.error("Month Chart :", err));

}


/* ==========================================
   Top Technician
========================================== */

function loadTechnicianChart() {

    fetch("api_technician.php")
        .then(res => res.json())
        .then(data => {

            const ctx = document.getElementById("technicianChart");

            if (!ctx) return;

            if (technicianChart) {
                technicianChart.destroy();
            }

            technicianChart = new Chart(ctx, {

                type: "bar",

                data: {

                    labels: data.labels,

                    datasets: [{

                        label: "จำนวนงาน",

                        data: data.data,

                        backgroundColor: "#20c997",

                        borderRadius: 10

                    }]

                },

                options: {

                    indexAxis: "y",

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {

                            display: false

                        }

                    },

                    scales: {

                        x: {

                            beginAtZero: true

                        }

                    }

                }

            });

        })
        .catch(err => console.error("Technician Chart :", err));

}


/* ==========================================
   ค่าใช้จ่ายรายเดือน
========================================== */

function loadCostChart() {

    fetch("api_cost.php")
        .then(res => res.json())
        .then(data => {

            const ctx = document.getElementById("costChart");

            if (!ctx) return;

            if (costChart) {
                costChart.destroy();
            }

            costChart = new Chart(ctx, {

                type: "line",

                data: {

                    labels: data.labels,

                    datasets: [{

                        label: "ค่าใช้จ่าย",

                        data: data.data,

                        borderColor: "#dc3545",

                        backgroundColor: "rgba(220,53,69,.20)",

                        fill: true,

                        tension: 0.4

                    }]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false

                }

            });

        })
        .catch(err => console.error("Cost Chart :", err));

}


/* ==========================================
   ตารางล่าสุด
========================================== */

function loadLatestTable() {

    fetch("api_latest.php")

        .then(res => res.text())

        .then(html => {

            const table = document.getElementById("latestTable");

            if (table) {
                table.innerHTML = html;
            }

        })

        .catch(err => console.error("Latest Table :", err));

}