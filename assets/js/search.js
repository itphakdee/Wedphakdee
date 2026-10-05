const searchInput = document.getElementById("searchInput");
const tableBody = document.getElementById("tableBody");
const pagination = document.querySelector(".pagination");

let timer;

searchInput.addEventListener("keyup", function () {

    clearTimeout(timer);

    timer = setTimeout(() => {

        const keyword = this.value.trim();

        fetch("search_property.php?keyword=" + encodeURIComponent(keyword))
            .then(res => res.text())
            .then(html => {

                tableBody.innerHTML = html;

                if (pagination) {
                    pagination.style.display = keyword === "" ? "flex" : "none";
                }

            });

    }, 300);

});