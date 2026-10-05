document.addEventListener("DOMContentLoaded", function () {

    // 1. Repair Type (Doughnut)
    const repairCanvas = document.getElementById("repairTypeChart");
    if (repairCanvas) {
        new Chart(repairCanvas, {
            type: "doughnut",
            data: {
                labels: window.repairLabel || [],
                datasets: [{
                    data: window.repairData || [],
                    backgroundColor: ["#3366CC", "#4CAF50", "#FF9800", "#F44336"]
                }]
            }
        });
    }

    // 2. Department / Status (คุณยังไม่มี data ชัดเจน)
    const departmentCanvas = document.getElementById("departmentChart");
    if (departmentCanvas) {
        new Chart(departmentCanvas, {
            type: "bar",
            data: {
                labels: window.statusLabel || [],
                datasets: [{
                    label: "จำนวนงาน",
                    data: window.statusData || [], // <- ถ้ายังไม่มีจะเป็น []
                    backgroundColor: "#4CAF50"
                }]
            }
        });
    }

    // 3. Monthly Repair (Line Chart)
    const monthlyRepairCanvas = document.getElementById("monthlyRepairChart");
    if (monthlyRepairCanvas) {
        new Chart(monthlyRepairCanvas, {
            type: "line",
            data: {
                labels: window.monthlyRepairLabel || [],
                datasets: [{
                    label: "งานซ่อมรายเดือน",
                    data: window.monthlyRepairData || [],
                    borderColor: "#3366CC",
                    backgroundColor: "rgba(51,102,204,0.2)",
                    fill: true,
                    tension: 0.3
                }]
            }
        });
    }

    // 4. PM Chart (Line Chart)
    const pmCanvas = document.getElementById("pmChart");
    if (pmCanvas) {
        new Chart(pmCanvas, {
            type: "line",
            data: {
                labels: window.pmLabel || [],
                datasets: [{
                    label: "งาน PM รายเดือน",
                    data: window.pmData || [],
                    borderColor: "#FF9800",
                    backgroundColor: "rgba(255,152,0,0.2)",
                    fill: true,
                    tension: 0.3
                }]
            }
        });
    }

    // 5. Status Chart (Doughnut)
    const statusCanvas = document.getElementById("statusChart");
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: "doughnut",
            data: {
                labels: window.statusLabel || [],
                datasets: [{
                    data: window.statusData || [], // <- ถ้ายังไม่มีต้องเพิ่มฝั่ง PHP
                    backgroundColor: ["#F44336", "#FF9800", "#4CAF50", "#2196F3"]
                }]
            }
        });
    }

    // 6. Inhouse vs Outside (Pie)
    const inhouseCanvas = document.getElementById("inhouseChart");
    if (inhouseCanvas) {
        new Chart(inhouseCanvas, {
            type: "pie",
            data: {
                labels: window.inhouseLabel || [],
                datasets: [{
                    data: window.inhouseData || [],
                    backgroundColor: ["#3366CC", "#FF9800"]
                }]
            }
        });
    }

});