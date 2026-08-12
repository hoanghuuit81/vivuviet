import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Underline,
    Link,
    List,
    Heading,
    BlockQuote,
    Image,
    ImageBlock,
    ImageInline,
    ImageUpload,
    ImageInsert,
    AutoImage,
    SimpleUploadAdapter,
    PasteFromOffice
} from 'ckeditor5';

document.querySelectorAll('.rich-editor').forEach((element) => {
    ClassicEditor.create(element, {
        licenseKey: 'GPL',
        plugins: [
            Essentials, Paragraph, Bold, Italic, Underline, Link, List, Heading, BlockQuote,
            Image, ImageBlock, ImageInline, ImageUpload, ImageInsert, AutoImage,
            SimpleUploadAdapter, PasteFromOffice
        ],
        toolbar: [
            'undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', 'link', '|',
            'bulletedList', 'numberedList', '|', 'blockQuote', 'insertImage'
        ],
        simpleUpload: {
            uploadUrl: (() => {
                const url = new URL(window.location.href);
                url.searchParams.set('action', 'upload_editor_image');
                return url.href;
            })(),
            headers: {
                'X-CSRF-Token': document.querySelector('input[name="csrf"]')?.value || ''
            }
        },
        placeholder: element.getAttribute('placeholder') || 'Viết nội dung giới thiệu…'
    }).then((editor) => {
        const form = element.closest('form');
        const editorElement = editor.ui.getEditableElement();

        // CKEditor hides the source textarea. Validate the visible editor instead.
        element.removeAttribute('required');
        element.removeAttribute('minlength');

        form?.addEventListener('submit', () => {
            element.value = editor.getData();
        });

        editor.model.document.on('change:data', () => {
            element.value = editor.getData();
            form?.dispatchEvent(new CustomEvent('rich-editor-change', { detail: { element, editorElement } }));
        });
    }).catch((error) => console.error('Không thể khởi tạo CKEditor:', error));
});

const fieldMessage = (field) => {
    if (field.validity.valueMissing) return 'Trường này không được để trống.';
    if (field.validity.tooShort) return `Vui lòng nhập ít nhất ${field.minLength} ký tự.`;
    if (field.validity.typeMismatch) return field.type === 'url' ? 'Vui lòng nhập một đường dẫn hợp lệ.' : 'Vui lòng nhập đúng định dạng.';
    return 'Thông tin này chưa hợp lệ.';
};

const setFieldError = (field, message) => {
    const container = field.closest('label') || field.parentElement;
    if (!container) return;
    let error = container.querySelector(':scope > .field-error');
    if (!message) {
        error?.remove();
        field.removeAttribute('aria-invalid');
        return;
    }
    if (!error) {
        error = document.createElement('span');
        error.className = 'field-error';
        error.setAttribute('role', 'alert');
        container.append(error);
    }
    error.textContent = message;
    field.setAttribute('aria-invalid', 'true');
};

document.querySelectorAll('.place-form').forEach((form) => {
    form.noValidate = true;
    const fields = [...form.querySelectorAll('input, select, textarea')]
        .filter((field) => field.type !== 'hidden' && !field.classList.contains('rich-editor'));
    const description = form.querySelector('.rich-editor');

    const clearFieldError = (event) => setFieldError(event.currentTarget, '');
    fields.forEach((field) => {
        field.addEventListener('input', clearFieldError);
        field.addEventListener('change', clearFieldError);
    });

    form.addEventListener('rich-editor-change', () => {
        if (description) setFieldError(description, '');
        form.querySelector('.ck-editor__editable')?.classList.remove('has-field-error');
    });

    form.addEventListener('submit', (event) => {
        let firstInvalid = null;
        fields.forEach((field) => {
            const message = field.checkValidity() ? '' : fieldMessage(field);
            setFieldError(field, message);
            if (message && !firstInvalid) firstInvalid = field;
        });

        if (description) {
            const textLength = description.value.replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').trim().length;
            const message = textLength < 80 ? 'Bài giới thiệu cần ít nhất 80 ký tự.' : '';
            setFieldError(description, message);
            const editor = form.querySelector('.ck-editor__editable');
            editor?.classList.toggle('has-field-error', Boolean(message));
            if (message && !firstInvalid) firstInvalid = editor || description;
        }

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
        }
    });
});
