import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import TextAlign from '@tiptap/extension-text-align';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function fetchTemplates(listUrl, selectEl, feedbackEl) {
    if (!listUrl || !selectEl) {
        return;
    }

    try {
        const response = await fetch(listUrl, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Nie udalo sie pobrac listy szablonow.');
        }

        const data = await response.json();
        const templates = Array.isArray(data.templates) ? data.templates : [];

        selectEl.innerHTML = '<option value="">-- wybierz szablon --</option>';
        templates.forEach((template) => {
            const option = document.createElement('option');
            option.value = String(template.id);
            option.textContent = template.name || `Szablon #${template.id}`;
            selectEl.appendChild(option);
        });

        if (feedbackEl) {
            feedbackEl.textContent = templates.length
                ? `Dostepne szablony: ${templates.length}`
                : 'Brak zapisanych szablonow.';
        }
    } catch (error) {
        if (feedbackEl) {
            feedbackEl.textContent = error.message || 'Blad pobierania szablonow.';
        }
    }
}

function initOfferDescriptionEditor() {
    const editorRoot = document.getElementById('offer_description_editor');
    const hiddenTextarea = document.getElementById('offer_description');

    if (!editorRoot || !hiddenTextarea) {
        return;
    }

    const toolbar = document.getElementById('offer-description-toolbar');
    const templateWrapper = document.getElementById('offer-template-controls');

    const editor = new Editor({
        element: editorRoot,
        extensions: [
            StarterKit,
            Link.configure({
                openOnClick: false,
                autolink: true,
                defaultProtocol: 'https',
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
        ],
        content: hiddenTextarea.value || '<p></p>',
        onUpdate: ({ editor: tiptapEditor }) => {
            hiddenTextarea.value = tiptapEditor.getHTML();
        },
    });

    if (toolbar) {
        toolbar.addEventListener('click', (event) => {
            const button = event.target.closest('[data-command]');
            if (!button) {
                return;
            }

            const command = button.dataset.command;
            const chain = editor.chain().focus();

            switch (command) {
                case 'bold':
                    chain.toggleBold().run();
                    break;
                case 'italic':
                    chain.toggleItalic().run();
                    break;
                case 'strike':
                    chain.toggleStrike().run();
                    break;
                case 'bulletList':
                    chain.toggleBulletList().run();
                    break;
                case 'orderedList':
                    chain.toggleOrderedList().run();
                    break;
                case 'h2':
                    chain.toggleHeading({ level: 2 }).run();
                    break;
                case 'h3':
                    chain.toggleHeading({ level: 3 }).run();
                    break;
                case 'alignLeft':
                    chain.setTextAlign('left').run();
                    break;
                case 'alignCenter':
                    chain.setTextAlign('center').run();
                    break;
                case 'alignRight':
                    chain.setTextAlign('right').run();
                    break;
                case 'link': {
                    const previous = editor.getAttributes('link').href || '';
                    const url = window.prompt('Podaj URL', previous || 'https://');
                    if (url === null) {
                        break;
                    }
                    if (url.trim() === '') {
                        chain.unsetLink().run();
                    } else {
                        chain.extendMarkRange('link').setLink({ href: url.trim() }).run();
                    }
                    break;
                }
                case 'clearFormatting':
                    chain.clearNodes().unsetAllMarks().run();
                    break;
                default:
                    break;
            }

            button.blur();
        });
    }

    if (templateWrapper) {
        const listUrl = templateWrapper.dataset.templatesListUrl || '';
        const saveUrl = templateWrapper.dataset.templatesSaveUrl || '';
        const showUrlTemplate = templateWrapper.dataset.templatesShowUrlTemplate || '';

        const templateSelect = document.getElementById('offer-template-select');
        const templateNameInput = document.getElementById('offer-template-name');
        const templateLoadBtn = document.getElementById('offer-template-load-btn');
        const templateSaveBtn = document.getElementById('offer-template-save-btn');
        const templateRefreshBtn = document.getElementById('offer-template-refresh-btn');
        const templateFeedback = document.getElementById('offer-template-feedback');

        const setFeedback = (message, isError = false) => {
            if (!templateFeedback) {
                return;
            }
            templateFeedback.textContent = message;
            templateFeedback.classList.toggle('text-red-600', isError);
            templateFeedback.classList.toggle('text-gray-500', !isError);
        };

        fetchTemplates(listUrl, templateSelect, templateFeedback);

        templateRefreshBtn?.addEventListener('click', async () => {
            templateRefreshBtn.disabled = true;
            await fetchTemplates(listUrl, templateSelect, templateFeedback);
            templateRefreshBtn.disabled = false;
        });

        templateLoadBtn?.addEventListener('click', async () => {
            const selectedId = templateSelect?.value || '';
            if (!selectedId) {
                setFeedback('Wybierz szablon do wczytania.', true);
                return;
            }

            const showUrl = showUrlTemplate.replace('__ID__', encodeURIComponent(selectedId));

            try {
                templateLoadBtn.disabled = true;
                const response = await fetch(showUrl, {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Nie udalo sie pobrac szablonu.');
                }

                const data = await response.json();
                const contentHtml = data.template?.content_html || '<p></p>';
                editor.commands.setContent(contentHtml, true);
                hiddenTextarea.value = editor.getHTML();
                setFeedback(`Wczytano szablon: ${data.template?.name || 'bez nazwy'}`);
            } catch (error) {
                setFeedback(error.message || 'Blad wczytywania szablonu.', true);
            } finally {
                templateLoadBtn.disabled = false;
            }
        });

        templateSaveBtn?.addEventListener('click', async () => {
            const name = (templateNameInput?.value || '').trim();
            if (!name) {
                setFeedback('Podaj nazwe szablonu.', true);
                return;
            }

            try {
                templateSaveBtn.disabled = true;

                const payload = {
                    name,
                    content_html: editor.getHTML(),
                    content_json: JSON.stringify(editor.getJSON()),
                };

                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw new Error(data.message || 'Nie udalo sie zapisac szablonu.');
                }

                await fetchTemplates(listUrl, templateSelect, templateFeedback);
                setFeedback(`Zapisano szablon: ${name}`);
                if (templateNameInput) {
                    templateNameInput.value = '';
                }
            } catch (error) {
                setFeedback(error.message || 'Blad zapisu szablonu.', true);
            } finally {
                templateSaveBtn.disabled = false;
            }
        });
    }

    hiddenTextarea.value = editor.getHTML();
}

document.addEventListener('DOMContentLoaded', initOfferDescriptionEditor);
