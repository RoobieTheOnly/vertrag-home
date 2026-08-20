(() => {
    const normalizeText = (value) => {
        return String(value ?? '')
            .toLocaleLowerCase('de-DE')
            .trim();
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat(
            'de-DE',
            {
                style: 'currency',
                currency: 'EUR',
            }
        ).format(
            Number(value) || 0
        );
    };

    const formatDate = (value) => {
        if (!value) {
            return '–';
        }

        const date = new Date(
            value + 'T00:00:00'
        );

        if (
            Number.isNaN(
                date.getTime()
            )
        ) {
            return value;
        }

        return new Intl.DateTimeFormat(
            'de-DE'
        ).format(date);
    };

    const setBodyModalState = (
        isOpen
    ) => {
        document.body.classList.toggle(
            'overflow-hidden',
            isOpen
        );
    };


    const notificationButton =
        document.querySelector(
            '[data-notification-button]'
        );

    const notificationMenu =
        document.querySelector(
            '[data-notification-menu]'
        );


    /*
    |--------------------------------------------------------------------------
    | Toast-Meldungen
    |--------------------------------------------------------------------------
    */

    const toastElements =
        Array.from(
            document.querySelectorAll(
                '[data-toast]'
            )
        );

    toastElements.forEach(
        (toast, index) => {
            let hideTimer = null;
            let removeTimer = null;

            const closeButton =
                toast.querySelector(
                    '[data-toast-close]'
                );

            const hideToast = () => {
                if (hideTimer) {
                    window.clearTimeout(
                        hideTimer
                    );
                }

                toast.classList.remove(
                    'opacity-100',
                    'translate-y-0'
                );

                toast.classList.add(
                    'opacity-0',
                    'translate-y-2'
                );

                removeTimer =
                    window.setTimeout(
                        () => {
                            toast.remove();
                        },
                        320
                    );
            };

            const showToast = () => {
                toast.classList.remove(
                    'opacity-0',
                    'translate-y-2'
                );

                toast.classList.add(
                    'opacity-100',
                    'translate-y-0'
                );

                const duration =
                    Math.max(
                        1500,
                        Number(
                            toast.dataset.toastDuration
                        ) || 3800
                    );

                hideTimer =
                    window.setTimeout(
                        hideToast,
                        duration
                    );
            };

            if (closeButton) {
                closeButton.addEventListener(
                    'click',
                    hideToast
                );
            }

            toast.addEventListener(
                'mouseenter',
                () => {
                    if (hideTimer) {
                        window.clearTimeout(
                            hideTimer
                        );

                        hideTimer = null;
                    }
                }
            );

            toast.addEventListener(
                'mouseleave',
                () => {
                    if (
                        removeTimer
                        || !document.body.contains(
                            toast
                        )
                    ) {
                        return;
                    }

                    hideTimer =
                        window.setTimeout(
                            hideToast,
                            1800
                        );
                }
            );

            window.setTimeout(
                showToast,
                80 + index * 120
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Benutzer-Menü
    |--------------------------------------------------------------------------
    */

    const menuButton =
        document.querySelector(
            '[data-user-menu-button]'
        );

    const userMenu =
        document.querySelector(
            '[data-user-menu]'
        );

    if (
        menuButton
        && userMenu
    ) {
        const closeMenu = () => {
            userMenu.classList.add(
                'hidden'
            );

            menuButton.setAttribute(
                'aria-expanded',
                'false'
            );
        };

        menuButton.addEventListener(
            'click',
            (event) => {
                event.stopPropagation();

                const opening =
                    userMenu.classList.contains(
                        'hidden'
                    );

                if (
                    notificationMenu
                    && notificationButton
                ) {
                    notificationMenu.classList.add(
                        'hidden'
                    );

                    notificationButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }

                userMenu.classList.toggle(
                    'hidden',
                    !opening
                );

                menuButton.setAttribute(
                    'aria-expanded',
                    opening
                        ? 'true'
                        : 'false'
                );
            }
        );

        userMenu.addEventListener(
            'click',
            (event) => {
                event.stopPropagation();
            }
        );

        document.addEventListener(
            'click',
            closeMenu
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Vertragshinweise
    |--------------------------------------------------------------------------
    */

    if (
        notificationButton
        && notificationMenu
    ) {
        const closeNotifications = () => {
            notificationMenu.classList.add(
                'hidden'
            );

            notificationButton.setAttribute(
                'aria-expanded',
                'false'
            );
        };

        notificationButton.addEventListener(
            'click',
            (event) => {
                event.stopPropagation();

                if (
                    userMenu
                    && menuButton
                ) {
                    userMenu.classList.add(
                        'hidden'
                    );

                    menuButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }

                const opening =
                    notificationMenu.classList.contains(
                        'hidden'
                    );

                notificationMenu.classList.toggle(
                    'hidden',
                    !opening
                );

                notificationButton.setAttribute(
                    'aria-expanded',
                    opening
                        ? 'true'
                        : 'false'
                );
            }
        );

        notificationMenu.addEventListener(
            'click',
            (event) => {
                event.stopPropagation();
            }
        );

        document.addEventListener(
            'click',
            closeNotifications
        );

        document.addEventListener(
            'keydown',
            (event) => {
                if (
                    event.key === 'Escape'
                    && !notificationMenu.classList.contains(
                        'hidden'
                    )
                ) {
                    closeNotifications();
                    notificationButton.focus();
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Integriertes Bestätigungs-Popup
    |--------------------------------------------------------------------------
    */

    const confirmModal =
        document.querySelector(
            '[data-confirm-modal]'
        );

    if (confirmModal) {
        const confirmTitle =
            confirmModal.querySelector(
                '[data-confirm-title-output]'
            );

        const confirmMessage =
            confirmModal.querySelector(
                '[data-confirm-message-output]'
            );

        const confirmAccept =
            confirmModal.querySelector(
                '[data-confirm-accept]'
            );

        const closeButtons =
            confirmModal.querySelectorAll(
                '[data-confirm-close], [data-confirm-cancel]'
            );

        let pendingForm = null;
        let previouslyFocused = null;

        const acceptBaseClasses = [
            'rounded-xl',
            'px-4',
            'py-2.5',
            'text-sm',
            'font-semibold',
            'text-white',
            'transition',
        ];

        const variantClasses = {
            danger: [
                'bg-red-600',
                'hover:bg-red-700',
                'dark:hover:bg-red-500',
            ],
            warning: [
                'bg-amber-600',
                'hover:bg-amber-700',
                'dark:hover:bg-amber-500',
            ],
            success: [
                'bg-emerald-600',
                'hover:bg-emerald-700',
                'dark:hover:bg-emerald-500',
            ],
            default: [
                'bg-blue-600',
                'hover:bg-blue-700',
                'dark:hover:bg-blue-500',
            ],
        };

        const closeConfirm = () => {
            confirmModal.classList.add(
                'hidden'
            );

            confirmModal.classList.remove(
                'flex'
            );

            confirmModal.setAttribute(
                'aria-hidden',
                'true'
            );

            setBodyModalState(false);

            pendingForm = null;

            if (previouslyFocused) {
                previouslyFocused.focus();
            }
        };

        const openConfirm = (form) => {
            pendingForm = form;
            previouslyFocused =
                document.activeElement;

            confirmTitle.textContent =
                form.dataset.confirmTitle
                || 'Aktion bestätigen';

            confirmMessage.textContent =
                form.dataset.confirmMessage
                || 'Möchten Sie diese Aktion wirklich ausführen?';

            confirmAccept.textContent =
                form.dataset.confirmLabel
                || 'Bestätigen';

            const variant =
                form.dataset.confirmVariant
                || 'default';

            confirmAccept.className =
                [
                    ...acceptBaseClasses,
                    ...(
                        variantClasses[
                            variant
                        ]
                        || variantClasses.default
                    ),
                ].join(' ');

            confirmModal.classList.remove(
                'hidden'
            );

            confirmModal.classList.add(
                'flex'
            );

            confirmModal.setAttribute(
                'aria-hidden',
                'false'
            );

            setBodyModalState(true);

            window.setTimeout(
                () => {
                    confirmAccept.focus();
                },
                0
            );
        };

        document.querySelectorAll(
            'form[data-confirm]'
        ).forEach((form) => {
            form.addEventListener(
                'submit',
                (event) => {
                    event.preventDefault();

                    openConfirm(form);
                }
            );
        });

        closeButtons.forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    closeConfirm
                );
            }
        );

        confirmAccept.addEventListener(
            'click',
            () => {
                if (!pendingForm) {
                    return;
                }

                const form =
                    pendingForm;

                pendingForm = null;

                confirmModal.classList.add(
                    'hidden'
                );

                confirmModal.classList.remove(
                    'flex'
                );

                confirmModal.setAttribute(
                    'aria-hidden',
                    'true'
                );

                setBodyModalState(false);

                form.submit();
            }
        );

        confirmModal.addEventListener(
            'click',
            (event) => {
                if (
                    event.target
                    === confirmModal
                ) {
                    closeConfirm();
                }
            }
        );
    }


/*
|--------------------------------------------------------------------------
| Kündigungs-Popup
|--------------------------------------------------------------------------
*/

const contractCancelModal =
    document.querySelector(
        '[data-contract-cancel-modal]'
    );

const contractCancelOpenButton =
    document.querySelector(
        '[data-contract-cancel-open]'
    );

if (
    contractCancelModal
    && contractCancelOpenButton
) {
    const closeButtons =
        contractCancelModal.querySelectorAll(
            '[data-contract-cancel-close]'
        );

    const dateInput =
        contractCancelModal.querySelector(
            'input[name="cancellation_effective_date"]'
        );

    let lastFocused = null;

    const openContractCancel = () => {
        lastFocused =
            document.activeElement;

        contractCancelModal.classList.remove(
            'hidden'
        );

        contractCancelModal.classList.add(
            'flex'
        );

        contractCancelModal.setAttribute(
            'aria-hidden',
            'false'
        );

        setBodyModalState(true);

        window.setTimeout(
            () => {
                if (dateInput) {
                    dateInput.focus();
                }
            },
            0
        );
    };

    const closeContractCancel = () => {
        contractCancelModal.classList.add(
            'hidden'
        );

        contractCancelModal.classList.remove(
            'flex'
        );

        contractCancelModal.setAttribute(
            'aria-hidden',
            'true'
        );

        setBodyModalState(false);

        if (lastFocused) {
            lastFocused.focus();
        }
    };

    contractCancelOpenButton.addEventListener(
        'click',
        openContractCancel
    );

    closeButtons.forEach(
        (button) => {
            button.addEventListener(
                'click',
                closeContractCancel
            );
        }
    );

    contractCancelModal.addEventListener(
        'click',
        (event) => {
            if (
                event.target
                === contractCancelModal
            ) {
                closeContractCancel();
            }
        }
    );

    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape'
                && !contractCancelModal
                    .classList
                    .contains(
                        'hidden'
                    )
            ) {
                closeContractCancel();
            }
        }
    );

    if (
        contractCancelModal.dataset
            .contractCancelOpenOnLoad
        === '1'
    ) {
        openContractCancel();
    }
}


/*
|--------------------------------------------------------------------------
| Vertragspause-Popup
|--------------------------------------------------------------------------
*/

const contractPauseModal =
    document.querySelector(
        '[data-contract-pause-modal]'
    );

const contractPauseOpenButtons =
    Array.from(
        document.querySelectorAll(
            '[data-contract-pause-open]'
        )
    );

if (
    contractPauseModal
    && contractPauseOpenButtons.length > 0
) {
    const closeButtons =
        contractPauseModal.querySelectorAll(
            '[data-contract-pause-close]'
        );

    const fromInput =
        contractPauseModal.querySelector(
            'input[name="pause_from"]'
        );

    const toInput =
        contractPauseModal.querySelector(
            'input[name="pause_to"]'
        );

    let lastFocused = null;

    const syncPauseMinimum = () => {
        if (
            !fromInput
            || !toInput
        ) {
            return;
        }

        toInput.min =
            fromInput.value
            || '';

        if (
            toInput.value
            && fromInput.value
            && toInput.value
                < fromInput.value
        ) {
            toInput.value =
                fromInput.value;
        }
    };

    const openContractPause = (
        button
    ) => {
        lastFocused =
            button
            || document.activeElement;

        contractPauseModal.classList.remove(
            'hidden'
        );

        contractPauseModal.classList.add(
            'flex'
        );

        contractPauseModal.setAttribute(
            'aria-hidden',
            'false'
        );

        syncPauseMinimum();
        setBodyModalState(true);

        window.setTimeout(
            () => {
                if (fromInput) {
                    fromInput.focus();
                }
            },
            0
        );
    };

    const closeContractPause = () => {
        contractPauseModal.classList.add(
            'hidden'
        );

        contractPauseModal.classList.remove(
            'flex'
        );

        contractPauseModal.setAttribute(
            'aria-hidden',
            'true'
        );

        setBodyModalState(false);

        if (lastFocused) {
            lastFocused.focus();
        }
    };

    contractPauseOpenButtons.forEach(
        (button) => {
            button.addEventListener(
                'click',
                () => {
                    openContractPause(
                        button
                    );
                }
            );
        }
    );

    closeButtons.forEach(
        (button) => {
            button.addEventListener(
                'click',
                closeContractPause
            );
        }
    );

    if (fromInput) {
        fromInput.addEventListener(
            'change',
            syncPauseMinimum
        );
    }

    contractPauseModal.addEventListener(
        'click',
        (event) => {
            if (
                event.target
                === contractPauseModal
            ) {
                closeContractPause();
            }
        }
    );

    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape'
                && !contractPauseModal
                    .classList
                    .contains(
                        'hidden'
                    )
            ) {
                closeContractPause();
            }
        }
    );

    if (
        contractPauseModal.dataset
            .contractPauseOpenOnLoad
        === '1'
    ) {
        openContractPause(
            contractPauseOpenButtons[0]
        );
    }

    syncPauseMinimum();
}


/*
|--------------------------------------------------------------------------
| Preis-/Kostenhistorie-Popup
|--------------------------------------------------------------------------
*/

const priceHistoryModal =
    document.querySelector(
        '[data-price-history-modal]'
    );

const priceHistoryOpenButton =
    document.querySelector(
        '[data-price-history-open]'
    );

if (
    priceHistoryModal
    && priceHistoryOpenButton
) {
    const closeButtons =
        priceHistoryModal.querySelectorAll(
            '[data-price-history-close]'
        );

    const firstInput =
        priceHistoryModal.querySelector(
            'input[name="amount"]'
        );

    let lastFocused = null;

    const openPriceHistory = () => {
        lastFocused =
            document.activeElement;

        priceHistoryModal.classList.remove(
            'hidden'
        );

        priceHistoryModal.classList.add(
            'flex'
        );

        priceHistoryModal.setAttribute(
            'aria-hidden',
            'false'
        );

        setBodyModalState(true);

        window.setTimeout(
            () => {
                if (firstInput) {
                    firstInput.focus();
                    firstInput.select();
                }
            },
            0
        );
    };

    const closePriceHistory = () => {
        priceHistoryModal.classList.add(
            'hidden'
        );

        priceHistoryModal.classList.remove(
            'flex'
        );

        priceHistoryModal.setAttribute(
            'aria-hidden',
            'true'
        );

        setBodyModalState(false);

        if (lastFocused) {
            lastFocused.focus();
        }
    };

    priceHistoryOpenButton.addEventListener(
        'click',
        openPriceHistory
    );

    closeButtons.forEach(
        (button) => {
            button.addEventListener(
                'click',
                closePriceHistory
            );
        }
    );

    priceHistoryModal.addEventListener(
        'click',
        (event) => {
            if (
                event.target
                === priceHistoryModal
            ) {
                closePriceHistory();
            }
        }
    );

    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape'
                && !priceHistoryModal
                    .classList
                    .contains(
                        'hidden'
                    )
            ) {
                closePriceHistory();
            }
        }
    );

    if (
        priceHistoryModal.dataset
            .priceHistoryOpenOnLoad
        === '1'
    ) {
        openPriceHistory();
    }
}


