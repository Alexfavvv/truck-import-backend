(() => {
    const root = document.querySelector('[data-dashboard-chart]');
    const dataNode = document.getElementById('admin-dashboard-chart-data');
    if (!root || !dataNode) return;

    const canvas = root.querySelector('canvas');
    const tooltip = root.querySelector('.admin-dashboard__chart-tooltip');
    const buttons = document.querySelectorAll('[data-dashboard-series]');
    const sparklines = document.querySelectorAll('[data-dashboard-sparkline]');
    const chart = JSON.parse(dataNode.textContent);
    const context = canvas.getContext('2d');
    if (!context) return;

    const number = new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 2 });
    const compact = new Intl.NumberFormat('ru-RU', { notation: 'compact', maximumFractionDigits: 1 });
    let series = 'orders';
    let points = [];
    let hovered = -1;

    function formatValue(value) {
        return `${number.format(value)}${series === 'revenue' ? ' ₽' : ' заказов'}`;
    }

    function draw() {
        const width = Math.max(root.clientWidth, 1);
        const height = Math.max(root.clientHeight, 260);
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);

        const left = width < 520 ? 42 : 64;
        const right = 16;
        const top = 20;
        const bottom = 35;
        const plotWidth = width - left - right;
        const plotHeight = height - top - bottom;
        const values = chart[series] || [];
        const maximum = Math.max(1, ...values) * 1.12;

        context.font = '12px Gilroy-Regular, sans-serif';
        context.textBaseline = 'middle';
        context.strokeStyle = '#e8eaee';
        context.fillStyle = '#79808a';
        context.lineWidth = 1;

        for (let tick = 0; tick <= 4; tick += 1) {
            const y = top + plotHeight * tick / 4;
            const value = maximum * (4 - tick) / 4;
            context.beginPath();
            context.moveTo(left, y);
            context.lineTo(width - right, y);
            context.stroke();
            context.textAlign = 'right';
            context.fillText(compact.format(value), left - 9, y);
        }

        const labelStep = Math.max(1, Math.ceil(chart.labels.length / Math.max(2, Math.floor(plotWidth / 85))));
        chart.labels.forEach((label, index) => {
            if (index % labelStep !== 0 && index !== chart.labels.length - 1) return;
            const x = left + plotWidth * index / Math.max(1, chart.labels.length - 1);
            context.textAlign = index === 0 ? 'left' : (index === chart.labels.length - 1 ? 'right' : 'center');
            context.fillText(label, x, height - 14);
        });

        points = values.map((value, index) => ({
            x: left + plotWidth * index / Math.max(1, values.length - 1),
            y: top + plotHeight * (1 - value / maximum),
            value,
            label: chart.labels[index],
        }));
        if (points.length === 0) return;

        const color = series === 'orders' ? '#c9ac37' : '#30343b';
        const gradient = context.createLinearGradient(0, top, 0, top + plotHeight);
        gradient.addColorStop(0, series === 'orders' ? 'rgba(201,172,55,.22)' : 'rgba(48,52,59,.16)');
        gradient.addColorStop(1, 'rgba(255,255,255,0)');

        context.beginPath();
        context.moveTo(points[0].x, top + plotHeight);
        points.forEach((point) => context.lineTo(point.x, point.y));
        context.lineTo(points[points.length - 1].x, top + plotHeight);
        context.closePath();
        context.fillStyle = gradient;
        context.fill();

        context.beginPath();
        points.forEach((point, index) => {
            if (index === 0) context.moveTo(point.x, point.y);
            else context.lineTo(point.x, point.y);
        });
        context.strokeStyle = color;
        context.lineWidth = 2.5;
        context.lineJoin = 'round';
        context.stroke();

        points.forEach((point, index) => {
            context.beginPath();
            context.arc(point.x, point.y, index === hovered ? 5.5 : 3, 0, Math.PI * 2);
            context.fillStyle = '#fff';
            context.fill();
            context.lineWidth = 2;
            context.strokeStyle = color;
            context.stroke();
        });
    }

    function showTooltip(event) {
        if (points.length === 0) return;
        const bounds = canvas.getBoundingClientRect();
        const x = event.clientX - bounds.left;
        hovered = points.reduce((closest, point, index) =>
            Math.abs(point.x - x) < Math.abs(points[closest].x - x) ? index : closest, 0);
        draw();
        const point = points[hovered];
        tooltip.textContent = `${point.label}: ${formatValue(point.value)}`;
        tooltip.hidden = false;
        const tooltipWidth = tooltip.offsetWidth;
        tooltip.style.left = `${Math.min(Math.max(point.x - tooltipWidth / 2, 8), root.clientWidth - tooltipWidth - 8)}px`;
        tooltip.style.top = `${Math.max(8, point.y - 46)}px`;
    }

    function drawSparklines() {
        const colors = {
            orders: '#c9ac37',
            revenue: '#30343b',
            clients: '#4b8270',
            paid_invoices: '#74818c',
        };
        sparklines.forEach((sparkline) => {
            const values = chart[sparkline.dataset.dashboardSparkline] || [];
            const sparkContext = sparkline.getContext('2d');
            if (!sparkContext || values.length === 0) return;
            const width = Math.max(sparkline.parentElement.clientWidth, 1);
            const height = 30;
            const ratio = Math.min(window.devicePixelRatio || 1, 2);
            sparkline.width = Math.round(width * ratio);
            sparkline.height = Math.round(height * ratio);
            sparkContext.setTransform(ratio, 0, 0, ratio, 0, 0);
            sparkContext.clearRect(0, 0, width, height);
            const maximum = Math.max(1, ...values);
            sparkContext.beginPath();
            values.forEach((value, index) => {
                const x = 2 + (width - 4) * index / Math.max(1, values.length - 1);
                const y = 26 - value / maximum * 21;
                if (index === 0) sparkContext.moveTo(x, y);
                else sparkContext.lineTo(x, y);
            });
            sparkContext.strokeStyle = colors[sparkline.dataset.dashboardSparkline] || '#30343b';
            sparkContext.lineWidth = 2;
            sparkContext.lineJoin = 'round';
            sparkContext.lineCap = 'round';
            sparkContext.stroke();
        });
    }

    buttons.forEach((button) => button.addEventListener('click', () => {
        series = button.dataset.dashboardSeries;
        hovered = -1;
        tooltip.hidden = true;
        buttons.forEach((item) => {
            const active = item === button;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-pressed', String(active));
        });
        draw();
    }));

    canvas.addEventListener('pointermove', showTooltip);
    canvas.addEventListener('pointerleave', () => {
        hovered = -1;
        tooltip.hidden = true;
        draw();
    });

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(() => {
            draw();
            drawSparklines();
        });
        observer.observe(root);
    } else {
        window.addEventListener('resize', () => {
            draw();
            drawSparklines();
        });
    }
    draw();
    drawSparklines();
})();
