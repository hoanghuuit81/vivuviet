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
    BlockQuote
} from 'ckeditor5';

document.querySelectorAll('.rich-editor').forEach((element) => {
    ClassicEditor.create(element, {
        licenseKey: 'GPL',
        plugins: [Essentials, Paragraph, Bold, Italic, Underline, Link, List, Heading, BlockQuote],
        toolbar: ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', 'link', '|', 'bulletedList', 'numberedList', '|', 'blockQuote'],
        placeholder: element.getAttribute('placeholder') || 'Viết nội dung giới thiệu…'
    }).catch((error) => console.error('Không thể khởi tạo CKEditor:', error));
});