/*
|--------------------------------------------------------------------------
| Dokument-Upload-Popup
|--------------------------------------------------------------------------
*/

const documentUploadModal =
    document.querySelector(
        '[data-document-upload-modal]'
    );

const documentUploadOpenButton =
    document.querySelector(
        '[data-document-upload-open]'
    );

if (
    documentUploadModal
    && documentUploadOpenButton
) {
    const closeButtons =
        documentUploadModal.querySelectorAll(
            '[data-document-upload-close]'
        );

    const uploadForm =
        documentUploadModal.querySelector(
            '[data-document-upload-form]'
        );

    const fileInput =
        documentUploadModal.querySelector(
            '[data-document-upload-file]'
        );

    const fileNameOutput =
        documentUploadModal.querySelector(
            '[data-document-upload-file-name]'
        );

    const documentNameInput =
        documentUploadModal.querySelector(
            'input[name="document_name"]'
        );

    let lastFocused = null;

    const updateSelectedFileName = () => {
        const file =
            fileInput
            && fileInput.files
            && fileInput.files.length > 0
                ? fileInput.files[0]
                : null;

        if (!file) {
            fileNameOutput.textContent =
                'Noch keine Datei ausgewählt';

            return;
        }

        const megabytes =
            file.size
            / 1024
            / 1024;

        fileNameOutput.textContent =
            file.name
            + ' · '
            + (
                megabytes >= 0.1
                    ? megabytes.toFixed(1)
                        + ' MB'
                    : Math.max(
                        1,
                        Math.round(
                            file.size
                            / 1024
                        )
                    )
                        + ' KB'
            );
    };

    const openDocumentUpload = () => {
        lastFocused =
            document.activeElement;

        documentUploadModal.classList.remove(
            'hidden'
        );

        documentUploadModal.classList.add(
            'flex'
        );

        documentUploadModal.setAttribute(
            'aria-hidden',
            'false'
        );

        setBodyModalState(true);

        window.setTimeout(
            () => {
                if (documentNameInput) {
                    documentNameInput.focus();
                }
            },
            0
        );
    };

    const closeDocumentUpload = () => {
        documentUploadModal.classList.add(
            'hidden'
        );

        documentUploadModal.classList.remove(
            'flex'
        );

        documentUploadModal.setAttribute(
            'aria-hidden',
            'true'
        );

        setBodyModalState(false);

        if (uploadForm) {
            uploadForm.reset();
        }

        updateSelectedFileName();

        if (lastFocused) {
            lastFocused.focus();
        }
    };

    documentUploadOpenButton.addEventListener(
        'click',
        openDocumentUpload
    );

    closeButtons.forEach(
        (button) => {
            button.addEventListener(
                'click',
                closeDocumentUpload
            );
        }
    );

    if (fileInput) {
        fileInput.addEventListener(
            'change',
            updateSelectedFileName
        );
    }

    documentUploadModal.addEventListener(
        'click',
        (event) => {
            if (
                event.target
                === documentUploadModal
            ) {
                closeDocumentUpload();
            }
        }
    );

    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape'
                && !documentUploadModal
                    .classList
                    .contains(
                        'hidden'
                    )
            ) {
                closeDocumentUpload();
            }
        }
    );

    if (
        documentUploadModal.dataset
            .documentUploadOpenOnLoad
        === '1'
    ) {
        openDocumentUpload();
    }

    updateSelectedFileName();
}


