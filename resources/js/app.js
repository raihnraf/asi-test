import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('priceInput', () => ({
        format(value) {
            const sanitized = String(value).replace(/[^\d,]/g, '');

            if (sanitized === '') {
                return '';
            }

            const [wholePart, decimalPart = ''] = sanitized.split(',');
            const normalizedWholePart = wholePart.replace(/\./g, '').replace(/^0+(?=\d)/, '');
            const groupedWholePart = (normalizedWholePart === '' ? '0' : normalizedWholePart)
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            if (decimalPart === '') {
                return groupedWholePart;
            }

            return `${groupedWholePart},${decimalPart.slice(0, 2)}`;
        },
    }));
});

Alpine.start();
