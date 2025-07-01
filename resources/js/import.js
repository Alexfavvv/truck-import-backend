
import axios from 'axios';

const importForm = document.getElementById('importForm');
const statusAreaContainer = document.getElementById('statusArea');
const statusMessageElement = document.getElementById('statusMessage');
const progressBarContainer = document.getElementById('progressBarContainer');
const progressBar = document.getElementById('progressBar');
const importDetails = document.getElementById('importDetails');
const processedRowsSpan = document.getElementById('processedRows');
const totalRowsSpan = document.getElementById('totalRows');
const errorListContainer = document.getElementById('errorList');
const failuresList = document.getElementById('failuresList');

let pollingIntervalId = null;
const POLLING_INTERVAL_MS = 2000;

/**
 * Останавливает текущий опрос статуса.
 */
function stopPolling() {
    if (pollingIntervalId) {
        clearInterval(pollingIntervalId);
        pollingIntervalId = null;
        console.log('Опрос статуса остановлен.');
    }
}

/**
 * Отправляет запрос на получение статуса импорта.
 * Использует текущий ID пользователя для запроса.
 */
async function fetchImportStatus() {
    try {
        const url = `/admin/products/import-status`;

        const response = await axios.get(url, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const data = response.data;

        statusMessageElement.textContent = data.message || 'Обновление статуса...';
        statusMessageElement.className = 'fw-bold';

        if (data.status === 'processing') {
            statusMessageElement.style.color = 'blue';
            progressBarContainer.style.display = 'block';
            importDetails.style.display = 'block';

            const percentage = data.percentage || 0;
            progressBar.style.width = `${percentage}%`;
            progressBar.setAttribute('aria-valuenow', percentage);
            progressBar.textContent = `${percentage}%`;

            processedRowsSpan.textContent = data.processedRows || 0;
            totalRowsSpan.textContent = data.totalRows || 'Ожидание...';

        } else if (data.status === 'success') {
            statusMessageElement.style.color = 'green';
            progressBarContainer.style.display = 'none';
            importDetails.style.display = 'none';
            errorListContainer.style.display = 'none';
            stopPolling();
        } else if (data.status === 'warning') {
            statusMessageElement.style.color = 'orange';
            progressBarContainer.style.display = 'none';
            importDetails.style.display = 'none';
            displayFailures(data.failures);
            stopPolling();
        } else if (data.status === 'error') {
            statusMessageElement.style.color = 'red';
            progressBarContainer.style.display = 'none';
            importDetails.style.display = 'none';
            displayFailures(data.failures);
            stopPolling();
        } else if (data.status === 'not_found') {
            statusMessageElement.textContent = 'Ожидание Job\'а или Job завершен/не найден. Повторная попытка...';
            statusMessageElement.style.color = 'gray';
        }

    } catch (error) {
        console.error('Ошибка при получении статуса импорта:', error);
        if (error.response && error.response.status === 404) {
            statusMessageElement.textContent = 'Импорт завершен или Job не найден. Проверьте логи.';
            statusMessageElement.style.color = 'gray';
            stopPolling();
        } else {
            statusMessageElement.textContent = 'Ошибка связи с сервером. Повторная попытка...';
            statusMessageElement.style.color = 'red';
        }
    }
}

/**
 * Начинает опрос статуса Job'а.
 */
function startPolling() {
    stopPolling();
    fetchImportStatus(); // Сразу запрашиваем статус
    pollingIntervalId = setInterval(fetchImportStatus, POLLING_INTERVAL_MS); // Затем по интервалу
}

function displayFailures(failures) {
    failuresList.innerHTML = '';
    if (failures && failures.length > 0) {
        errorListContainer.style.display = 'block';
        failures.forEach(failure => {
            const li = document.createElement('li');
            let msg = `Строка ${failure.row}, атрибут "${failure.attribute || 'N/A'}": `;
            if (Array.isArray(failure.errors)) {
                msg += failure.errors.join(', ');
            } else if (typeof failure.errors === 'string') {
                msg += failure.errors;
            }
            li.textContent = msg;
            failuresList.appendChild(li);
        });
    } else {
        errorListContainer.style.display = 'none';
    }
}

importForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    statusAreaContainer.style.display = 'block';
    statusMessageElement.textContent = 'Файл загружается...';
    statusMessageElement.style.color = 'black';
    progressBarContainer.style.display = 'none';
    importDetails.style.display = 'none';
    errorListContainer.style.display = 'none';
    failuresList.innerHTML = '';
    stopPolling();

    const formData = new FormData(importForm);

    try {
        const response = await axios.post(importForm.action, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Accept': 'application/json'
            }
        });

        if (response.data.success) {
            statusMessageElement.textContent = response.data.message + ' Начат импорт. Ожидаем статус...';
            statusMessageElement.style.color = 'blue';
            startPolling();
        } else {
            statusMessageElement.textContent = 'Ошибка загрузки файла: ' + response.data.message;
            statusMessageElement.style.color = 'red';
        }

    } catch (error) {
        console.error('Ошибка отправки формы:', error);
        let errorMessage = 'Неизвестная ошибка при загрузке файла.';
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }
        statusMessageElement.textContent = 'Ошибка: ' + errorMessage;
        statusMessageElement.style.color = 'red';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    progressBarContainer.style.display = 'none';
    importDetails.style.display = 'none';
    errorListContainer.style.display = 'none';
});
