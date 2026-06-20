<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalTitle">แจ้งเตือน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageModalBody">
                ข้อความแจ้งเตือน
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<style>
    #logoutModal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    #logoutModal.active {
        display: flex;
    }

    #logoutModal .modal-box {
        width: min(500px, calc(100% - 40px));
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.25);
    }

    #logoutModal .modal-header {
        padding: 18px 20px;
        background: #198754;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #logoutModal .modal-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
    }

    #logoutModal .modal-close {
        background: transparent;
        border: none;
        color: #fff;
        font-size: 24px;
        cursor: pointer;
    }

    #logoutModal .modal-body {
        padding: 20px;
        color: #333;
        font-size: 15px;
        line-height: 1.6;
    }

    #logoutModal .modal-footer {
        padding: 16px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    #logoutModal .btn-cancel,
    #logoutModal .btn-confirm {
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        cursor: pointer;
        font-weight: 600;
    }

    #logoutModal .btn-cancel {
        background: #6c757d;
        color: #fff;
    }

    #logoutModal .btn-confirm {
        background: #dc3545;
        color: #fff;
    }
</style>

<div id="logoutModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">ยืนยันออกจากระบบ</div>
            <div><button class="modal-close" type="button" id="logoutModalClose">&times;</button></div>
        </div>
        <div class="modal-body">คุณต้องการออกจากระบบหรือไม่?</div>
        <div class="modal-footer">
            <button class="btn-cancel" type="button" id="logoutModalCancel">ยกเลิก</button>
            <button class="btn-confirm" type="button" id="logoutModalConfirm">ออกจากระบบ</button>
        </div>
    </div>
</div>

<script>
    function showMessageDialog(message, title = 'แจ้งเตือน') {
        document.getElementById('messageModalTitle').innerText = title;
        document.getElementById('messageModalBody').innerText = message;

        var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var logoutModal = document.getElementById('logoutModal');
        var logoutHref = '';
        var closeBtn = document.getElementById('logoutModalClose');
        var cancelBtn = document.getElementById('logoutModalCancel');
        var confirmBtn = document.getElementById('logoutModalConfirm');

        document.querySelectorAll('.logout-link').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                logoutHref = this.href;
                logoutModal.classList.add('active');
            });
        });

        var hideModal = function() {
            logoutModal.classList.remove('active');
        };

        closeBtn.addEventListener('click', hideModal);
        cancelBtn.addEventListener('click', hideModal);
        logoutModal.addEventListener('click', function(event) {
            if (event.target === logoutModal) {
                hideModal();
            }
        });
        confirmBtn.addEventListener('click', function() {
            if (logoutHref) {
                window.location.href = logoutHref;
            }
        });
    });
</script>
