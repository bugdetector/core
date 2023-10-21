$(document).on("click", ".rowdelete", function (e) {
    e.preventDefault();
    let button = $(this);
    let table_name = $(this).data("table");
    let id = $(this).data("id");
    alert({
        message: _t("record_remove_accept"),
        title: _t("warning"),
        callback: function () {
            $.ajax({
                url: `${root}/admin/ajax/delete`,
                method: "post",
                data: { table: table_name, id: id },
                success: function () {
                    button.parents("tr").fadeOut(1000);
                }
            })
        }
    })
}).on("click", ".entityrowdelete", function (e) {
    e.preventDefault();
    let button = $(this);
    alert({
        message: _t("record_remove_accept_entity", [
            button.data("entity-name")
        ]),
        okLabel: _t("yes"),
        callback: function () {
            $.ajax({
                url: root + "/ajax/entityDelete",
                method: "post",
                data: { key: button.data("key") },
                success: function () {
                    button.parents("tr").fadeOut(1000);
                }
            })
        }
    });
}).on("click", "input[type='reset'],button[type='reset']", function (e) {
    e.preventDefault();
    $(this).parents("form").find("input:not([type='submit']):not([type='reset']),textarea").val("");
    loadSelect2($(this).parents("form").find("select").val("NULL"));
    $(this).parents("form").find("input[type='checkbox']").prop("checked", false).trigger("change");
})

$(document).on("keydown", ".search-form-asynch", function(e){
    if(e.which == 13){ // Enter key
        e.preventDefault();
        return;
    }
}).on("input", ".search-form-asynch", function (e) {
    let form = $(this).closest(".search-form");
    form.find("input:not([type='submit']):not([type='reset']):not(.search-form-asynch),textarea").val("");
    if (typeof window.loadSelect2 === "function") {
        loadSelect2(form.find("select").val("NULL"));
    }
    form.find("input[type='checkbox']").prop("checked", false).trigger("change");
    asynchLoad(form);
}).on("submit", '.search-form', function () {
    let form = $(this);
    form.find(".search-form-asynch").val("");
    asynchLoad(form);
    return false;
}).on("click", ".search-form #pagination .page-link", function (e) {
    e.preventDefault();
    let form = $(this).closest(".search-form");
    let searchParams = new URLSearchParams(location.search);
    searchParams.set("page", $(this).data("page"));
    asynchLoad(form, searchParams, true, 0);
    $('html,body').animate({
        scrollTop: form.offset().top - 200
    }, 500);
}).on('click', '.search-form .order-results', function (e) {
    e.preventDefault();
    let form = $(this).closest(".search-form");
    let searchParams = new URLSearchParams($(this).data('order'));
    asynchLoad(form, searchParams, true, 0);
});

window.addEventListener("popstate", (event) => {
    $('.search-form').each(function (i, el) {
        let form = $(el);
        form.find("input:not([type='submit']):not([type='reset']),textarea").val("");
        loadSelect2(form.find("select").val("NULL"));
        form.find("input[type='checkbox']").prop("checked", false).trigger("change");
        let searchParams = new URLSearchParams(event.currentTarget.location.search);
        for (key of searchParams.keys()) {
            form.find(`[name='${key}']`).val(searchParams.get(key));
        }
        asynchLoad(form, new URLSearchParams(event.currentTarget.location.search), false, 0);
    })
});


var timeout = null;
function asynchLoad(form, searchParams = null, pushState = true, timeoutDuration = 500) {
    if (timeout) {
        clearTimeout(timeout);
    }
    form.find(".search-form-asynch-loading").removeClass('d-none');
    timeout = setTimeout(function () {
        filterSearchForm(form, searchParams ?? new URLSearchParams(form.serialize()), pushState, function (response) {
            if (response.data.status) {
                let resultItems = $(response.data.render).find(".result-viewer");
                let pagination = $(response.data.render).find(".result-pagination");
                form.find(".result-viewer").replaceWith(resultItems);
                form.find(".result-pagination").replaceWith(pagination);
                initComponents(resultItems);
                form.trigger("autoload-page", [resultItems]);
            } else {
                form.find(".result-viewer").html(response.data.render);
                form.find(".result-pagination").html("");
            }
            form.find(".search-form-asynch-loading").addClass('d-none');
        }, pushState)
    }, timeoutDuration);
}

function filterSearchForm(form, searchParams, pushState = true, callback = () => { }) {
    let keys = [];
    for (key of searchParams.keys()) {
        keys.push(key);
    }
    for (key of keys) {
        if (!searchParams.get(key)) {
            searchParams.delete(key);
        }
    }
    form = $(form);
    let url = root + "/search/filter" + (Array.from(searchParams).length ? "?" + searchParams : "");
    fetch(url, {
        method: "post",
        body: JSON.stringify({
            token: form.data("token")
        })
    }
    )
        .then((response) => response.json())
        .then((response) => {
            let data = response.data;
            if(data.status){
                let newUrl = window.location.pathname + (Array.from(searchParams).length ? "?" + searchParams : "");
                if (pushState) {
                    if (window.history.pushState) {
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    } else {
                        window.history.replaceState({ path: newUrl }, '', newUrl);
                    }
                }
            }
            callback(response);
        })
}

$(function () {
    var ajaxActive = false;
    var loadMoreIntersectionObserver = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting === true) {
            if (!ajaxActive) {
                ajaxActive = true;
                let target = $(entries[0].target);
                let form = target.closest(".search-form");
                let searchParams = new URLSearchParams(window.location.search);
                let nextPage = target.data("page");
                searchParams.set("page", nextPage);
                filterSearchForm(form, searchParams, true, function (response) {
                    if (response.data.status) {
                        let resultItems = $(response.data.render).find(".result-viewer");
                        form.find(".result-viewer:last").after(resultItems);
                        target.addClass("load-more-section invisible")
                            .data("page", nextPage + 1);
                        ajaxActive = false;
                        initComponents(resultItems);
                        form.trigger("autoload-page", [resultItems, nextPage]);
                    } else {
                        target.remove();
                    }
                });
                target.removeClass("load-more-section invisible");
            }
        }
    }, { threshold: [1] });

    let loadMoreSection = document.querySelector(".load-more-section");
    if (loadMoreSection) {
        loadMoreIntersectionObserver.observe(loadMoreSection);
    }
})

function initComponents(resultItems) {
    var tooltipTriggerList = resultItems.find('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.each(function (i, tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    var popoverTriggerList = resultItems.find('[data-bs-toggle="popover"]');
    popoverTriggerList.each(function (i, popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl);
    });
    if (typeof window.loadSelect2 === "function") {
        resultItems.find("select").each(function (i, el) {
            loadSelect2(el);
        });
    }
    if (typeof window.loadTimeInput === "function") {
        loadTimeInput();
        loadDateInput();
        loadDateTimeInput();
    }
    if (typeof window.loadCheckbox === "function") {
        resultItems.find("input[type='checkbox']").each(function (i, element) {
            loadCheckbox(element);
        });
    }
    if (typeof window.loadHtmlEditor === "function") {
        resultItems.find('.html-editor').each(function (i, el) {
            loadHtmlEditor(el);
        })
    }

}