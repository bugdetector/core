$(document).on("click", ".trans-lang-btn", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const selectedLang = $btn.attr("data-lang");
    if (!selectedLang) {
        return;
    }

    const $form = $btn.closest("form");
    const $nav = $btn.closest(".trans-lang-nav");

    $nav.find(".trans-lang-btn")
        .removeClass("btn-primary active")
        .addClass("btn-light-secondary text-gray-700");

    $btn.removeClass("btn-light-secondary text-gray-700")
        .addClass("btn-primary active");

    $form.find("[data-trans-lang-wrap]").each(function () {
        const $wrap = $(this);
        if ($wrap.attr("data-trans-lang-wrap") === selectedLang) {
            $wrap.show();
        } else {
            $wrap.hide();
        }
    });
});

$(document).on("submit", "form", function () {
    if (window.tinymce && typeof window.tinymce.triggerSave === "function") {
        window.tinymce.triggerSave();
    }
});
