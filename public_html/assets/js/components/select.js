$(function () {
    for (let select of $(".form-select")) {
        loadSelect2(select);
    }
})

window.loadSelect2 = function (element, defaults = {}) {
    element = $(element);
    let options = {
        language: language,
        width: "100%",
        theme: "bootstrap5",
        dropdownParent: $(element).closest(".modal-content, body"),
        ...defaults,
        ...element.data()
    };
    if ($(element).hasClass("autocomplete")) {
        let request = {
            token: $(element).data("autocomplete-token")
        };
        options.ajax = {
            url: root + "/ajax/autocompleteFilter",
            method: "post",
            dataType: 'json',
            data: function (params) {
                request.term = params.term;
                return request;
            },
            processResults: function (response) {
                if (response.data == null) {
                    response.data = [];
                }
                return {
                    results: request.term ? response.data :
                        element.find("option").map(function (i, el) { return { id: $(el).attr("value"), text: $(el).text() }; })
                };
            }
        };
    }
    element.select2(options);
    if (element.prop("multiple")) {
        element.on('change', function () {
            if (element.val().length == 0) {
                element.before(`<input type='hidden' name='${element.attr('name')}' id='empty_${element.attr('name')}'>`);
            } else {
                $(`input[id='empty_${element.attr('name')}']`).remove();
            }
        }).trigger('change');
    }
}
