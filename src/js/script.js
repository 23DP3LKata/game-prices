FooterYear();
GameSearch();
CurrencyConverter();


function FooterYear() {
    const yearElement = document.getElementById('year');
    if (yearElement) {
        yearElement.textContent = String(new Date().getFullYear());
    }
}

function GameSearch() {
    const searchInput = document.getElementById('games-search');
    const gamesTable = document.querySelector('[data-games-table]');
    if (!searchInput || !gamesTable) {
        return;
    }
    const tableRows = gamesTable.querySelectorAll('tbody tr');
    if (tableRows.length === 0) {
        return;
    }
    searchInput.addEventListener('input', () => {
        const userText = searchInput.value.trim().toLowerCase();
        tableRows.forEach((row) => {
            const titleLink = row.querySelector('.game-cell a');
            const titleText = titleLink ? titleLink.textContent.toLowerCase() : '';
            const shouldShow = userText === '' || titleText.includes(userText);
            row.style.display = shouldShow ? '' : 'none';
        });
    });
}

function CurrencyConverter() {
    const form = document.querySelector('[data-currency-form]');
    const resultBlock = document.querySelector('[data-currency-result]');

    if (!form || !resultBlock) {
        return;
    }

    const rates = {
        EUR: 1, USD: 1.16, GBP: 0.86
    };

    const renderResult = (text) => {
        if (resultBlock instanceof HTMLInputElement || resultBlock instanceof HTMLTextAreaElement) {
            resultBlock.value = text;
        } else {
            resultBlock.textContent = text;
        }
    };

    const convert = () => {
        const formData = new FormData(form);
        const rawAmount = formData.get('amount');
        const rawFrom = String(formData.get('from') || 'EUR').toUpperCase();
        const rawTo = String(formData.get('to') || 'USD').toUpperCase();

        const amount = Number(rawAmount);
        const fromCurrency = rates[rawFrom] ? rawFrom : 'EUR';
        const toCurrency = rates[rawTo] ? rawTo : 'EUR';

        if (Number.isNaN(amount) || amount < 0) {
            renderResult('Amount must be positive.');
            return;
        }

        const fromRate = rates[fromCurrency];
        const toRate = rates[toCurrency];

        const amountInEur = fromCurrency === 'EUR' ? amount : amount / fromRate;
        const result = Number((amountInEur * toRate).toFixed(2));

        renderResult(`${amount} ${fromCurrency} = ${result} ${toCurrency}`);
    };
    form.addEventListener('input', convert);
    form.addEventListener('change', convert);
    convert();
}