/*
|--------------------------------------------------------------------------
| Dokumentvorschau
|--------------------------------------------------------------------------
*/

const documentPreviewModal =
    document.querySelector(
        '[data-document-preview-modal]'
    );

const documentRows =
    Array.from(
        document.querySelectorAll(
            '[data-document-open]'
        )
    );

if (
    documentPreviewModal
    && documentRows.length > 0
) {
    const titleOutput =
        documentPreviewModal.querySelector(
            '[data-document-preview-title]'
        );

    const metaOutput =
        documentPreviewModal.querySelector(
            '[data-document-preview-meta]'
        );

    const downloadLink =
        documentPreviewModal.querySelector(
            '[data-document-preview-download]'
        );

    const loading =
        documentPreviewModal.querySelector(
            '[data-document-preview-loading]'
        );

    const pdfWrap =
        documentPreviewModal.querySelector(
            '[data-document-preview-pdf-wrap]'
        );

    const pdfImage =
        documentPreviewModal.querySelector(
            '[data-document-pdf-image]'
        );

    const pdfScroll =
        documentPreviewModal.querySelector(
            '[data-document-pdf-scroll]'
        );

    const pdfPrev =
        documentPreviewModal.querySelector(
            '[data-document-pdf-prev]'
        );

    const pdfNext =
        documentPreviewModal.querySelector(
            '[data-document-pdf-next]'
        );

    const pdfPageLabel =
        documentPreviewModal.querySelector(
            '[data-document-pdf-page-label]'
        );

    const pdfZoomOut =
        documentPreviewModal.querySelector(
            '[data-document-pdf-zoom-out]'
        );

    const pdfZoomIn =
        documentPreviewModal.querySelector(
            '[data-document-pdf-zoom-in]'
        );

    const pdfZoomLabel =
        documentPreviewModal.querySelector(
            '[data-document-pdf-zoom-label]'
        );

    const imageWrap =
        documentPreviewModal.querySelector(
            '[data-document-preview-image-wrap]'
        );

    const image =
        documentPreviewModal.querySelector(
            '[data-document-preview-image]'
        );

    const textWrap =
        documentPreviewModal.querySelector(
            '[data-document-preview-text-wrap]'
        );

    const textOutput =
        documentPreviewModal.querySelector(
            '[data-document-preview-text]'
        );

    const unsupported =
        documentPreviewModal.querySelector(
            '[data-document-preview-unsupported]'
        );

    const unsupportedExtension =
        documentPreviewModal.querySelector(
            '[data-document-preview-extension]'
        );

    const closeButtons =
        documentPreviewModal.querySelectorAll(
            '[data-document-preview-close]'
        );

    let lastFocused = null;
    let textRequestController = null;
    let previewInfoController = null;

    const pdfState = {
        page: 1,
        pages: 1,
        zoom: 100,
        pageUrl: '',
    };

    const setLoading = (
        visible,
        message = 'Vorschau wird geladen …'
    ) => {
        loading.textContent =
            message;

        loading.classList.toggle(
            'hidden',
            !visible
        );
    };

    const updatePdfControls = () => {
        pdfPrev.disabled =
            pdfState.page <= 1;

        pdfNext.disabled =
            pdfState.page
            >= pdfState.pages;

        pdfPageLabel.textContent =
            'Seite '
            + pdfState.page
            + ' / '
            + pdfState.pages;

        pdfZoomLabel.textContent =
            pdfState.zoom
            + ' %';

        pdfZoomOut.disabled =
            pdfState.zoom <= 50;

        pdfZoomIn.disabled =
            pdfState.zoom >= 200;
    };

    const applyPdfZoom = () => {
        pdfImage.style.width =
            pdfState.zoom
            + '%';

        pdfImage.style.maxWidth =
            'none';

        updatePdfControls();
    };

    const loadPdfPage = (
        page
    ) => {
        const safePage =
            Math.min(
                pdfState.pages,
                Math.max(
                    1,
                    Number(page) || 1
                )
            );

        pdfState.page =
            safePage;

        updatePdfControls();

        setLoading(
            true,
            'Dokumentseite wird geladen …'
        );

        pdfImage.onload = () => {
            setLoading(false);

            if (pdfScroll) {
                pdfScroll.scrollTo({
                    top: 0,
                    left: 0,
                    behavior: 'instant',
                });
            }
        };

        pdfImage.onerror = () => {
            setLoading(
                true,
                'Die Dokumentseite konnte nicht dargestellt werden.'
            );
        };

        pdfImage.src =
            pdfState.pageUrl
            + '?page='
            + safePage;
    };

    const resetPreviewContent = () => {
        if (textRequestController) {
            textRequestController.abort();
            textRequestController = null;
        }

        if (previewInfoController) {
            previewInfoController.abort();
            previewInfoController = null;
        }

        pdfImage.onload = null;
        pdfImage.onerror = null;
        pdfImage.removeAttribute(
            'src'
        );

        pdfState.page = 1;
        pdfState.pages = 1;
        pdfState.zoom = 100;
        pdfState.pageUrl = '';

        pdfWrap.classList.add(
            'hidden'
        );

        pdfWrap.classList.remove(
            'flex'
        );

        image.onload = null;
        image.onerror = null;
        image.removeAttribute(
            'src'
        );

        imageWrap.classList.add(
            'hidden'
        );

        imageWrap.classList.remove(
            'flex'
        );

        textOutput.textContent = '';

        textWrap.classList.add(
            'hidden'
        );

        unsupported.classList.add(
            'hidden'
        );

        unsupported.classList.remove(
            'flex'
        );

        updatePdfControls();
        applyPdfZoom();

        setLoading(true);
    };

    const closeDocumentPreview = () => {
        documentPreviewModal.classList.add(
            'hidden'
        );

        documentPreviewModal.classList.remove(
            'flex'
        );

        documentPreviewModal.setAttribute(
            'aria-hidden',
            'true'
        );

        resetPreviewContent();
        setBodyModalState(false);

        if (lastFocused) {
            lastFocused.focus();
        }
    };

    const showPreviewError = (
        message
    ) => {
        setLoading(
            true,
            message
        );
    };

    const openPagedPreview = (
        mode,
        infoUrl,
        pageUrl
    ) => {
        pdfWrap.classList.remove(
            'hidden'
        );

        pdfWrap.classList.add(
            'flex'
        );

        pdfState.pageUrl =
            pageUrl;

        setLoading(
            true,
            mode === 'office'
                ? 'Dokument wird lokal für die Vorschau aufbereitet …'
                : 'PDF wird für die Vorschau vorbereitet …'
        );

        previewInfoController =
            new AbortController();

        fetch(
            infoUrl,
            {
                credentials:
                    'same-origin',
                signal:
                    previewInfoController.signal,
                headers: {
                    Accept:
                        'application/json',
                },
            }
        )
            .then(async (response) => {
                const data =
                    await response.json()
                        .catch(
                            () => ({})
                        );

                if (!response.ok) {
                    throw new Error(
                        data.error
                        || 'Die Dokumentvorschau konnte nicht vorbereitet werden.'
                    );
                }

                return data;
            })
            .then((data) => {
                pdfState.pages =
                    Math.max(
                        1,
                        Number(
                            data.pages
                        ) || 1
                    );

                pdfState.page = 1;
                pdfState.zoom = 100;

                applyPdfZoom();
                loadPdfPage(1);
            })
            .catch((error) => {
                if (
                    error.name
                    === 'AbortError'
                ) {
                    return;
                }

                pdfWrap.classList.add(
                    'hidden'
                );

                pdfWrap.classList.remove(
                    'flex'
                );

                showPreviewError(
                    error.message
                    || 'Die Dokumentvorschau konnte nicht geladen werden.'
                );
            });
    };

    const openDocumentPreview = (
        row
    ) => {
        lastFocused =
            document.activeElement;

        resetPreviewContent();

        const name =
            row.dataset.documentName
            || 'Dokument';

        const filename =
            row.dataset.documentFilename
            || '';

        const extension =
            row.dataset.documentExtension
            || '';

        const size =
            row.dataset.documentSize
            || '';

        const mode =
            row.dataset.documentPreviewMode
            || 'unsupported';

        const previewUrl =
            row.dataset.documentPreviewUrl
            || '';

        const previewInfoUrl =
            row.dataset.documentPreviewInfoUrl
            || '';

        const previewPageUrl =
            row.dataset.documentPreviewPageUrl
            || '';

        const downloadUrl =
            row.dataset.documentDownloadUrl
            || '#';

        titleOutput.textContent =
            name;

        metaOutput.textContent =
            [
                filename,
                extension,
                size,
            ]
                .filter(Boolean)
                .join(' · ');

        downloadLink.href =
            downloadUrl;

        unsupportedExtension.textContent =
            extension
            || 'DATEI';

        documentPreviewModal.classList.remove(
            'hidden'
        );

        documentPreviewModal.classList.add(
            'flex'
        );

        documentPreviewModal.setAttribute(
            'aria-hidden',
            'false'
        );

        setBodyModalState(true);

        if (
            mode === 'pdf'
            || mode === 'office'
        ) {
            openPagedPreview(
                mode,
                previewInfoUrl,
                previewPageUrl
            );

            return;
        }

        if (mode === 'image') {
            imageWrap.classList.remove(
                'hidden'
            );

            imageWrap.classList.add(
                'flex'
            );

            image.onload = () => {
                setLoading(false);
            };

            image.onerror = () => {
                imageWrap.classList.add(
                    'hidden'
                );

                imageWrap.classList.remove(
                    'flex'
                );

                showPreviewError(
                    'Die Bildvorschau konnte nicht geladen werden.'
                );
            };

            image.src =
                previewUrl;

            return;
        }

        if (mode === 'text') {
            textWrap.classList.remove(
                'hidden'
            );

            textRequestController =
                new AbortController();

            fetch(
                previewUrl,
                {
                    credentials:
                        'same-origin',
                    signal:
                        textRequestController.signal,
                }
            )
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(
                            'Vorschau konnte nicht geladen werden.'
                        );
                    }

                    return response.text();
                })
                .then((text) => {
                    textOutput.textContent =
                        text;

                    setLoading(false);
                })
                .catch((error) => {
                    if (
                        error.name
                        === 'AbortError'
                    ) {
                        return;
                    }

                    textWrap.classList.add(
                        'hidden'
                    );

                    showPreviewError(
                        'Die Textvorschau konnte nicht geladen werden.'
                    );
                });

            return;
        }

        setLoading(false);

        unsupported.classList.remove(
            'hidden'
        );

        unsupported.classList.add(
            'flex'
        );
    };

    pdfPrev.addEventListener(
        'click',
        () => {
            if (
                pdfState.page > 1
            ) {
                loadPdfPage(
                    pdfState.page - 1
                );
            }
        }
    );

    pdfNext.addEventListener(
        'click',
        () => {
            if (
                pdfState.page
                < pdfState.pages
            ) {
                loadPdfPage(
                    pdfState.page + 1
                );
            }
        }
    );

    pdfZoomOut.addEventListener(
        'click',
        () => {
            pdfState.zoom =
                Math.max(
                    50,
                    pdfState.zoom - 25
                );

            applyPdfZoom();
        }
    );

    pdfZoomIn.addEventListener(
        'click',
        () => {
            pdfState.zoom =
                Math.min(
                    200,
                    pdfState.zoom + 25
                );

            applyPdfZoom();
        }
    );

    documentRows.forEach((row) => {
        row.addEventListener(
            'click',
            (event) => {
                const explicitPreviewButton =
                    event.target.closest(
                        '[data-document-open-button]'
                    );

                if (explicitPreviewButton) {
                    event.preventDefault();
                    event.stopPropagation();

                    openDocumentPreview(
                        row
                    );

                    return;
                }

                if (
                    event.target.closest(
                        'a, button, input, select, textarea, label, form'
                    )
                ) {
                    return;
                }

                openDocumentPreview(
                    row
                );
            }
        );

        row.addEventListener(
            'keydown',
            (event) => {
                if (
                    event.key === 'Enter'
                    || event.key === ' '
                ) {
                    if (
                        event.target
                        !== row
                    ) {
                        return;
                    }

                    event.preventDefault();

                    openDocumentPreview(
                        row
                    );
                }
            }
        );
    });

    closeButtons.forEach(
        (button) => {
            button.addEventListener(
                'click',
                closeDocumentPreview
            );
        }
    );

    documentPreviewModal.addEventListener(
        'click',
        (event) => {
            if (
                event.target
                === documentPreviewModal
            ) {
                closeDocumentPreview();
            }
        }
    );

    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape'
                && !documentPreviewModal
                    .classList
                    .contains(
                        'hidden'
                    )
            ) {
                closeDocumentPreview();
            }
        }
    );

    resetPreviewContent();
}


    /*
    |--------------------------------------------------------------------------
    | Vertragsübersicht: einklappbare Filter + dynamische Suche
    |--------------------------------------------------------------------------
    */

    const contractFilters =
        document.querySelector(
            '[data-contract-filters]'
        );

    if (contractFilters) {
        const toggleButton =
            contractFilters.querySelector(
                '[data-contract-filter-toggle]'
            );

        const filterPanel =
            contractFilters.querySelector(
                '[data-contract-filter-panel]'
            );

        const chevron =
            contractFilters.querySelector(
                '[data-contract-filter-chevron]'
            );

        const filterBadge =
            contractFilters.querySelector(
                '[data-contract-filter-badge]'
            );

        const filterSummary =
            contractFilters.querySelector(
                '[data-contract-filter-summary]'
            );

        const searchInput =
            contractFilters.querySelector(
                '[data-contract-search]'
            );

        const holderSelect =
            contractFilters.querySelector(
                '[data-contract-holder-filter]'
            );

        const statusSelect =
            contractFilters.querySelector(
                '[data-contract-status-filter]'
            );

        const resetButton =
            contractFilters.querySelector(
                '[data-contract-filter-reset]'
            );

        const rows = Array.from(
            document.querySelectorAll(
                '[data-contract-row]'
            )
        );

        const emptyRow =
            document.querySelector(
                '[data-contract-filter-empty]'
            );

        const countLabel =
            document.querySelector(
                '[data-contract-filter-count]'
            );

        const totalRow =
            document.querySelector(
                '[data-contract-total-row]'
            );

        const totalMonthly =
            document.querySelector(
                '[data-contract-total-monthly]'
            );

        const totalAnnual =
            document.querySelector(
                '[data-contract-total-annual]'
            );

        const setFilterPanelOpen = (
            isOpen
        ) => {
            filterPanel.classList.toggle(
                'hidden',
                !isOpen
            );

            toggleButton.setAttribute(
                'aria-expanded',
                isOpen
                    ? 'true'
                    : 'false'
            );

            chevron.classList.toggle(
                'rotate-180',
                isOpen
            );
        };

        toggleButton.addEventListener(
            'click',
            () => {
                setFilterPanelOpen(
                    filterPanel.classList.contains(
                        'hidden'
                    )
                );
            }
        );


        const updateContractUrl = () => {
            const url = new URL(
                window.location.href
            );

            const query =
                searchInput.value.trim();

            if (query !== '') {
                url.searchParams.set(
                    'q',
                    query
                );
            } else {
                url.searchParams.delete(
                    'q'
                );
            }

            if (holderSelect.value) {
                url.searchParams.set(
                    'holder',
                    holderSelect.value
                );
            } else {
                url.searchParams.delete(
                    'holder'
                );
            }

            if (statusSelect.value) {
                url.searchParams.set(
                    'status',
                    statusSelect.value
                );
            } else {
                url.searchParams.delete(
                    'status'
                );
            }

            window.history.replaceState(
                {},
                '',
                url
            );
        };


        const updateContractFilterSummary = () => {
            const query =
                searchInput.value.trim();

            const items = [];

            if (query !== '') {
                items.push(
                    'Suche: '
                    + query
                );
            }

            if (holderSelect.value) {
                items.push(
                    'Inhaber: '
                    + holderSelect.options[
                        holderSelect.selectedIndex
                    ].text
                );
            }

            if (statusSelect.value) {
                items.push(
                    'Status: '
                    + statusSelect.options[
                        statusSelect.selectedIndex
                    ].text
                );
            }

            filterBadge.classList.toggle(
                'hidden',
                items.length === 0
            );

            filterBadge.textContent =
                items.length
                + ' aktiv';

            filterSummary.textContent =
                items.length === 0
                    ? 'Zum Suchen und Filtern aufklappen'
                    : items.join(' · ');
        };


        const setContractRowVisible = (
            row,
            visible
        ) => {
            if (visible) {
                row.style.removeProperty(
                    'display'
                );

                row.removeAttribute(
                    'aria-hidden'
                );

                return;
            }

            /*
             * Nicht nur die Tailwind-Klasse "hidden" verwenden:
             * responsive Klassen wie md:grid können display:none
             * auf Desktop wieder überschreiben.
             */
            row.style.setProperty(
                'display',
                'none',
                'important'
            );

            row.setAttribute(
                'aria-hidden',
                'true'
            );
        };


        const applyContractFilters = () => {
            const query =
                normalizeText(
                    searchInput.value
                );

            const searchTerms =
                query === ''
                    ? []
                    : query
                        .split(/\s+/)
                        .filter(Boolean);

            const holderId =
                holderSelect.value;

            const status =
                statusSelect.value;

            let visibleCount = 0;
            let visibleMonthlyTotal = 0;
            let visibleAnnualTotal = 0;

            rows.forEach((row) => {
                const searchableText =
                    normalizeText(
                        row.dataset.search
                        || row.textContent
                    );

                const searchMatches =
                    searchTerms.length === 0
                    || searchTerms.every(
                        (term) =>
                            searchableText.includes(
                                term
                            )
                    );

                const holderMatches =
                    holderId === ''
                    || row.dataset.holder
                        === holderId;

                const rowStatuses =
                    String(
                        row.dataset.statuses
                        || row.dataset.status
                        || ''
                    )
                        .split(/\s+/)
                        .filter(Boolean);

                const statusMatches =
                    status === ''
                    || rowStatuses.includes(
                        status
                    );

                const visible =
                    searchMatches
                    && holderMatches
                    && statusMatches;

                setContractRowVisible(
                    row,
                    visible
                );

                if (visible) {
                    visibleCount += 1;

                    visibleMonthlyTotal +=
                        Number(
                            row.dataset.monthlyCost
                            || 0
                        );

                    visibleAnnualTotal +=
                        Number(
                            row.dataset.annualCost
                            || 0
                        );
                }
            });


            if (totalRow) {
                totalRow.classList.toggle(
                    'hidden',
                    visibleCount <= 1
                );
            }

            if (totalMonthly) {
                totalMonthly.textContent =
                    formatCurrency(
                        visibleMonthlyTotal
                    );
            }

            if (totalAnnual) {
                totalAnnual.textContent =
                    formatCurrency(
                        visibleAnnualTotal
                    );
            }


            if (emptyRow) {
                emptyRow.classList.toggle(
                    'hidden',
                    visibleCount !== 0
                );
            }


            if (countLabel) {
                countLabel.textContent =
                    visibleCount
                    + ' von '
                    + rows.length
                    + (
                        rows.length === 1
                            ? ' Vertrag'
                            : ' Verträgen'
                    );
            }


            if (resetButton) {
                resetButton.classList.toggle(
                    'hidden',
                    query === ''
                    && holderId === ''
                    && status === ''
                );
            }

            updateContractFilterSummary();
            updateContractUrl();
        };


        searchInput.addEventListener(
            'input',
            applyContractFilters
        );

        holderSelect.addEventListener(
            'change',
            applyContractFilters
        );

        statusSelect.addEventListener(
            'change',
            applyContractFilters
        );


        if (resetButton) {
            resetButton.addEventListener(
                'click',
                () => {
                    searchInput.value = '';
                    holderSelect.value = '';
                    statusSelect.value = '';

                    applyContractFilters();
                    searchInput.focus();
                }
            );
        }


        rows.forEach((row) => {
            const openContract = () => {
                const href =
                    row.dataset.href;

                if (href) {
                    window.location.href =
                        href;
                }
            };

            row.addEventListener(
                'click',
                (event) => {
                    if (
                        event.target.closest(
                            'a, button, input, select, textarea, label, form'
                        )
                    ) {
                        return;
                    }

                    openContract();
                }
            );

            row.addEventListener(
                'keydown',
                (event) => {
                    if (
                        event.key === 'Enter'
                        || event.key === ' '
                    ) {
                        event.preventDefault();
                        openContract();
                    }
                }
            );
        });


        setFilterPanelOpen(false);
        applyContractFilters();
    }


