(() => {
    const root = document.querySelector('[data-dashboard-chart]');
    const dataNode = document.getElementById('admin-dashboard-chart-data');
    if (!root || !dataNode) return;

    const canvas = root.querySelector('canvas');
    const tooltip = root.querySelector('.admin-dashboard__chart-tooltip');
    const buttons = document.querySelectorAll('[data-dashboard-series]');
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

    function traceSmoothLine(target, linePoints, continuePath = false) {
        if (linePoints.length === 0) return;
        if (!continuePath) target.moveTo(linePoints[0].x, linePoints[0].y);

        for (let index = 1; index < linePoints.length; index += 1) {
            const previous = linePoints[index - 1];
            const current = linePoints[index];
            const beforePrevious = linePoints[Math.max(0, index - 2)];
            const afterCurrent = linePoints[Math.min(linePoints.length - 1, index + 1)];
            const firstControl = {
                x: previous.x + (current.x - beforePrevious.x) / 6,
                y: previous.y + (current.y - beforePrevious.y) / 6,
            };
            const secondControl = {
                x: current.x - (afterCurrent.x - previous.x) / 6,
                y: current.y - (afterCurrent.y - previous.y) / 6,
            };

            target.bezierCurveTo(
                firstControl.x,
                firstControl.y,
                secondControl.x,
                secondControl.y,
                current.x,
                current.y,
            );
        }
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
        gradient.addColorStop(0, series === 'orders' ? 'rgba(201,172,55,.2)' : 'rgba(48,52,59,.14)');
        gradient.addColorStop(1, 'rgba(255,255,255,0)');

        context.beginPath();
        context.moveTo(points[0].x, top + plotHeight);
        context.lineTo(points[0].x, points[0].y);
        traceSmoothLine(context, points, true);
        context.lineTo(points[points.length - 1].x, top + plotHeight);
        context.closePath();
        context.fillStyle = gradient;
        context.fill();

        context.beginPath();
        traceSmoothLine(context, points);
        context.strokeStyle = color;
        context.lineWidth = 2.5;
        context.lineJoin = 'round';
        context.stroke();

        points.forEach((point, index) => {
            context.beginPath();
            context.arc(point.x, point.y, index === hovered ? 4.5 : 2, 0, Math.PI * 2);
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

    if ('ResizeObserver' in window) new ResizeObserver(draw).observe(root);
    else window.addEventListener('resize', draw);
    draw();
})();
