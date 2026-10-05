/* =========================================================
   structure_add.js
   หน้าเพิ่มข้อมูล อาคาร/สิ่งปลูกสร้าง
   Path:
   repair_form/structures/structure_add.js
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    // =====================================================
    // Elements
    // =====================================================

    const form = document.getElementById("buildingForm");

    const imageInput = document.getElementById("building_image");
    const imageInputMobile = document.getElementById("building_image_mobile");

    const preview = document.getElementById("buildingPreview");
    const placeholder = document.getElementById("previewPlaceholder");

    const fileName = document.getElementById("imageFileName");
    const fileNameRight = document.getElementById("imageFileNameRight");

    const saveButton = document.getElementById("saveButton");

    const constructionDate =
        document.getElementById("construction_date");

    const usefulLife =
        document.getElementById("useful_life");

    const endDate =
        document.getElementById("end_date");


    // =====================================================
    // ตรวจสอบว่า SweetAlert2 มีหรือไม่
    // =====================================================

    function showAlert(options) {

        if (typeof Swal !== "undefined") {

            return Swal.fire(options);

        }

        alert(
            options.text ||
            options.title ||
            "เกิดข้อผิดพลาด"
        );

        return Promise.resolve({
            isConfirmed: true
        });
    }


    // =====================================================
    // Preview รูปภาพ
    // =====================================================

    function previewImage(file) {

        if (!file) {
            return;
        }


        // ประเภทไฟล์ที่อนุญาต

        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];


        // ตรวจสอบประเภทไฟล์

        if (!allowedTypes.includes(file.type)) {

            showAlert({
                icon: "error",
                title: "ไฟล์ไม่ถูกต้อง",
                text: "กรุณาเลือกไฟล์ JPG, JPEG, PNG หรือ WEBP",
                confirmButtonText: "ตกลง"
            });

            return;
        }


        // จำกัดขนาด 5 MB

        const maxSize = 5 * 1024 * 1024;

        if (file.size > maxSize) {

            showAlert({
                icon: "error",
                title: "ไฟล์มีขนาดใหญ่เกินไป",
                text: "รูปภาพต้องมีขนาดไม่เกิน 5 MB",
                confirmButtonText: "ตกลง"
            });

            return;
        }


        // อ่านรูปภาพ

        const reader = new FileReader();


        reader.onload = function (event) {

            if (preview) {

                preview.src = event.target.result;

                preview.style.display = "block";

            }


            if (placeholder) {

                placeholder.style.display = "none";

            }

        };


        reader.onerror = function () {

            showAlert({
                icon: "error",
                title: "ไม่สามารถอ่านรูปภาพได้",
                text: "กรุณาลองเลือกไฟล์ใหม่",
                confirmButtonText: "ตกลง"
            });

        };


        reader.readAsDataURL(file);


        // แสดงชื่อไฟล์

        if (fileName) {

            fileName.textContent = file.name;

        }


        if (fileNameRight) {

            fileNameRight.textContent = file.name;

        }

    }


    // =====================================================
    // เลือกรูปภาพหลัก
    // =====================================================

    if (imageInput) {

        imageInput.addEventListener("change", function () {

            if (!this.files || this.files.length === 0) {

                return;

            }

            previewImage(this.files[0]);

        });

    }


    // =====================================================
    // เลือกรูปภาพจากช่องด้านขวา
    // =====================================================

    if (imageInputMobile) {

        imageInputMobile.addEventListener("change", function () {

            if (!this.files || this.files.length === 0) {

                return;

            }

            previewImage(this.files[0]);

        });

    }


    // =====================================================
    // คำนวณวันสิ้นสุดจาก
    // วันที่ก่อสร้าง + อายุการใช้งาน
    // =====================================================

    function calculateEndDate() {

        if (!constructionDate) {
            return;
        }

        if (!usefulLife) {
            return;
        }

        if (!endDate) {
            return;
        }


        const startDate =
            constructionDate.value;

        const years =
            parseInt(usefulLife.value, 10);


        if (!startDate) {

            endDate.value = "";

            return;

        }


        if (isNaN(years) || years <= 0) {

            endDate.value = "";

            return;

        }


        const date =
            new Date(startDate + "T00:00:00");


        if (isNaN(date.getTime())) {

            endDate.value = "";

            return;

        }


        date.setFullYear(
            date.getFullYear() + years
        );


        const year =
            date.getFullYear();


        const month =
            String(
                date.getMonth() + 1
            ).padStart(2, "0");


        const day =
            String(
                date.getDate()
            ).padStart(2, "0");


        endDate.value =
            `${year}-${month}-${day}`;

    }


    // =====================================================
    // Event วันที่ก่อสร้าง
    // =====================================================

    if (constructionDate) {

        constructionDate.addEventListener(
            "change",
            calculateEndDate
        );

    }


    // =====================================================
    // Event อายุการใช้งาน
    // =====================================================

    if (usefulLife) {

        usefulLife.addEventListener(
            "input",
            calculateEndDate
        );

    }


    // =====================================================
    // ตรวจสอบจำนวนเงิน
    // =====================================================

    const amountInput =
        document.getElementById("amount");


    if (amountInput) {

        amountInput.addEventListener(
            "input",
            function () {

                let value = this.value;


                if (value < 0) {

                    this.value = 0;

                }

            }
        );

    }


    // =====================================================
    // Submit Form
    // =====================================================

    if (form) {

        form.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                // ---------------------------------------------
                // ช่องชื่ออาคาร
                // ---------------------------------------------

                const name =
                    form.querySelector(
                        '[name="building_name"]'
                    );


                // ---------------------------------------------
                // ประเภทอาคาร
                // ---------------------------------------------

                const type =
                    form.querySelector(
                        '[name="building_type"]'
                    );


                // ---------------------------------------------
                // งบประมาณ
                // ---------------------------------------------

                const budget =
                    form.querySelector(
                        '[name="budget_type"]'
                    );


                // ---------------------------------------------
                // ตรวจชื่ออาคาร
                // ---------------------------------------------

                if (
                    !name ||
                    !name.value.trim()
                ) {

                    showAlert({
                        icon: "warning",
                        title: "กรุณาระบุชื่ออาคาร",
                        text: "กรุณากรอกชื่ออาคารก่อนบันทึกข้อมูล",
                        confirmButtonText: "ตกลง"
                    });

                    if (name) {

                        name.focus();

                    }

                    return;

                }


                // ---------------------------------------------
                // ตรวจประเภท
                // ---------------------------------------------

                if (
                    !type ||
                    !type.value
                ) {

                    showAlert({
                        icon: "warning",
                        title: "กรุณาเลือกประเภท",
                        text: "กรุณาเลือกประเภทสิ่งปลูกสร้าง",
                        confirmButtonText: "ตกลง"
                    });

                    if (type) {

                        type.focus();

                    }

                    return;

                }


                // ---------------------------------------------
                // ตรวจงบประมาณ
                // ---------------------------------------------

                if (
                    !budget ||
                    !budget.value
                ) {

                    showAlert({
                        icon: "warning",
                        title: "กรุณาเลือกงบประมาณ",
                        text: "กรุณาเลือกประเภทงบประมาณ",
                        confirmButtonText: "ตกลง"
                    });

                    if (budget) {

                        budget.focus();

                    }

                    return;

                }


                // =================================================
                // ยืนยันการบันทึก
                // =================================================

                showAlert({

                    icon: "question",

                    title: "ยืนยันการบันทึก",

                    text:
                        "ต้องการบันทึกข้อมูลอาคารนี้หรือไม่?",

                    showCancelButton: true,

                    confirmButtonText:
                        "บันทึกข้อมูล",

                    cancelButtonText:
                        "ยกเลิก",

                    confirmButtonColor:
                        "#198754",

                    cancelButtonColor:
                        "#6c757d"

                }).then(function (result) {


                    // ผู้ใช้กดยกเลิก

                    if (!result.isConfirmed) {

                        return;

                    }


                    // =================================================
                    // ป้องกันกดบันทึกซ้ำ
                    // =================================================

                    if (saveButton) {

                        saveButton.disabled = true;


                        saveButton.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin"></i> กำลังบันทึก...';

                    }


                    // =================================================
                    // ส่ง Form
                    // =================================================

                    form.submit();

                });

            }
        );

    }


    // =====================================================
    // ป้องกันการส่งฟอร์มด้วย Enter โดยไม่ตั้งใจ
    // =====================================================

    if (form) {

        form.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Enter" &&
                    event.target.tagName !== "TEXTAREA"
                ) {

                    event.preventDefault();

                }

            }
        );

    }


    // =====================================================
    // Format จำนวนเงิน
    // =====================================================

    if (amountInput) {

        amountInput.addEventListener(
            "blur",
            function () {

                if (
                    this.value !== "" &&
                    !isNaN(this.value)
                ) {

                    this.value =
                        parseFloat(this.value)
                            .toFixed(2);

                }

            }
        );

    }


    // =====================================================
    // Console สำหรับตรวจสอบระบบ
    // =====================================================

    console.log(
        "structure_add.js loaded successfully"
    );

});