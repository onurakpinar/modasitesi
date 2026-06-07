import 'trix';
import 'trix/dist/trix.css';

document.addEventListener('trix-before-initialize', () => {
    if (window.Trix?.config?.blockAttributes?.default) {
        delete window.Trix.config.blockAttributes.default;
    }
});

document.addEventListener('trix-file-accept', (event) => {
    event.preventDefault();
});

document.addEventListener('trix-initialize', (event) => {
    const editor = event.target;

    editor.addEventListener('trix-change', () => {
        const hiddenInput = document.getElementById('body-input');

        if (hiddenInput) {
            hiddenInput.value = editor.value;
        }
    });
});