/*
|--------------------------------------------------------------------------
| Ausgabenplanung
|--------------------------------------------------------------------------
*/

const planner =
    document.querySelector(
        '[data-payment-planner]'
    );

if (planner) {
    const dataNode =
        planner.querySelector(
            '[data-payment-planner-data]'
        );

    const chart =
        planner.querySelector(
            '[data-payment-chart]'
        );

    const chartScale =
        planner.querySelector(
            '[data-payment-chart-scale]'
        );

    const chartGrid =
        planner.querySelector(
            '[data-payment-chart-grid]'
        );

    const chartEmpty =
        planner.querySelector(
            '[data-payment-chart-empty]'
        );

    const totalOutput =
        planner.querySelector(
            '[data-payment-total]'
        );

    const countOutput =
        planner.querySelector(
            '[data-payment-count]'
        );

    const rangeLabel =
        planner.querySelector(
            '[data-payment-range-label]'
        );

    const eventList =
        planner.querySelector(
            '[data-payment-event-list]'
        );

    const averageOutput =
        planner.querySelector(
            '[data-payment-average]'
        );

    const nextOutput =
        planner.querySelector(
            '[data-payment-next]'
        );

    const largestOutput =
        planner.querySelector(
            '[data-payment-largest]'
        );

    const largestMetaOutput =
        planner.querySelector(
            '[data-payment-largest-meta]'
        );

    const peakOutput =
        planner.querySelector(
            '[data-payment-peak]'
        );

    const peakMetaOutput =
        planner.querySelector(
            '[data-payment-peak-meta]'
        );

    const coveredContractsOutput =
        planner.querySelector(
            '[data-payment-covered-contracts]'
        );

    const activeHolderOutput =
        planner.querySelector(
            '[data-payment-active-holder]'
        );

    const topContractsOutput =
        planner.querySelector(
            '[data-payment-top-contracts]'
        );

    const breakdownOutput =
        planner.querySelector(
            '[data-payment-breakdown]'
        );

    const rangeButtons =
        Array.from(
            planner.querySelectorAll(
                '[data-payment-range]'
            )
        );

    const holderSelect =
        planner.querySelector(
            '[data-payment-holder]'
        );

    let activeRange = 30;
    let paymentEvents = [];

    try {
        paymentEvents =
            JSON.parse(
                dataNode.textContent
                || '[]'
            );
    } catch {
        paymentEvents = [];
    }

    const today =
        new Date();

    today.setHours(
        0,
        0,
        0,
        0
    );

    const parseDate = (value) => {
        const date =
            new Date(
                value
                + 'T00:00:00'
            );

        date.setHours(
            0,
            0,
            0,
            0
        );

        return date;
    };

    const addDays = (
        date,
        days
    ) => {
        const result =
            new Date(date);

        result.setDate(
            result.getDate()
            + days
        );

        return result;
    };

    const addMonthsClamped = (
        date,
        months
    ) => {
        const originalDay =
            date.getDate();

        const result =
            new Date(
                date.getFullYear(),
                date.getMonth()
                    + months,
                1
            );

        const lastDay =
            new Date(
                result.getFullYear(),
                result.getMonth() + 1,
                0
            ).getDate();

        result.setDate(
            Math.min(
                originalDay,
                lastDay
            )
        );

        return result;
    };

    const shortDate = (date) => {
        return new Intl.DateTimeFormat(
            'de-DE',
            {
                day: '2-digit',
                month: '2-digit',
            }
        ).format(date);
    };

    const monthLabel = (date) => {
        return new Intl.DateTimeFormat(
            'de-DE',
            {
                month: 'short',
            }
        ).format(date)
            .replace('.', '');
    };

    const formatScaleCurrency = (
        value
    ) => {
        const numeric =
            Number(value) || 0;

        if (
            Math.abs(numeric)
            >= 1000
        ) {
            return new Intl.NumberFormat(
                'de-DE',
                {
                    maximumFractionDigits: 1,
                }
            ).format(
                numeric / 1000
            )
                + ' T€';
        }

        return new Intl.NumberFormat(
            'de-DE',
            {
                maximumFractionDigits:
                    numeric < 10
                        ? 1
                        : 0,
            }
        ).format(numeric)
            + ' €';
    };

    const niceScaleMaximum = (
        value
    ) => {
        const numeric =
            Math.max(
                0,
                Number(value) || 0
            );

        if (numeric <= 0) {
            return 100;
        }

        const exponent =
            Math.floor(
                Math.log10(
                    numeric
                )
            );

        const magnitude =
            Math.pow(
                10,
                exponent
            );

        const normalized =
            numeric / magnitude;

        let niceNormalized = 1;

        if (normalized <= 1) {
            niceNormalized = 1;
        } else if (
            normalized <= 2
        ) {
            niceNormalized = 2;
        } else if (
            normalized <= 5
        ) {
            niceNormalized = 5;
        } else {
            niceNormalized = 10;
        }

        return (
            niceNormalized
            * magnitude
        );
    };

    const renderChartScale = (
        maximum
    ) => {
        chartScale.replaceChildren();
        chartGrid.replaceChildren();

        const steps = 4;

        for (
            let index = 0;
            index <= steps;
            index += 1
        ) {
            const ratio =
                index / steps;

            const value =
                maximum
                * (
                    1 - ratio
                );

            const top =
                ratio * 100;

            const label =
                document.createElement(
                    'div'
                );

            label.className =
                'absolute right-0 -translate-y-1/2 whitespace-nowrap pr-1 text-[9px] font-medium text-slate-500 sm:text-[10px] dark:text-slate-400';

            label.style.top =
                'calc((100% - 24px) * '
                + ratio
                + ')';

            label.textContent =
                formatScaleCurrency(
                    value
                );

            chartScale.append(
                label
            );

            const line =
                document.createElement(
                    'div'
                );

            line.className =
                index === steps
                    ? 'absolute inset-x-0 border-t border-slate-300 dark:border-slate-700'
                    : 'absolute inset-x-0 border-t border-dashed border-slate-200 dark:border-slate-800';

            line.style.top =
                top + '%';

            chartGrid.append(
                line
            );
        }
    };

    const rangeEndFor = (
        range
    ) => {
        if (range === 365) {
            return addMonthsClamped(
                today,
                12
            );
        }

        if (range === 90) {
            return addMonthsClamped(
                today,
                3
            );
        }

        return addMonthsClamped(
            today,
            1
        );
    };

    const createBins = (
        range
    ) => {
        const bins = [];
        const rangeEnd =
            rangeEndFor(range);

        if (range === 365) {
            for (
                let index = 0;
                index < 12;
                index += 1
            ) {
                bins.push({
                    start:
                        addMonthsClamped(
                            today,
                            index
                        ),
                    end:
                        addMonthsClamped(
                            today,
                            index + 1
                        ),
                    label:
                        monthLabel(
                            addMonthsClamped(
                                today,
                                index
                            )
                        ),
                    amount: 0,
                    count: 0,
                });
            }

            return bins;
        }

        const binCount =
            range === 90
                ? 6
                : 4;

        const totalDays =
            Math.max(
                1,
                Math.round(
                    (
                        rangeEnd.getTime()
                        - today.getTime()
                    )
                    / 86400000
                )
            );

        for (
            let index = 0;
            index < binCount;
            index += 1
        ) {
            const startOffset =
                Math.floor(
                    (
                        totalDays
                        * index
                    )
                    / binCount
                );

            const endOffset =
                index === binCount - 1
                    ? totalDays
                    : Math.floor(
                        (
                            totalDays
                            * (
                                index + 1
                            )
                        )
                        / binCount
                    );

            const start =
                addDays(
                    today,
                    startOffset
                );

            const end =
                addDays(
                    today,
                    endOffset
                );

            bins.push({
                start,
                end,
                label:
                    shortDate(start),
                amount: 0,
                count: 0,
            });
        }

        return bins;
    };

    const eventIsInRange = (
        event,
        range
    ) => {
        const date =
            parseDate(
                event.date
            );

        const end =
            rangeEndFor(range);

        return (
            date >= today
            && date < end
        );
    };

    const buildEventCard = (
        event
    ) => {
        const link =
            document.createElement(
                'a'
            );

        link.href =
            '/contracts/'
            + event.contract_id;

        link.className =
            'flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-slate-50 dark:border-slate-800 dark:hover:border-blue-700 dark:hover:bg-slate-800/60';

        const left =
            document.createElement(
                'div'
            );

        left.className =
            'min-w-0';

        const title =
            document.createElement(
                'div'
            );

        title.className =
            'truncate font-semibold text-slate-900 dark:text-white';

        title.textContent =
            event.title;

        const meta =
            document.createElement(
                'div'
            );

        meta.className =
            'mt-1 text-xs text-slate-500 dark:text-slate-400';

        meta.textContent =
            formatDate(
                event.date
            )
            + ' · '
            + event.holder
            + ' · '
            + event.provider;

        left.append(
            title,
            meta
        );

        const amount =
            document.createElement(
                'div'
            );

        amount.className =
            'shrink-0 font-bold text-slate-900 dark:text-white';

        amount.textContent =
            formatCurrency(
                event.amount
            );

        link.append(
            left,
            amount
        );

        return link;
    };

    const buildChartBar = (
        bin,
        scaleMaximum,
        range
    ) => {
        const wrapper =
            document.createElement(
                'div'
            );

        wrapper.className =
            'group relative flex h-full min-w-0 flex-col';

        const barArea =
            document.createElement(
                'div'
            );

        barArea.className =
            'flex min-h-0 flex-1 items-end justify-center px-0.5';

        const bar =
            document.createElement(
                'div'
            );

        const percentage =
            scaleMaximum > 0
            && bin.amount > 0
                ? Math.min(
                    100,
                    Math.max(
                        3,
                        (
                            bin.amount
                            / scaleMaximum
                        ) * 100
                    )
                )
                : 1;

        bar.className =
            bin.amount > 0
                ? 'relative w-full max-w-10 rounded-t-md bg-blue-600 transition-all outline-none focus:ring-2 focus:ring-blue-300 dark:bg-blue-500 dark:focus:ring-blue-700'
                : 'w-full max-w-10 rounded-t-md bg-slate-200 dark:bg-slate-800';

        bar.style.height =
            percentage + '%';

        const tooltipText =
            bin.label
            + ': '
            + formatCurrency(
                bin.amount
            )
            + ' · '
            + bin.count
            + (
                bin.count === 1
                    ? ' Abbuchung'
                    : ' Abbuchungen'
            );

        if (bin.amount > 0) {
            bar.tabIndex = 0;

            bar.setAttribute(
                'aria-label',
                tooltipText
            );

            const tooltip =
                document.createElement(
                    'div'
                );

            tooltip.className =
                'pointer-events-none absolute bottom-full left-1/2 z-30 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-slate-950 px-2.5 py-1.5 text-[11px] font-semibold text-white opacity-0 shadow-xl transition-opacity group-hover:opacity-100 group-focus-within:opacity-100 lg:block dark:bg-white dark:text-slate-900';

            tooltip.textContent =
                tooltipText;

            bar.append(
                tooltip
            );
        }

        barArea.append(bar);

        const label =
            document.createElement(
                'div'
            );

        label.className =
            range === 365
                ? 'flex h-6 max-w-full items-end justify-center truncate text-[9px] font-medium text-slate-500 sm:text-[10px] dark:text-slate-400'
                : 'flex h-6 max-w-full items-end justify-center truncate text-[10px] font-medium text-slate-500 sm:text-xs dark:text-slate-400';

        label.textContent =
            bin.label;

        wrapper.append(
            barArea,
            label
        );

        return wrapper;
    };

    const buildTopContractCard = (
        item,
        index
    ) => {
        const card =
            document.createElement(
                'a'
            );

        card.href =
            '/contracts/'
            + item.contract_id;

        card.className =
            'block rounded-xl border border-slate-200 p-3 transition hover:border-blue-300 hover:bg-slate-50 dark:border-slate-800 dark:hover:border-blue-700 dark:hover:bg-slate-800/60';

        const titleRow =
            document.createElement(
                'div'
            );

        titleRow.className =
            'flex items-start justify-between gap-3';

        const titleWrap =
            document.createElement(
                'div'
            );

        titleWrap.className =
            'min-w-0';

        const title =
            document.createElement(
                'div'
            );

        title.className =
            'truncate font-semibold text-slate-900 dark:text-white';

        title.textContent =
            (index + 1)
            + '. '
            + item.title;

        const meta =
            document.createElement(
                'div'
            );

        meta.className =
            'mt-1 text-xs text-slate-500 dark:text-slate-400';

        meta.textContent =
            item.holder
            + ' · '
            + item.provider
            + ' · '
            + item.count
            + (
                item.count === 1
                    ? ' Abbuchung'
                    : ' Abbuchungen'
            );

        titleWrap.append(
            title,
            meta
        );

        const amount =
            document.createElement(
                'div'
            );

        amount.className =
            'shrink-0 text-right';

        amount.innerHTML =
            '<div class="font-bold text-slate-900 dark:text-white">'
            + formatCurrency(
                item.total
            )
            + '</div>'
            + '<div class="mt-1 text-xs text-slate-500 dark:text-slate-400">nächste: '
            + formatDate(
                item.nextDate
            )
            + '</div>';

        titleRow.append(
            titleWrap,
            amount
        );

        card.append(titleRow);

        return card;
    };

    const buildBreakdownCard = (
        bin,
        isPeak
    ) => {
        const card =
            document.createElement(
                'div'
            );

        card.className =
            isPeak
                ? 'rounded-xl border border-blue-200 bg-blue-50/60 p-3 dark:border-blue-900 dark:bg-blue-950/20'
                : 'rounded-xl border border-slate-200 p-3 dark:border-slate-800';

        const row =
            document.createElement(
                'div'
            );

        row.className =
            'flex items-center justify-between gap-3';

        const left =
            document.createElement(
                'div'
            );

        const title =
            document.createElement(
                'div'
            );

        title.className =
            'font-semibold text-slate-900 dark:text-white';

        title.textContent =
            bin.label;

        const meta =
            document.createElement(
                'div'
            );

        meta.className =
            'mt-1 text-xs text-slate-500 dark:text-slate-400';

        meta.textContent =
            bin.count
            + (
                bin.count === 1
                    ? ' Abbuchung'
                    : ' Abbuchungen'
            );

        left.append(
            title,
            meta
        );

        const right =
            document.createElement(
                'div'
            );

        right.className =
            'text-right font-bold text-slate-900 dark:text-white';

        right.textContent =
            formatCurrency(
                bin.amount
            );

        row.append(
            left,
            right
        );

        card.append(row);

        return card;
    };


    const renderPlanner = (
        range
    ) => {
        activeRange = range;

        const selectedHolderId =
            holderSelect
                ? holderSelect.value
                : '';

        const selectedHolderLabel =
            holderSelect
            && holderSelect.selectedIndex >= 0
                ? holderSelect.options[
                    holderSelect.selectedIndex
                ].textContent.trim()
                : 'Alle Vertragsinhaber';

        const activeEvents =
            paymentEvents.filter(
                (event) => {
                    const rangeMatches =
                        eventIsInRange(
                            event,
                            range
                        );

                    const holderMatches =
                        selectedHolderId === ''
                        || String(
                            event.holder_id
                            ?? ''
                        ) === selectedHolderId;

                    return (
                        rangeMatches
                        && holderMatches
                    );
                }
            );

        const total =
            activeEvents.reduce(
                (
                    sum,
                    event
                ) =>
                    sum
                    + Number(
                        event.amount
                    ),
                0
            );

        totalOutput.textContent =
            formatCurrency(total);

        countOutput.textContent =
            String(
                activeEvents.length
            );

        rangeLabel.textContent =
            range === 30
                ? 'Nächster Monat ab heute'
                : (
                    range === 90
                        ? 'Nächste 3 Monate'
                        : 'Nächstes Jahr'
                );

        if (activeHolderOutput) {
            activeHolderOutput.textContent =
                selectedHolderLabel;
        }

        const bins =
            createBins(range);

        activeEvents.forEach(
            (event) => {
                const eventDate =
                    parseDate(
                        event.date
                    );

                const bin =
                    bins.find(
                        (candidate) =>
                            eventDate
                                >= candidate.start
                            && eventDate
                                < candidate.end
                    );

                if (bin) {
                    bin.amount +=
                        Number(
                            event.amount
                        );

                    bin.count += 1;
                }
            }
        );

        const maxAmount =
            Math.max(
                0,
                ...bins.map(
                    (bin) =>
                        bin.amount
                )
            );

        const scaleMaximum =
            niceScaleMaximum(
                maxAmount
            );

        renderChartScale(
            scaleMaximum
        );

        chart.replaceChildren();

        chart.style.gridTemplateColumns =
            'repeat('
            + bins.length
            + ', minmax(0, 1fr))';

        bins.forEach((bin) => {
            chart.append(
                buildChartBar(
                    bin,
                    scaleMaximum,
                    range
                )
            );
        });

        chart.classList.toggle(
            'hidden',
            activeEvents.length
            === 0
        );

        chartEmpty.classList.toggle(
            'hidden',
            activeEvents.length
            !== 0
        );

        const monthDivisor =
            range === 365
                ? 12
                : (
                    range === 90
                        ? 3
                        : 1
                );

        if (averageOutput) {
            averageOutput.textContent =
                'Ø pro Monat: '
                + formatCurrency(
                    monthDivisor > 0
                        ? total / monthDivisor
                        : total
                );
        }

        const coveredContracts =
            new Set(
                activeEvents.map(
                    (event) =>
                        String(
                            event.contract_id
                        )
                )
            ).size;

        if (coveredContractsOutput) {
            coveredContractsOutput.textContent =
                coveredContracts
                + (
                    coveredContracts === 1
                        ? ' Vertrag betroffen'
                        : ' Verträge betroffen'
                );
        }

        const nextEvent =
            activeEvents.length > 0
                ? activeEvents[0]
                : null;

        if (nextOutput) {
            nextOutput.textContent =
                nextEvent
                    ? formatDate(
                        nextEvent.date
                    )
                    : '–';
        }

        let largestEvent = null;

        activeEvents.forEach(
            (event) => {
                if (
                    !largestEvent
                    || Number(
                        event.amount
                    )
                        > Number(
                            largestEvent.amount
                        )
                ) {
                    largestEvent = event;
                }
            }
        );

        if (largestOutput) {
            largestOutput.textContent =
                largestEvent
                    ? formatCurrency(
                        largestEvent.amount
                    )
                    : '–';
        }

        if (largestMetaOutput) {
            largestMetaOutput.textContent =
                largestEvent
                    ? largestEvent.title
                        + ' · '
                        + formatDate(
                            largestEvent.date
                        )
                    : 'Noch keine Abbuchung im Zeitraum';
        }

        const peakBin =
            bins.reduce(
                (
                    winner,
                    bin
                ) => {
                    if (!winner) {
                        return bin;
                    }

                    return bin.amount
                        > winner.amount
                        ? bin
                        : winner;
                },
                null
            );

        if (peakOutput) {
            peakOutput.textContent =
                peakBin
                && peakBin.amount > 0
                    ? peakBin.label
                    : '–';
        }

        if (peakMetaOutput) {
            peakMetaOutput.textContent =
                peakBin
                && peakBin.amount > 0
                    ? formatCurrency(
                        peakBin.amount
                    )
                    + ' · '
                    + peakBin.count
                    + (
                        peakBin.count === 1
                            ? ' Abbuchung'
                            : ' Abbuchungen'
                    )
                    : 'Im gewählten Zeitraum gibt es noch keine Belastung.';
        }

        eventList.replaceChildren();

        activeEvents
            .slice(0, 12)
            .forEach((event) => {
                eventList.append(
                    buildEventCard(
                        event
                    )
                );
            });

        if (
            activeEvents.length > 12
        ) {
            const more =
                document.createElement(
                    'div'
                );

            more.className =
                'rounded-xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400';

            more.textContent =
                '+ '
                + (
                    activeEvents.length
                    - 12
                )
                + ' weitere Abbuchungen im Zeitraum';

            eventList.append(more);
        }

        if (
            activeEvents.length === 0
        ) {
            const empty =
                document.createElement(
                    'div'
                );

            empty.className =
                'col-span-full rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400';

            empty.textContent =
                'Keine Abbuchungen im ausgewählten Zeitraum.';

            eventList.append(empty);
        }

        if (topContractsOutput) {
            topContractsOutput.replaceChildren();

            const topContractsMap =
                new Map();

            activeEvents.forEach(
                (event) => {
                    const key =
                        String(
                            event.contract_id
                        );

                    if (
                        !topContractsMap.has(
                            key
                        )
                    ) {
                        topContractsMap.set(
                            key,
                            {
                                contract_id:
                                    event.contract_id,
                                title:
                                    event.title,
                                holder:
                                    event.holder,
                                provider:
                                    event.provider,
                                total: 0,
                                count: 0,
                                nextDate:
                                    event.date,
                            }
                        );
                    }

                    const item =
                        topContractsMap.get(
                            key
                        );

                    item.total +=
                        Number(
                            event.amount
                        );

                    item.count += 1;

                    if (
                        event.date
                        < item.nextDate
                    ) {
                        item.nextDate =
                            event.date;
                    }
                }
            );

            const topContracts =
                Array.from(
                    topContractsMap.values()
                )
                    .sort(
                        (a, b) => {
                            if (
                                b.total !== a.total
                            ) {
                                return (
                                    b.total
                                    - a.total
                                );
                            }

                            return a.title.localeCompare(
                                b.title,
                                'de-DE'
                            );
                        }
                    )
                    .slice(0, 5);

            if (
                topContracts.length === 0
            ) {
                const empty =
                    document.createElement(
                        'div'
                    );

                empty.className =
                    'rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400';

                empty.textContent =
                    'Keine Verträge im aktuellen Zeitraum.';

                topContractsOutput.append(
                    empty
                );
            } else {
                topContracts.forEach(
                    (
                        item,
                        index
                    ) => {
                        topContractsOutput.append(
                            buildTopContractCard(
                                item,
                                index
                            )
                        );
                    }
                );
            }
        }

        if (breakdownOutput) {
            breakdownOutput.replaceChildren();

            const nonEmptyBins =
                bins.filter(
                    (bin) =>
                        bin.amount > 0
                        || bin.count > 0
                );

            if (
                nonEmptyBins.length === 0
            ) {
                const empty =
                    document.createElement(
                        'div'
                    );

                empty.className =
                    'rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400';

                empty.textContent =
                    'Keine Verteilung vorhanden, weil im Zeitraum nichts abgebucht wird.';

                breakdownOutput.append(
                    empty
                );
            } else {
                nonEmptyBins.forEach(
                    (bin) => {
                        breakdownOutput.append(
                            buildBreakdownCard(
                                bin,
                                peakBin
                                && bin.label
                                    === peakBin.label
                                && bin.amount
                                    === peakBin.amount
                            )
                        );
                    }
                );
            }
        }

        rangeButtons.forEach(
            (button) => {
                const isActive =
                    Number(
                        button.dataset.paymentRange
                    ) === range;

                button.className =
                    isActive
                        ? 'flex h-9 items-center justify-center whitespace-nowrap rounded-lg bg-white px-2 text-xs font-semibold text-slate-900 shadow-sm transition sm:text-sm dark:bg-slate-700 dark:text-white'
                        : 'flex h-9 items-center justify-center whitespace-nowrap rounded-lg px-2 text-xs font-semibold text-slate-500 transition hover:text-slate-900 sm:text-sm dark:text-slate-400 dark:hover:text-white';
            }
        );
    };

    rangeButtons.forEach(
        (button) => {
            button.addEventListener(
                'click',
                () => {
                    renderPlanner(
                        Number(
                            button.dataset.paymentRange
                        )
                    );
                }
            );
        }
    );

    if (holderSelect) {
        holderSelect.addEventListener(
            'change',
            () => {
                renderPlanner(
                    activeRange
                );
            }
        );
    }

    renderPlanner(30);
}


    /*
    |--------------------------------------------------------------------------
    | Auditlog: dynamische Suche + Detail-Popup
    |--------------------------------------------------------------------------
    */

    const auditFilters =
        document.querySelector(
            '[data-audit-filters]'
        );

    const auditRows = Array.from(
        document.querySelectorAll(
            '[data-audit-row]'
        )
    );

    if (auditFilters) {
        const searchInput =
            auditFilters.querySelector(
                '[data-audit-search]'
            );

        const userSelect =
            auditFilters.querySelector(
                '[data-audit-user]'
            );

        const actionSelect =
            auditFilters.querySelector(
                '[data-audit-action]'
            );

        const dateFromInput =
            auditFilters.querySelector(
                '[data-audit-date-from]'
            );

        const dateToInput =
            auditFilters.querySelector(
                '[data-audit-date-to]'
            );

        const resetButton =
            auditFilters.querySelector(
                '[data-audit-reset]'
            );

        const emptyRow =
            document.querySelector(
                '[data-audit-empty]'
            );

        const countLabel =
            document.querySelector(
                '[data-audit-count]'
            );


        const applyAuditFilters = () => {
            const query =
                normalizeText(
                    searchInput.value
                );

            const selectedUser =
                userSelect.value;

            const selectedAction =
                actionSelect.value;

            const dateFrom =
                dateFromInput.value;

            const dateTo =
                dateToInput.value;

            let visibleCount = 0;

            auditRows.forEach((row) => {
                const searchableText =
                    normalizeText(
                        row.dataset.search
                        || row.textContent
                    );

                const rowDate =
                    row.dataset.date
                    || '';

                const searchMatches =
                    query === ''
                    || searchableText.includes(
                        query
                    );

                const userMatches =
                    selectedUser === ''
                    || row.dataset.user
                        === selectedUser;

                const actionMatches =
                    selectedAction === ''
                    || row.dataset.action
                        === selectedAction;

                const dateFromMatches =
                    dateFrom === ''
                    || rowDate >= dateFrom;

                const dateToMatches =
                    dateTo === ''
                    || rowDate <= dateTo;

                const visible =
                    searchMatches
                    && userMatches
                    && actionMatches
                    && dateFromMatches
                    && dateToMatches;

                row.classList.toggle(
                    'hidden',
                    !visible
                );

                if (visible) {
                    visibleCount += 1;
                }
            });


            if (emptyRow) {
                emptyRow.classList.toggle(
                    'hidden',
                    visibleCount !== 0
                );
            }


            if (countLabel) {
                countLabel.textContent =
                    visibleCount
                    + ' von '
                    + auditRows.length
                    + ' geladenen Auditlog-Einträgen';
            }


            if (resetButton) {
                resetButton.classList.toggle(
                    'hidden',
                    query === ''
                    && selectedUser === ''
                    && selectedAction === ''
                    && dateFrom === ''
                    && dateTo === ''
                );
            }
        };


        [
            searchInput,
            dateFromInput,
            dateToInput,
        ].forEach((element) => {
            element.addEventListener(
                'input',
                applyAuditFilters
            );
        });


        [
            userSelect,
            actionSelect,
        ].forEach((element) => {
            element.addEventListener(
                'change',
                applyAuditFilters
            );
        });


        if (resetButton) {
            resetButton.addEventListener(
                'click',
                () => {
                    searchInput.value = '';
                    userSelect.value = '';
                    actionSelect.value = '';
                    dateFromInput.value = '';
                    dateToInput.value = '';

                    applyAuditFilters();
                    searchInput.focus();
                }
            );
        }


        applyAuditFilters();
    }


    const auditModal =
        document.querySelector(
            '[data-audit-detail-modal]'
        );

    if (
        auditModal
        && auditRows.length > 0
    ) {
        const actionOutput =
            auditModal.querySelector(
                '[data-audit-detail-action]'
            );

        const timeOutput =
            auditModal.querySelector(
                '[data-audit-detail-time]'
            );

        const userOutput =
            auditModal.querySelector(
                '[data-audit-detail-user]'
            );

        const objectOutput =
            auditModal.querySelector(
                '[data-audit-detail-object]'
            );

        const ipOutput =
            auditModal.querySelector(
                '[data-audit-detail-ip]'
            );

        const descriptionOutput =
            auditModal.querySelector(
                '[data-audit-detail-description]'
            );

        const structuredOutput =
            auditModal.querySelector(
                '[data-audit-detail-structured]'
            );

        const closeButtons =
            auditModal.querySelectorAll(
                '[data-audit-detail-close]'
            );

        let lastFocused = null;

        const fieldLabels = {
            title: 'Vertrag',
            provider: 'Anbieter',
            contract_holder_id: 'Vertragsinhaber-ID',
            contract_type_id: 'Vertragsart-ID',
            status: 'Status',
            amount: 'Betrag',
            billing_frequency: 'Abrechnung',
            first_payment_date: 'Erster Abbuchungstermin',
            username: 'Benutzername',
            display_name: 'Anzeigename',
            email: 'E-Mail',
            role_id: 'Rollen-ID',
            is_active: 'Aktiv',
            must_change_password: 'Passwortwechsel erforderlich',
            password_reset: 'Passwort zurückgesetzt',
            name: 'Name',
            description: 'Beschreibung',
            sort_order: 'Sortierung',
            contract_id: 'Vertrags-ID',
            document_name: 'Dokumentbezeichnung',
            original_filename: 'Dateiname',
            filename: 'Dateiname',
        };

        const valueLabels = {
            active: 'Aktiv',
            planned: 'Geplant',
            cancelled: 'Gekündigt',
            expired: 'Beendet',
            monthly: 'Monatlich',
            quarterly: 'Vierteljährlich',
            semiannual: 'Halbjährlich',
            annual: 'Jährlich',
            one_time: 'Einmalig',
            custom: 'Individuell',
        };

        const humanField = (key) => {
            return fieldLabels[key]
                || key.replaceAll(
                    '_',
                    ' '
                );
        };

        const humanValue = (
            key,
            value
        ) => {
            if (
                value === null
                || value === ''
            ) {
                return '–';
            }

            if (
                typeof value
                === 'boolean'
            ) {
                return value
                    ? 'Ja'
                    : 'Nein';
            }

            if (
                valueLabels[
                    String(value)
                ]
            ) {
                return valueLabels[
                    String(value)
                ];
            }

            if (
                key === 'amount'
                && !Number.isNaN(
                    Number(value)
                )
            ) {
                return formatCurrency(
                    value
                );
            }

            if (
                key.endsWith('_date')
                && /^\d{4}-\d{2}-\d{2}$/.test(
                    String(value)
                )
            ) {
                return formatDate(
                    String(value)
                );
            }

            return String(value);
        };

        const createDetailValue = (
            label,
            value,
            changed = false
        ) => {
            const item =
                document.createElement(
                    'div'
                );

            item.className =
                changed
                    ? 'rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900 dark:bg-amber-950/40'
                    : 'rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/60';

            const labelNode =
                document.createElement(
                    'div'
                );

            labelNode.className =
                'text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400';

            labelNode.textContent =
                label;

            const valueNode =
                document.createElement(
                    'div'
                );

            valueNode.className =
                'mt-1 break-words text-sm font-medium text-slate-900 dark:text-white';

            valueNode.textContent =
                value;

            item.append(
                labelNode,
                valueNode
            );

            return item;
        };

        const renderStructuredDetails = (
            rawDetails
        ) => {
            structuredOutput.replaceChildren();

            if (!rawDetails) {
                const empty =
                    document.createElement(
                        'div'
                    );

                empty.className =
                    'rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400';

                empty.textContent =
                    'Für diesen älteren Auditlog-Eintrag sind keine zusätzlichen Änderungsdetails gespeichert.';

                structuredOutput.append(
                    empty
                );

                return;
            }

            let details = null;

            try {
                details =
                    JSON.parse(
                        rawDetails
                    );
            } catch {
                details = null;
            }

            if (
                !details
                || typeof details
                    !== 'object'
            ) {
                const fallback =
                    document.createElement(
                        'pre'
                    );

                fallback.className =
                    'overflow-x-auto whitespace-pre-wrap rounded-xl bg-slate-950 p-4 text-xs text-slate-100';

                fallback.textContent =
                    rawDetails;

                structuredOutput.append(
                    fallback
                );

                return;
            }

            const before =
                details.before
                && typeof details.before
                    === 'object'
                    ? details.before
                    : null;

            const after =
                details.after
                && typeof details.after
                    === 'object'
                    ? details.after
                    : null;

            if (
                before
                || after
            ) {
                const keys =
                    Array.from(
                        new Set([
                            ...Object.keys(
                                before || {}
                            ),
                            ...Object.keys(
                                after || {}
                            ),
                        ])
                    );

                keys.forEach((key) => {
                    const oldValue =
                        before
                            ? before[key]
                            : null;

                    const newValue =
                        after
                            ? after[key]
                            : null;

                    const changed =
                        JSON.stringify(
                            oldValue
                        )
                        !==
                        JSON.stringify(
                            newValue
                        );

                    if (!changed) {
                        return;
                    }

                    const wrapper =
                        document.createElement(
                            'div'
                        );

                    wrapper.className =
                        'rounded-xl border border-slate-200 p-4 dark:border-slate-800';

                    const heading =
                        document.createElement(
                            'div'
                        );

                    heading.className =
                        'mb-3 font-semibold text-slate-900 dark:text-white';

                    heading.textContent =
                        humanField(key);

                    const grid =
                        document.createElement(
                            'div'
                        );

                    grid.className =
                        'grid gap-3 sm:grid-cols-2';

                    grid.append(
                        createDetailValue(
                            'Vorher',
                            humanValue(
                                key,
                                oldValue
                            ),
                            true
                        ),
                        createDetailValue(
                            'Nachher',
                            humanValue(
                                key,
                                newValue
                            ),
                            true
                        )
                    );

                    wrapper.append(
                        heading,
                        grid
                    );

                    structuredOutput.append(
                        wrapper
                    );
                });

                Object.entries(
                    details
                ).forEach(
                    ([key, value]) => {
                        if (
                            key === 'before'
                            || key === 'after'
                        ) {
                            return;
                        }

                        structuredOutput.append(
                            createDetailValue(
                                humanField(key),
                                humanValue(
                                    key,
                                    value
                                )
                            )
                        );
                    }
                );

                if (
                    structuredOutput.children
                        .length === 0
                ) {
                    const noChanges =
                        document.createElement(
                            'div'
                        );

                    noChanges.className =
                        'rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400';

                    noChanges.textContent =
                        'Es wurden keine Feldänderungen erkannt.';

                    structuredOutput.append(
                        noChanges
                    );
                }

                return;
            }

            Object.entries(
                details
            ).forEach(
                ([key, value]) => {
                    structuredOutput.append(
                        createDetailValue(
                            humanField(key),
                            humanValue(
                                key,
                                value
                            )
                        )
                    );
                }
            );
        };

        const closeAuditModal = () => {
            auditModal.classList.add(
                'hidden'
            );

            auditModal.classList.remove(
                'flex'
            );

            auditModal.setAttribute(
                'aria-hidden',
                'true'
            );

            setBodyModalState(false);

            if (lastFocused) {
                lastFocused.focus();
            }
        };

        const openAuditModal = (row) => {
            lastFocused =
                document.activeElement;

            actionOutput.textContent =
                row.dataset.auditActionLabel
                || 'Auditlog-Eintrag';

            timeOutput.textContent =
                row.dataset.auditTime
                || '–';

            const username =
                row.dataset.auditUsername;

            userOutput.textContent =
                (
                    row.dataset.auditUserLabel
                    || '–'
                )
                + (
                    username
                        ? ' ('
                            + username
                            + ')'
                        : ''
                );

            objectOutput.textContent =
                (
                    row.dataset.auditObject
                    || '–'
                )
                + (
                    row.dataset.auditObjectId
                        ? ' · ID '
                            + row.dataset.auditObjectId
                        : ''
                );

            ipOutput.textContent =
                row.dataset.auditIp
                || '–';

            descriptionOutput.textContent =
                row.dataset.auditDescription
                || '–';

            renderStructuredDetails(
                row.dataset.auditDetails
                || ''
            );

            auditModal.classList.remove(
                'hidden'
            );

            auditModal.classList.add(
                'flex'
            );

            auditModal.setAttribute(
                'aria-hidden',
                'false'
            );

            setBodyModalState(true);
        };

        auditRows.forEach((row) => {
            row.addEventListener(
                'click',
                () => {
                    openAuditModal(row);
                }
            );

            row.addEventListener(
                'keydown',
                (event) => {
                    if (
                        event.key === 'Enter'
                        || event.key === ' '
                    ) {
                        event.preventDefault();

                        openAuditModal(
                            row
                        );
                    }
                }
            );
        });

        closeButtons.forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    closeAuditModal
                );
            }
        );

        auditModal.addEventListener(
            'click',
            (event) => {
                if (
                    event.target
                    === auditModal
                ) {
                    closeAuditModal();
                }
            }
        );

        document.addEventListener(
            'keydown',
            (event) => {
                if (
                    event.key === 'Escape'
                    && !auditModal.classList.contains(
                        'hidden'
                    )
                ) {
                    closeAuditModal();
                }
            }
        );
    }


    document.addEventListener(
        'keydown',
        (event) => {
            if (
                event.key === 'Escape'
                && confirmModal
                && !confirmModal.classList.contains(
                    'hidden'
                )
            ) {
                const cancelButton =
                    confirmModal.querySelector(
                        '[data-confirm-cancel]'
                    );

                if (cancelButton) {
                    cancelButton.click();
                }
            }
        }
    );
})();
