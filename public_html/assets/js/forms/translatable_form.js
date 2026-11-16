$(function () {
    $('form[data-translation-editor]').each(function () {
        const form = this;
        let dirty = form.hasAttribute('data-translation-dirty');
        let confirming = false;
        let leaving = false;
        $(form).on('input change', ':input', function () { dirty = true; });
        $(form).on('submit', function () {
            if (window.tinymce) window.tinymce.triggerSave();
            dirty = false;
        });
        $(form).on('click', '.trans-copy-btn', function (event) {
            event.preventDefault();
            const row = $(this).closest('.trans-field-row');
            const rawScript = row.find('script.trans-source-raw').first();
            if (!rawScript.length) return;
            let sourceValue = '';
            try {
                sourceValue = JSON.parse(rawScript.text());
            } catch (e) {
                return;
            }
            const targetInput = row.find(':input[name^="translations["]').first();
            if (!targetInput.length) return;
            const inputId = targetInput.attr('id');
            if (window.tinymce && inputId && tinymce.get(inputId)) {
                tinymce.get(inputId).setContent(sourceValue);
                tinymce.get(inputId).fire('change');
            } else {
                targetInput.val(sourceValue).trigger('input').trigger('change');
            }
            dirty = true;
        });
        function watchEditor(editor) {
            if (editor.targetElm.closest('form') === form) {
                editor.on('change input', function () { dirty = true; });
            }
        }
        if (window.tinymce) {
            tinymce.editors.forEach(watchEditor);
            tinymce.on('AddEditor', function (event) { watchEditor(event.editor); });
        }
        function trackCollections(records) {
            for (const record of records) {
                for (const node of [...record.addedNodes, ...record.removedNodes]) {
                    if (node.nodeType === 1 && !node.closest('.tox, .mce-container') &&
                        (node.matches(':is(input, select, textarea)[name]') ||
                            node.querySelector(':is(input, select, textarea)[name]'))) dirty = true;
                }
            }
        }
        const observer = new MutationObserver(trackCollections);
        form.querySelectorAll('.collapsible-widget-group').forEach(function (group) {
            observer.observe(group, {childList: true, subtree: true});
        });
        $(form).on('dragend', '.collapsible-widget-group', function () { dirty = true; });
        $(form).closest('.entity-translation-editor').on('click', '.trans-lang-link', function (event) {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.which > 1 ||
                this.target === '_blank') return;
            if (this.getAttribute('aria-current') === 'page') {
                event.preventDefault();
                return;
            }
            trackCollections(observer.takeRecords());
            if (!dirty) return;
            event.preventDefault();
            if (confirming) return;
            confirming = true;
            const destination = this.href;
            swal.fire({
                title: _t('translation_unsaved_title'),
                text: _t('translation_unsaved_message'),
                icon: 'warning',
                showCancelButton: true,
                focusCancel: true,
                buttonsStyling: false,
                confirmButtonText: _t('translation_discard_switch'),
                cancelButtonText: _t('translation_stay'),
                customClass: {confirmButton: 'btn btn-light-danger', cancelButton: 'btn btn-primary'}
            }).then(function (result) {
                confirming = false;
                if (result.isConfirmed) {
                    leaving = true;
                    window.location.assign(destination);
                }
            });
        });
        window.addEventListener('beforeunload', function (event) {
            trackCollections(observer.takeRecords());
            if (dirty && !leaving) { event.preventDefault(); event.returnValue = ''; }
        });
    });
});
