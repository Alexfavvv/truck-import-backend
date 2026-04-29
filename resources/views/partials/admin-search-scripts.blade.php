<script>
    const STORAGE_KEY_DELETED = 'adminSearchDeletedByContext';
    const STORAGE_KEY_LAST_QUERY = 'adminSearchLastQuery';

    window.adminSearchToggleAll = function (masterCheckbox) {
        const table = masterCheckbox.closest('table');
        if (!table) return;
        const checkboxes = table.querySelectorAll('tbody input[type="checkbox"][name="items[]"]');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
        if (typeof window.adminSearchUpdateSummary === 'function') {
            window.adminSearchUpdateSummary();
        }
    };

    function getStorageKey(wrapper) {
        if (!wrapper || !wrapper.dataset.searchContext) return null;
        const ctx = wrapper.dataset.searchContext;
        if (ctx === 'results') {
            const q = (wrapper.dataset.currentQuery || '').trim();
            return 'results::' + (q || '__empty__');
        }
        if (ctx === 'history') return 'history';
        return null;
    }

    (function () {
        const section = document.querySelector('.admin-search');
        if (!section) return;

        const tables = document.querySelectorAll('.admin-search__table');
        const currentResultIdsRaw = section.dataset.currentResultIds ? section.dataset.currentResultIds : '';
        const currentResultIds = new Set(currentResultIdsRaw.split(',').map(Number).filter(Number.isInteger));
        const currentQuery = section.dataset.currentQuery ? String(section.dataset.currentQuery).trim() : '';

        if (tables.length) {
            try {
                let raw = sessionStorage.getItem(STORAGE_KEY_DELETED);
                let byKey = raw ? JSON.parse(raw) : {};
                if (typeof byKey !== 'object' || byKey === null) byKey = {};

                const lastQuery = sessionStorage.getItem(STORAGE_KEY_LAST_QUERY);
                const isNewSearch = currentQuery !== lastQuery;

                if (isNewSearch) {
                    sessionStorage.setItem(STORAGE_KEY_LAST_QUERY, currentQuery);
                    if (currentResultIds.size > 0 && byKey.history && Array.isArray(byKey.history)) {
                        byKey.history = byKey.history.filter(id => !currentResultIds.has(Number(id)));
                        sessionStorage.setItem(STORAGE_KEY_DELETED, JSON.stringify(byKey));
                    }
                }

                document.querySelectorAll('.admin-search__row[data-product-id]').forEach(row => {
                    const wrapper = row.closest('[data-search-context]');
                    const key = getStorageKey(wrapper);
                    if (!key || !byKey[key] || !Array.isArray(byKey[key])) return;
                    const set = new Set(byKey[key].map(Number));
                    const id = parseInt(row.dataset.productId, 10);
                    if (!Number.isInteger(id) || !set.has(id)) return;
                    if (key === 'history' && currentResultIds.has(id)) return;
                    row.remove();
                });
            } catch (e) {
                sessionStorage.removeItem(STORAGE_KEY_DELETED);
                sessionStorage.removeItem(STORAGE_KEY_LAST_QUERY);
            }
        }

        const formatCurrency = value => {
            try {
                return new Intl.NumberFormat('ru-RU', {
                    style: 'currency',
                    currency: 'RUB',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(value);
            } catch (e) {
                return value.toFixed(2) + ' ₽';
            }
        };

        let updateSummary = () => {};

        const updateRowTotal = row => {
            const qtyInput = row.querySelector('.js-item-qty');
            const totalEl = row.querySelector('.js-item-total');
            const priceEl = row.querySelector('.js-item-price');
            if (!qtyInput || !totalEl || !priceEl) return;

            const rawPrice = parseFloat(priceEl.dataset.raw || '0') || 0;
            let qty = parseInt(qtyInput.value || '1', 10);
            if (qty < 1 || Number.isNaN(qty)) {
                qty = 1;
                qtyInput.value = '1';
            }

            const sum = rawPrice * qty;
            totalEl.textContent = formatCurrency(sum);
        };

        const attachRowHandlers = row => {
            const qtyInput = row.querySelector('.js-item-qty');
            if (qtyInput) {
                qtyInput.addEventListener('input', () => {
                    updateRowTotal(row);
                    updateSummary();
                });
                qtyInput.addEventListener('change', () => {
                    updateRowTotal(row);
                    updateSummary();
                });
            }

            const qtyButtons = row.querySelectorAll('[data-qty-action]');
            qtyButtons.forEach(button => {
                button.addEventListener('click', () => {
                    if (!qtyInput) return;
                    let qty = parseInt(qtyInput.value || '1', 10);
                    if (Number.isNaN(qty) || qty < 1) {
                        qty = 1;
                    }

                    if (button.dataset.qtyAction === 'plus') {
                        qty += 1;
                    } else if (button.dataset.qtyAction === 'minus' && qty > 1) {
                        qty -= 1;
                    }

                    qtyInput.value = String(qty);
                    updateRowTotal(row);
                    updateSummary();
                });
            });

            if (qtyInput) {
                updateRowTotal(row);
            }

            const cb = row.querySelector('input[type="checkbox"][name="items[]"]');
            if (cb) {
                cb.addEventListener('change', () => updateSummary());
            }
        };

        window.adminSearchAttachRowHandlers = attachRowHandlers;

        if (tables.length) {
            tables.forEach(table => {
                table.querySelectorAll('tbody tr').forEach(row => attachRowHandlers(row));
            });
        }

        const getSelectedRows = () => {
            const checkboxes = document.querySelectorAll('.admin-search__row input[type="checkbox"][name="items[]"]:checked');
            return Array.from(checkboxes).map(cb => cb.closest('.admin-search__row')).filter(Boolean);
        };

        const deleteSelected = () => {
            const rows = getSelectedRows();
            if (rows.length === 0) return;
            const wrapper = rows[0].closest('[data-search-context]');
            const key = getStorageKey(wrapper);
            const toDelete = [];
            rows.forEach(row => {
                const id = row.dataset.productId ? parseInt(row.dataset.productId, 10) : null;
                if (Number.isInteger(id)) {
                    toDelete.push(id);
                }
                row.remove();
            });
            if (toDelete.length > 0 && key) {
                try {
                    const raw = sessionStorage.getItem(STORAGE_KEY_DELETED);
                    const byKey = raw ? JSON.parse(raw) : {};
                    if (typeof byKey !== 'object' || byKey === null) byKey = {};
                    const current = Array.isArray(byKey[key]) ? byKey[key] : [];
                    byKey[key] = [...new Set([...current, ...toDelete])];
                    sessionStorage.setItem(STORAGE_KEY_DELETED, JSON.stringify(byKey));
                } catch (e) {
                    const byKey = {};
                    byKey[key] = toDelete;
                    sessionStorage.setItem(STORAGE_KEY_DELETED, JSON.stringify(byKey));
                }
            }
            updateSummary();
        };

        const getSelectedItems = () => {
            const rows = getSelectedRows();
            return rows.map(row => {
                const productId = row.dataset.productId ? parseInt(row.dataset.productId, 10) : null;
                const qtyInput = row.querySelector('.js-item-qty');
                let quantity = qtyInput ? parseInt(qtyInput.value || '1', 10) : 1;
                if (Number.isNaN(quantity) || quantity < 1) {
                    quantity = 1;
                }
                return { row, productId, quantity };
            }).filter(item => Number.isInteger(item.productId) && item.productId > 0);
        };

        updateSummary = () => {
            const qtyEl = document.querySelector('[data-admin-search-summary-qty]');
            const totalEl = document.querySelector('[data-admin-search-summary-total]');
            if (!qtyEl || !totalEl) return;

            const rows = getSelectedRows();
            let totalQty = 0;
            let totalSum = 0;

            rows.forEach(row => {
                const qtyInput = row.querySelector('.js-item-qty');
                const priceEl = row.querySelector('.js-item-price');
                if (!priceEl) return;

                const rawPrice = parseFloat(priceEl.dataset.raw || '0') || 0;
                let qty = qtyInput ? parseInt(qtyInput.value || '1', 10) : 1;
                if (Number.isNaN(qty) || qty < 1) {
                    qty = 1;
                }
                totalQty += qty;
                totalSum += rawPrice * qty;
            });

            qtyEl.textContent = String(totalQty);
            totalEl.textContent = formatCurrency(totalSum);
        };

        window.adminSearchUpdateSummary = updateSummary;
        updateSummary();

        const bar = document.querySelector('.admin-search__actions-bar');
        const cartAddUrlDefault = bar?.getAttribute('data-cart-add-url') || '';

        const addToCartViaAdmin = async () => {
            const selected = getSelectedItems();
            if (!selected.length) {
                alert('Не выбраны товары.');
                return;
            }

            const url = bar?.getAttribute('data-cart-add-url') || cartAddUrlDefault;
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : null;

            const payload = {
                items: selected.map(item => ({
                    product_id: item.productId,
                    quantity: item.quantity,
                })),
            };

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    let message = data.message || 'Ошибка добавления в корзину (код ' + response.status + ')';
                    if (Array.isArray(data.errors) && data.errors.length) {
                        message = data.errors.join('\n');
                    }
                    alert(message);
                    return;
                }

                alert(data.message || 'Товары добавлены в корзину.');
            } catch (e) {
                alert('Сетевая ошибка при добавлении в корзину.');
            }
        };

        const sendRequestStub = () => {
            alert('Отправка запроса пока не реализована.');
        };

        document.querySelectorAll('[data-admin-search-action]').forEach(button => {
            button.addEventListener('click', () => {
                const action = button.dataset.adminSearchAction;
                (async () => {
                    if (action === 'delete-selected') {
                        deleteSelected();
                    } else if (action === 'add-to-cart') {
                        await addToCartViaAdmin();
                    } else if (action === 'send-request') {
                        sendRequestStub();
                    }
                })();
            });
        });

        const uploadUrl = section.dataset.uploadListUrl;
        const fileInput = document.getElementById('adminSearchUploadListInput');
        const uploadBtn = document.getElementById('adminSearchUploadListBtn');
        const helpBtn = document.getElementById('adminSearchUploadHelpBtn');
        const helpDialog = document.getElementById('adminSearchUploadHelpDialog');

        if (helpBtn && helpDialog && typeof helpDialog.showModal === 'function') {
            helpBtn.addEventListener('click', () => helpDialog.showModal());
            helpDialog.addEventListener('click', (e) => {
                if (e.target === helpDialog) {
                    helpDialog.close();
                }
            });
        }

        const getResultsTbody = () => {
            const w = document.querySelector('.admin-search__table-wrapper[data-search-context="results"]');
            return w ? w.querySelector('tbody') : null;
        };

        const ensureResultsTable = () => {
            const body = document.querySelector('.admin-search__results-body');
            if (!body) return null;
            let tbody = getResultsTbody();
            if (tbody) {
                const wrap = tbody.closest('.admin-search__table-wrapper');
                if (wrap) {
                    wrap.dataset.currentQuery = section.dataset.currentQuery || '';
                }
                return tbody;
            }

            const tmpl = document.getElementById('admin-search-results-table-shell');
            if (!tmpl || !tmpl.content) return null;
            body.innerHTML = '';
            const node = tmpl.content.cloneNode(true);
            const wrapper = node.querySelector('.admin-search__table-wrapper');
            if (wrapper) {
                wrapper.dataset.currentQuery = section.dataset.currentQuery || '';
            }
            body.appendChild(node);
            return getResultsTbody();
        };

        const mergeRowHtml = (tbody, rowHtml) => {
            const tpl = document.createElement('template');
            tpl.innerHTML = rowHtml.trim();
            const tr = tpl.content.querySelector('tr.admin-search__row');
            if (!tr) return;
            const id = tr.dataset.productId;
            if (!id) return;
            const existing = tbody.querySelector('tr.admin-search__row[data-product-id="' + id + '"]');
            if (existing) {
                const newQtyInput = tr.querySelector('.js-item-qty');
                let addQty = newQtyInput ? parseInt(newQtyInput.value || '1', 10) : 1;
                if (Number.isNaN(addQty) || addQty < 1) addQty = 1;
                const inp = existing.querySelector('.js-item-qty');
                let cur = inp ? parseInt(inp.value || '1', 10) : 1;
                if (Number.isNaN(cur) || cur < 1) cur = 1;
                if (inp) inp.value = String(cur + addQty);
                const cb = existing.querySelector('input[type="checkbox"][name="items[]"]');
                if (cb) cb.checked = true;
                updateRowTotal(existing);
                return;
            }
            const imported = document.importNode(tr, true);
            tbody.appendChild(imported);
            attachRowHandlers(imported);
        };

        const runUpload = async (file) => {
            if (!uploadUrl || !file) return;
            const form = new FormData();
            form.append('file', file);
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : null;
            try {
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    credentials: 'same-origin',
                    body: form,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    let msg = data.message || 'Не удалось обработать файл.';
                    if (data.errors && data.errors.file) {
                        msg = Array.isArray(data.errors.file) ? data.errors.file[0] : String(data.errors.file);
                    }
                    alert(msg);
                    return;
                }
                const rows = data.rows_html || [];
                if (!rows.length) {
                    let t = 'В файле нет строк с артикулами или совпадений в каталоге не найдено.';
                    if (Array.isArray(data.not_found) && data.not_found.length) {
                        t += '\n\nНе найдены артикулы:\n' + data.not_found.join('\n');
                    }
                    alert(t);
                    return;
                }
                const tbody = ensureResultsTable();
                if (!tbody) {
                    alert('Не удалось отобразить результаты.');
                    return;
                }
                rows.forEach(html => mergeRowHtml(tbody, html));
                updateSummary();
                if (Array.isArray(data.not_found) && data.not_found.length) {
                    alert('Часть артикулов не найдена (' + data.not_found.length + '):\n' + data.not_found.join(', '));
                }
            } catch (e) {
                alert('Ошибка сети при загрузке файла.');
            }
        };

        if (uploadBtn && fileInput) {
            uploadBtn.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => {
                const f = fileInput.files && fileInput.files[0];
                fileInput.value = '';
                if (f) runUpload(f);
            });
        }
    })();
</script>
