$(document).ready(function () {

    //=========================
    // DataTable
    //=========================

    let table = $("#buildingTable").DataTable({

        responsive: true,

        autoWidth: false,

        pageLength: 15,

        ordering: true,

        searching: true,

        language: {

            url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/th.json"

        },

        dom: 'Bfrtip',

        buttons: [

            {

                extend: 'excelHtml5',

                text: '<i class="fa fa-file-excel"></i> Export Excel',

                className: 'btn btn-success'

            },

            {

                extend: 'print',

                text: '<i class="fa fa-print"></i> พิมพ์',

                className: 'btn btn-primary'

            }

        ]

    });

    //=========================
    // Search
    //=========================

    $("#searchInput").keyup(function () {

        table.search($(this).val()).draw();

    });

});
//=========================
// Budget Filter
//=========================

$("#budgetFilter").change(function () {

    table.column(5)

        .search($(this).val())

        .draw();

});
//=========================
// Budget Filter
//=========================

$("#budgetFilter").change(function () {

    table.column(5)

        .search($(this).val())

        .draw();

});
$(document).on("click",".btn-delete",function(e){

if(!confirm("ยืนยันการลบข้อมูล ?")){

e.preventDefault();

}

});
$(window).on("load",function(){

$(".loader").fadeOut();

});
function toast(msg){

let html=`

<div class="toast show position-fixed bottom-0 end-0 m-4">

<div class="toast-header">

<strong class="me-auto">

แจ้งเตือน

</strong>

</div>

<div class="toast-body">

${msg}

</div>

</div>

`;

$("body").append(html);

setTimeout(function(){

$(".toast").fadeOut();

},2500);

}