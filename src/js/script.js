const translations = window.translations || {
    en: {
        'nav.brand': 'Game Prices',
        'nav.games': 'Games',
        'nav.about': 'About us',
        'nav.services': 'Services',
        'nav.weather': 'Weather',
        'mode.dark': 'Dark Mode',
        'mode.light': 'Light Mode',
        'footer.notice': '&copy; {year} Games Prices. Not affiliated with Valve or Steam. All times are UTC.',
        'games.heading': 'Premium Games We Track',
        'games.subheading': 'We monitor paid releases across Steam, console storefronts, SteamDB, AppGames, and IsThereAnyDeal to surface the best live prices.',
        'games.searchLabel': 'Search',
        'games.searchPlaceholder': 'Search games',
        'games.genreLabel': 'Genre',
        'games.genre.all': 'All genres',
        'games.genre.action': 'Action',
        'games.genre.rpg': 'RPG',
        'games.genre.shooter': 'Shooter',
        'games.genre.adventure': 'Adventure',
        'games.genre.strategy': 'Strategy',
        'games.genre.sports': 'Sports',
        'games.genre.horror': 'Horror',
        'games.genre.metroidvania': 'Metroidvania',
        'games.platformLabel': 'Platform',
        'games.platform.all': 'All platforms',
        'games.platform.pc': 'PC',
        'games.platform.playstation': 'PlayStation',
        'games.platform.xbox': 'Xbox',
        'games.sortLabel': 'Sort by',
        'games.sort.featured': 'Featured order',
        'games.sort.priceAsc': 'Price: Low to High',
        'games.sort.priceDesc': 'Price: High to Low',
        'games.sort.players': 'Players Online',
        'games.sort.peak': '24h Peak',
        'games.caption': 'Current prices across Steam, PlayStation, Xbox, and AppGames',
        'games.empty': 'No games match your filters yet. Try adjusting the search or filters.',
        'games.results': 'Showing {shown} of {total} games',
        'games.disclaimer': 'Data refreshed on 2 December 2025. Prices reflect your selected currency and may fluctuate with platform promotions and regional taxes.',
        'games.chartUntitled': 'Untitled chart',
        'games.chartAria': 'Price history for {title} in {currency}',
        'weather.heading': 'Check the weather before you play',
        'weather.subheading': 'Enter a city to pull live conditions from Open-Meteo so you know whether it is a couch day or an outdoor break.',
        'weather.inputLabel': 'City',
        'weather.inputPlaceholder': 'Search city',
        'weather.submit': 'Get weather',
        'weather.status.loading': 'Looking up weather for {city}...',
        'weather.error.empty': 'Enter a city to see the forecast.',
        'weather.error.lookup': 'Could not contact the weather service. Try again in a moment.',
        'weather.error.notFound': 'We could not find weather data for {city}.',
        'weather.error.unavailable': 'Weather data is unavailable right now. Please try later.',
        'weather.meta': '{condition} • Updated {time}',
        'weather.metrics.temperature': 'Temperature',
        'weather.metrics.feelsLike': 'Feels like',
        'weather.metrics.humidity': 'Humidity',
        'weather.metrics.wind': 'Wind',
        'weather.condition.unknown': 'Current conditions unavailable'
    },
    lv: {
        'nav.brand': 'Game Prices',
        'nav.games': 'Spēles',
        'nav.about': 'Par mums',
        'nav.services': 'Pakalpojumi',
        'nav.weather': 'Laikapstākļi',
        'mode.dark': 'Tumšais režīms',
        'mode.light': 'Gaišais režīms',
        'footer.notice': '&copy; {year} Game Prices. Nav saistīts ar Valve vai Steam. Visi laiki norādīti pēc UTC.',
        'games.heading': 'Premium spēles mūsu katalogā',
        'games.subheading': 'Mēs sekojam jaunākajām cenām Steam, konsolēs, SteamDB, AppGames un IsThereAnyDeal, lai atrastu labākos piedāvājumus.',
        'games.searchLabel': 'Meklēt',
        'games.searchPlaceholder': 'Meklēt spēles',
        'games.genreLabel': 'Žanrs',
        'games.genre.all': 'Visi žanri',
        'games.genre.action': 'Asa sižeta',
        'games.genre.rpg': 'RPG',
        'games.genre.shooter': 'Šāvēji',
        'games.genre.adventure': 'Piedzīvojumu',
        'games.genre.strategy': 'Stratēģija',
        'games.genre.sports': 'Sports',
        'games.genre.horror': 'Šausmu',
        'games.genre.metroidvania': 'Metroidvania',
        'games.platformLabel': 'Platforma',
        'games.platform.all': 'Visas platformas',
        'games.platform.pc': 'PC',
        'games.platform.playstation': 'PlayStation',
        'games.platform.xbox': 'Xbox',
        'games.sortLabel': 'Kārtot pēc',
        'games.sort.featured': 'Ieteiktais kārtojums',
        'games.sort.priceAsc': 'Cena: no zemākās',
        'games.sort.priceDesc': 'Cena: no augstākās',
        'games.sort.players': 'Aktīvie spēlētāji',
        'games.sort.peak': '24h maksimums',
        'games.caption': 'Aktuālās cenas Steam, PlayStation, Xbox un AppGames',
        'games.empty': 'Nav rezultātu. Pamēģini mainīt meklēšanu vai filtrus.',
        'games.results': 'Rādām {shown} no {total} spēlēm',
        'games.disclaimer': 'Dati atjaunoti 2025. gada 2. decembrī. Cenas mainās atkarībā no izvēlētās valūtas, akcijām un nodokļiem.',
        'games.chartUntitled': 'Grafiks bez nosaukuma',
        'games.chartAria': '{title} cenu vēstures grafiks ({currency})',
        'weather.heading': 'Pārbaudi laikapstākļus pirms spēlēšanas',
        'weather.subheading': 'Ieraksti pilsētu un iegūsti aktuālos datus no Open-Meteo.',
        'weather.inputLabel': 'Pilsēta',
        'weather.inputPlaceholder': 'Meklēt pilsētu',
        'weather.submit': 'Skatīt laikapstākļus',
        'weather.status.loading': 'Meklējam laikapstākļus vietai {city}...',
        'weather.error.empty': 'Ievadi pilsētas nosaukumu, lai redzētu prognozi.',
        'weather.error.lookup': 'Neizdevās sazināties ar laikapstākļu servisu. Mēģini vēlreiz.',
        'weather.error.notFound': 'Neatradām datus pilsētai {city}.',
        'weather.error.unavailable': 'Laikapstākļu dati šobrīd nav pieejami. Lūdzu, mēģini vēlāk.',
        'weather.meta': '{condition} • Atjaunots {time}',
        'weather.metrics.temperature': 'Temperatūra',
        'weather.metrics.feelsLike': 'Jūtas kā',
        'weather.metrics.humidity': 'Mitrums',
        'weather.metrics.wind': 'Vējš',
        'weather.condition.unknown': 'Nav informācijas par apstākļiem'
    }
};

if (!window.translations) {
    window.translations = translations;
}

const currencyMeta = {
    USD: { rate: 1, symbol: '$', decimals: 2 },
    EUR: { rate: 0.92, symbol: '€', decimals: 2 },
    GBP: { rate: 0.79, symbol: '£', decimals: 2 }
};

const fallbackLanguage = 'en';

const chartDataRegistry = new Map();
const convertiblePriceElements = [];
let chartResizeTimer;
let currentCurrency = 'USD';
let currentLanguage = fallbackLanguage;
let gamesTableState = null;
let currentGamesShown = 0;
let weatherState = null;

const weatherCodeDescriptions = {
    en: {
        0: 'Clear sky',
        1: 'Mainly clear',
        2: 'Partly cloudy',
        3: 'Overcast',
        45: 'Fog',
        48: 'Depositing rime fog',
        51: 'Light drizzle',
        53: 'Moderate drizzle',
        55: 'Dense drizzle',
        56: 'Light freezing drizzle',
        57: 'Dense freezing drizzle',
        61: 'Light rain',
        63: 'Moderate rain',
        65: 'Heavy rain',
        66: 'Light freezing rain',
        67: 'Heavy freezing rain',
        71: 'Light snowfall',
        73: 'Moderate snowfall',
        75: 'Heavy snowfall',
        77: 'Snow grains',
        80: 'Light rain showers',
        81: 'Moderate rain showers',
        82: 'Violent rain showers',
        85: 'Light snow showers',
        86: 'Heavy snow showers',
        95: 'Thunderstorm',
        96: 'Thunderstorm with slight hail',
        99: 'Thunderstorm with heavy hail'
    },
    lv: {
        0: 'Skaidras debesis',
        1: 'Pārsvarā skaidrs',
        2: 'Daļēji mākoņains',
        3: 'Apmācies',
        45: 'Migla',
        48: 'Apledojusi migla',
        51: 'Neliels smidzeklis',
        53: 'Mērens smidzeklis',
        55: 'Spēcīgs smidzeklis',
        56: 'Neliels sasalstošs smidzeklis',
        57: 'Spēcīgs sasalstošs smidzeklis',
        61: 'Neliels lietus',
        63: 'Mērens lietus',
        65: 'Spēcīgs lietus',
        66: 'Neliels sasalstošs lietus',
        67: 'Spēcīgs sasalstošs lietus',
        71: 'Neliels sniegs',
        73: 'Mērens sniegs',
        75: 'Spēcīgs sniegs',
        77: 'Sniega graudi',
        80: 'Nelielas lietusgāzes',
        81: 'Mērenas lietusgāzes',
        82: 'Spēcīgas lietusgāzes',
        85: 'Nelielas sniega brāzmas',
        86: 'Spēcīgas sniega brāzmas',
        95: 'Pērkona negaiss',
        96: 'Pērkona negaiss ar nelielu krusu',
        99: 'Pērkona negaiss ar spēcīgu krusu'
    }
};

initNavigation();
initDropdowns();
initModeToggle();
initFooterYear();
initPriceCharts();
initLanguageControl();
initCurrencyControl();
initGamesTableControls();
initWeatherWidget();

function initNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const mainNav = document.querySelector('.main-nav');

    if (!hamburger || !mainNav) {
        return;
    }

    hamburger.addEventListener('click', () => {
        const expanded = hamburger.getAttribute('aria-expanded') === 'true';
        hamburger.classList.toggle('active');
        mainNav.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', String(!expanded));
    });

    document.addEventListener('click', (event) => {
        if (!hamburger.contains(event.target) && !mainNav.contains(event.target)) {
            hamburger.classList.remove('active');
            mainNav.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });
}

function initDropdowns() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    if (!dropdownToggles.length) {
        return;
    }

    dropdownToggles.forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const dropdown = toggle.closest('.has-dropdown');
            if (!dropdown) {
                return;
            }
            const isOpen = dropdown.classList.toggle('open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });
    });

    document.addEventListener('click', (event) => {
        dropdownToggles.forEach((toggle) => {
            const dropdown = toggle.closest('.has-dropdown');
            if (!dropdown) {
                return;
            }
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });
}

function initModeToggle() {
    const modeToggle = document.querySelector('.mode-toggle');
    if (!modeToggle) {
        return;
    }

    modeToggle.addEventListener('click', () => {
        const isDark = document.body.classList.toggle('dark-mode');
        modeToggle.setAttribute('aria-pressed', String(isDark));
        setModeToggleLabel(modeToggle);
        renderAllCharts();
    });

    modeToggle.setAttribute('aria-pressed', String(document.body.classList.contains('dark-mode')));
    setModeToggleLabel(modeToggle);
}

function setModeToggleLabel(button) {
    if (!button) {
        return;
    }
    const isDark = document.body.classList.contains('dark-mode');
    const key = isDark ? 'mode.light' : 'mode.dark';
    button.textContent = translate(key);
}

function initFooterYear() {
    const yearElement = document.getElementById('year');
    if (yearElement) {
        yearElement.textContent = String(new Date().getFullYear());
    }
}

function initLanguageControl() {
    const languageSelectors = document.querySelectorAll('[data-control="language"]');
    const stored = localStorage.getItem('preferredLanguage');
    const initialLanguage = translations[stored] ? stored : fallbackLanguage;

    currentLanguage = initialLanguage;

    languageSelectors.forEach((select) => {
        select.value = currentLanguage;
        select.addEventListener('change', (event) => {
            const newLanguage = event.target.value;
            applyLanguage(newLanguage, { fromUser: true });
            languageSelectors.forEach((other) => {
                if (other !== select) {
                    other.value = newLanguage;
                }
            });
        });
    });

    applyLanguage(currentLanguage);
}

function applyLanguage(languageCode, options = {}) {
    const validLanguage = translations[languageCode] ? languageCode : fallbackLanguage;
    currentLanguage = validLanguage;
    if (options.fromUser) {
        localStorage.setItem('preferredLanguage', currentLanguage);
    }
    document.documentElement.setAttribute('lang', currentLanguage);

    const textNodes = document.querySelectorAll('[data-i18n]');
    textNodes.forEach((node) => {
        const key = node.dataset.i18n;
        if (!key) {
            return;
        }

        if (key === 'footer.notice') {
            const translated = translate(key, { year: '{year}' });
            node.innerHTML = translated.replace('{year}', '<span id="year"></span>');
        } else {
            node.textContent = translate(key);
        }
    });

    const placeholders = document.querySelectorAll('[data-i18n-placeholder]');
    placeholders.forEach((element) => {
        const key = element.dataset.i18nPlaceholder;
        if (key) {
            element.setAttribute('placeholder', translate(key));
        }
    });

    const optionNodes = document.querySelectorAll('option[data-i18n]');
    optionNodes.forEach((option) => {
        option.textContent = translate(option.dataset.i18n);
    });

    initFooterYear();

    const modeToggles = document.querySelectorAll('.mode-toggle');
    modeToggles.forEach((button) => setModeToggleLabel(button));

    refreshChartAriaLabels();

    if (gamesTableState?.applyFilters) {
        gamesTableState.applyFilters();
    } else if (gamesTableState?.resultsElement) {
        updateGamesResultsSummary(currentGamesShown, gamesTableState.total || 0);
    }

    renderWeatherStatus();
    renderWeatherResult();
}

function translate(key, replacements = {}) {
    const dictionary = translations[currentLanguage] || translations[fallbackLanguage];
    let template = dictionary[key];
    if (!template) {
        template = translations[fallbackLanguage][key] || '';
    }

    return template.replace(/\{(\w+)\}/g, (match, token) => {
        const replacement = replacements[token];
        if (replacement === undefined || replacement === null) {
            return '';
        }
        return String(replacement);
    });
}

function initPriceCharts() {
    const canvases = document.querySelectorAll('[data-price-history]');
    canvases.forEach((canvas) => {
        const historyRaw = canvas.dataset.priceHistory;
        if (!historyRaw) {
            return;
        }
        const points = parseHistoryString(historyRaw);
        if (!points.length) {
            return;
        }

        const baseCurrency = (canvas.dataset.priceCurrency || 'USD').toUpperCase();
        canvas.dataset.priceCurrencyBase = baseCurrency;

        const basePoints = points.map((point) => ({
            label: point.label,
            baseValue: point.value
        }));

        chartDataRegistry.set(canvas, {
            basePoints,
            points: basePoints.map(({ label, baseValue }) => ({
                label,
                value: convertCurrency(baseValue, currentCurrency, baseCurrency)
            })),
            baseCurrency,
            currency: currentCurrency,
            title: canvas.dataset.chartTitle || ''
        });

        canvas.dataset.priceCurrency = currentCurrency;
    });

    if (!chartDataRegistry.size) {
        return;
    }

    renderAllCharts();
    window.addEventListener('resize', handleResize);
}

function initCurrencyControl() {
    collectPriceElements();

    const currencySelectors = document.querySelectorAll('[data-control="currency"]');
    const stored = localStorage.getItem('preferredCurrency');
    const initialCurrency = currencyMeta[stored] ? stored : 'USD';
    currentCurrency = initialCurrency;

    currencySelectors.forEach((select) => {
        select.value = currentCurrency;
        select.addEventListener('change', (event) => {
            const newCurrency = event.target.value.toUpperCase();
            applyCurrencySelection(newCurrency, { fromUser: true });
            currencySelectors.forEach((other) => {
                if (other !== select) {
                    other.value = newCurrency;
                }
            });
        });
    });

    applyCurrencySelection(currentCurrency);
}

function collectPriceElements() {
    const priceNodes = document.querySelectorAll('[data-price-base]');
    priceNodes.forEach((node) => {
        const baseValue = Number.parseFloat(node.dataset.priceBase);
        if (Number.isFinite(baseValue)) {
            node.dataset.priceBase = baseValue.toString();
            node.dataset.priceFallback = node.dataset.priceFallback || node.textContent.trim();
            node.dataset.priceCurrencyBase = (node.dataset.priceCurrency || 'USD').toUpperCase();
            convertiblePriceElements.push(node);
        } else {
            node.dataset.priceFallback = node.dataset.priceFallback || node.textContent.trim();
        }
    });
}

function applyCurrencySelection(currencyCode, options = {}) {
    const normalized = currencyCode.toUpperCase();
    const meta = currencyMeta[normalized] ? currencyMeta[normalized] : currencyMeta.USD;
    currentCurrency = normalized;

    if (options.fromUser) {
        localStorage.setItem('preferredCurrency', currentCurrency);
    }

    convertiblePriceElements.forEach((element) => {
        const baseValue = Number.parseFloat(element.dataset.priceBase);
        if (!Number.isFinite(baseValue)) {
            element.textContent = element.dataset.priceFallback || element.textContent;
            return;
        }
        const baseCurrency = (element.dataset.priceCurrencyBase || 'USD').toUpperCase();
        const converted = convertCurrency(baseValue, currentCurrency, baseCurrency);
        element.textContent = formatCurrencyValue(converted, meta);
    });

    chartDataRegistry.forEach((dataset, canvas) => {
        const baseCurrency = dataset.baseCurrency || 'USD';
        dataset.points = dataset.basePoints.map(({ label, baseValue }) => ({
            label,
            value: convertCurrency(baseValue, currentCurrency, baseCurrency)
        }));
        dataset.currency = currentCurrency;
        canvas.dataset.priceCurrency = currentCurrency;
    });

    renderAllCharts();
    refreshChartAriaLabels();

    if (gamesTableState?.applyFilters) {
        gamesTableState.applyFilters();
    }
}

function convertCurrency(amount, targetCurrency, sourceCurrency = 'USD') {
    if (!Number.isFinite(amount)) {
        return Number.NaN;
    }
    const sourceMeta = currencyMeta[sourceCurrency] || currencyMeta.USD;
    const targetMeta = currencyMeta[targetCurrency] || currencyMeta.USD;
    const amountInUsd = amount / sourceMeta.rate;
    return amountInUsd * targetMeta.rate;
}

function formatCurrencyValue(amount, meta) {
    if (!Number.isFinite(amount)) {
        return '-';
    }
    const decimals = Number.isInteger(meta.decimals) ? meta.decimals : 2;
    const formatted = amount.toLocaleString(undefined, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
    return `${meta.symbol}${formatted}`;
}

function refreshChartAriaLabels() {
    chartDataRegistry.forEach((dataset, canvas) => {
        const title = dataset.title || translate('games.chartUntitled');
        const label = translate('games.chartAria', {
            title,
            currency: dataset.currency || currentCurrency
        }).trim();
        canvas.setAttribute('aria-label', label);
    });
}

function initGamesTableControls() {
    const table = document.querySelector('[data-games-table]');
    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');
    if (!tbody) {
        return;
    }

    const rows = Array.from(tbody.querySelectorAll('tr'));
    gamesTableState = {
        table,
        tbody,
        rows: rows.map((row, index) => ({
            element: row,
            order: Number.parseInt(row.dataset.order || String(index), 10),
            title: row.dataset.title ? row.dataset.title.toLowerCase() : '',
            genres: row.dataset.genres ? row.dataset.genres.split(',').map((item) => item.trim().toLowerCase()) : [],
            platforms: row.dataset.platforms ? row.dataset.platforms.split(',').map((item) => item.trim().toLowerCase()) : [],
            steamPrice: Number.parseFloat(row.dataset.steamPrice),
            players: Number.parseInt(row.dataset.players || '0', 10),
            peak: Number.parseInt(row.dataset.peak || '0', 10)
        })),
        controls: {
            search: document.querySelector('[data-action="games-search"]'),
            genre: document.querySelector('[data-action="games-genre"]'),
            platform: document.querySelector('[data-action="games-platform"]'),
            sort: document.querySelector('[data-action="games-sort"]')
        },
        emptyElement: document.querySelector('.games-empty'),
        resultsElement: document.querySelector('[data-results]'),
        total: rows.length,
        applyFilters: null
    };

    const applyFilters = () => {
        const query = gamesTableState.controls.search?.value.trim().toLowerCase() || '';
        const selectedGenre = gamesTableState.controls.genre?.value || 'all';
        const selectedPlatform = gamesTableState.controls.platform?.value || 'all';
        const sortKey = gamesTableState.controls.sort?.value || 'featured';

        const filtered = gamesTableState.rows.filter((row) => {
            const matchesQuery = !query || row.title.includes(query);
            const matchesGenre = selectedGenre === 'all' || row.genres.includes(selectedGenre);
            const matchesPlatform = selectedPlatform === 'all' || row.platforms.includes(selectedPlatform);
            return matchesQuery && matchesGenre && matchesPlatform;
        });

        const sorted = sortGameRows(filtered, sortKey);
        const fragment = document.createDocumentFragment();
        const matchedSet = new Set(sorted);

        sorted.forEach((row) => {
            row.element.hidden = false;
            fragment.appendChild(row.element);
        });

        gamesTableState.rows.forEach((row) => {
            if (!matchedSet.has(row)) {
                row.element.hidden = true;
                fragment.appendChild(row.element);
            }
        });

        gamesTableState.tbody.appendChild(fragment);

        currentGamesShown = filtered.length;
        updateGamesResultsSummary(currentGamesShown, gamesTableState.total);

        if (gamesTableState.emptyElement) {
            gamesTableState.emptyElement.hidden = filtered.length !== 0;
        }
    };

    gamesTableState.applyFilters = applyFilters;

    if (gamesTableState.controls.search) {
        gamesTableState.controls.search.addEventListener('input', () => applyFilters());
    }
    if (gamesTableState.controls.genre) {
        gamesTableState.controls.genre.addEventListener('change', () => applyFilters());
    }
    if (gamesTableState.controls.platform) {
        gamesTableState.controls.platform.addEventListener('change', () => applyFilters());
    }
    if (gamesTableState.controls.sort) {
        gamesTableState.controls.sort.addEventListener('change', () => applyFilters());
    }

    currentGamesShown = gamesTableState.total;
    updateGamesResultsSummary(currentGamesShown, gamesTableState.total);
    applyFilters();
}

function sortGameRows(rows, sortKey) {
    const sorter = {
        featured: (a, b) => compareNumbers(a.order, b.order),
        'price-asc': (a, b) => compareNumbers(getRowPrice(a), getRowPrice(b)),
        'price-desc': (a, b) => compareNumbers(getRowPrice(b), getRowPrice(a)),
        players: (a, b) => compareNumbers(b.players, a.players),
        peak: (a, b) => compareNumbers(b.peak, a.peak)
    }[sortKey] || ((a, b) => compareNumbers(a.order, b.order));

    return rows.slice().sort(sorter);
}

function getRowPrice(row) {
    if (!Number.isFinite(row.steamPrice)) {
        return Number.NaN;
    }
    return convertCurrency(row.steamPrice, currentCurrency, 'USD');
}

function compareNumbers(a, b) {
    if (Number.isNaN(a) && Number.isNaN(b)) {
        return 0;
    }
    if (Number.isNaN(a)) {
        return 1;
    }
    if (Number.isNaN(b)) {
        return -1;
    }
    if (a === b) {
        return 0;
    }
    return a < b ? -1 : 1;
}

function updateGamesResultsSummary(shown, total) {
    if (!gamesTableState?.resultsElement) {
        return;
    }
    gamesTableState.resultsElement.textContent = translate('games.results', { shown, total });
}

function initWeatherWidget() {
    const form = document.querySelector('[data-weather-form]');
    if (!form) {
        return;
    }

    const elements = {
        form,
        input: form.querySelector('[data-weather-input]'),
        submit: form.querySelector('button[type="submit"]'),
        status: document.querySelector('[data-weather-status]'),
        result: document.querySelector('[data-weather-result]'),
        city: document.querySelector('[data-weather-city]'),
        meta: document.querySelector('[data-weather-meta]'),
        temperature: document.querySelector('[data-weather-temperature]'),
        feelsLike: document.querySelector('[data-weather-feels-like]'),
        humidity: document.querySelector('[data-weather-humidity]'),
        wind: document.querySelector('[data-weather-wind]')
    };

    weatherState = {
        elements,
        statusKey: null,
        statusReplacements: {},
        data: null
    };

    renderWeatherStatus();
    renderWeatherResult();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!weatherState?.elements?.input) {
            return;
        }

        const query = weatherState.elements.input.value.trim();
        if (!query) {
            weatherState.data = null;
            setWeatherStatus('weather.error.empty');
            renderWeatherResult();
            return;
        }

        setWeatherStatus('weather.status.loading', { city: query });
        setWeatherLoading(true);
        renderWeatherResult();

        try {
            const location = await lookupWeatherLocation(query);
            const weatherData = await fetchWeatherForLocation(location);
            weatherState.data = {
                location,
                current: weatherData.current,
                units: weatherData.units,
                timezone: weatherData.timezone
            };
            setWeatherStatus(null);
            renderWeatherResult();
        } catch (error) {
            weatherState.data = null;
            renderWeatherResult();
            const type = error?.type || 'lookup';
            if (type === 'not_found') {
                setWeatherStatus('weather.error.notFound', { city: query });
            } else if (type === 'unavailable') {
                setWeatherStatus('weather.error.unavailable');
            } else {
                setWeatherStatus('weather.error.lookup');
            }
        } finally {
            setWeatherLoading(false);
        }
    });
}

function setWeatherLoading(isLoading) {
    if (!weatherState?.elements) {
        return;
    }
    if (weatherState.elements.submit) {
        weatherState.elements.submit.disabled = Boolean(isLoading);
    }
    if (weatherState.elements.input) {
        weatherState.elements.input.setAttribute('aria-busy', String(Boolean(isLoading)));
    }
}

function setWeatherStatus(key, replacements = {}) {
    if (!weatherState) {
        return;
    }
    weatherState.statusKey = key;
    weatherState.statusReplacements = replacements;
    renderWeatherStatus();
}

function renderWeatherStatus() {
    if (!weatherState?.elements?.status) {
        return;
    }
    const { statusKey, statusReplacements } = weatherState;
    weatherState.elements.status.textContent = statusKey ? translate(statusKey, statusReplacements) : '';
}

function renderWeatherResult() {
    if (!weatherState?.elements?.result) {
        return;
    }

    const { result, city, meta, temperature, feelsLike, humidity, wind } = weatherState.elements;
    if (!weatherState.data) {
        result.setAttribute('aria-hidden', 'true');
        if (temperature) {
            temperature.textContent = '-';
        }
        if (feelsLike) {
            feelsLike.textContent = '-';
        }
        if (humidity) {
            humidity.textContent = '-';
        }
        if (wind) {
            wind.textContent = '-';
        }
        if (meta) {
            meta.textContent = '';
        }
        if (city) {
            city.textContent = '-';
        }
        return;
    }

    const { location, current, units, timezone } = weatherState.data;
    const locationLabel = [location.name, location.country].filter(Boolean).join(', ');
    if (city) {
        city.textContent = locationLabel || location.name || '';
    }

    const condition = describeWeatherCode(current.weatherCode);
    const timeLabel = formatWeatherTime(current.updatedAt, timezone);
    if (meta) {
        meta.textContent = translate('weather.meta', {
            condition,
            time: timeLabel
        });
    }

    if (temperature) {
        temperature.textContent = formatWeatherMetric(current.temperature, units.temperature, 1);
    }
    if (feelsLike) {
        feelsLike.textContent = formatWeatherMetric(current.feelsLike, units.feelsLike, 1);
    }
    if (humidity) {
        humidity.textContent = formatWeatherMetric(current.humidity, units.humidity, 0);
    }
    if (wind) {
        wind.textContent = formatWeatherMetric(current.windSpeed, units.windSpeed, 1);
    }

    result.setAttribute('aria-hidden', 'false');
}

function describeWeatherCode(code) {
    const normalized = Number.isFinite(code) ? Number(code) : null;
    const dictionary = weatherCodeDescriptions[currentLanguage] || weatherCodeDescriptions[fallbackLanguage] || {};
    if (normalized !== null && dictionary[normalized]) {
        return dictionary[normalized];
    }
    const fallbackDictionary = weatherCodeDescriptions[fallbackLanguage] || {};
    if (normalized !== null && fallbackDictionary[normalized]) {
        return fallbackDictionary[normalized];
    }
    return translate('weather.condition.unknown');
}

function formatWeatherTime(isoString, timezone) {
    if (!isoString) {
        return translate('weather.condition.unknown');
    }
    const date = new Date(isoString);
    if (Number.isNaN(date.getTime())) {
        return isoString;
    }
    const options = {
        weekday: 'short',
        hour: '2-digit',
        minute: '2-digit',
        day: 'numeric',
        month: 'short'
    };
    try {
        if (timezone) {
            options.timeZone = timezone;
        }
        return new Intl.DateTimeFormat(undefined, options).format(date);
    } catch (error) {
        return date.toLocaleString();
    }
}

function formatWeatherMetric(value, unit, decimals) {
    if (!Number.isFinite(value)) {
        return '—';
    }
    const safeDecimals = Number.isInteger(decimals) && decimals >= 0 ? decimals : 1;
    const formatted = value.toLocaleString(undefined, {
        minimumFractionDigits: safeDecimals,
        maximumFractionDigits: safeDecimals
    });
    if (!unit) {
        return formatted;
    }
    return unit.startsWith('%') ? `${formatted}${unit}` : `${formatted} ${unit}`;
}

async function lookupWeatherLocation(query) {
    const params = new URLSearchParams({
        name: query,
        count: '1',
        language: currentLanguage || fallbackLanguage,
        format: 'json'
    });
    const url = `https://geocoding-api.open-meteo.com/v1/search?${params.toString()}`;
    let response;
    try {
        response = await fetch(url);
    } catch (networkError) {
        throw makeWeatherError('lookup', networkError);
    }
    if (!response.ok) {
        throw makeWeatherError('lookup', response.status);
    }
    const payload = await response.json();
    if (!payload?.results?.length) {
        throw makeWeatherError('not_found');
    }
    const result = payload.results[0];
    if (!Number.isFinite(result.latitude) || !Number.isFinite(result.longitude)) {
        throw makeWeatherError('not_found');
    }
    return {
        name: result.name,
        country: result.country || result.country_code || '',
        latitude: result.latitude,
        longitude: result.longitude
    };
}

async function fetchWeatherForLocation(location) {
    const params = new URLSearchParams({
        latitude: location.latitude,
        longitude: location.longitude,
        current: 'temperature_2m,relative_humidity_2m,apparent_temperature,wind_speed_10m,weather_code',
        timezone: 'auto'
    });
    const url = `https://api.open-meteo.com/v1/forecast?${params.toString()}`;
    let response;
    try {
        response = await fetch(url);
    } catch (networkError) {
        throw makeWeatherError('unavailable', networkError);
    }
    if (!response.ok) {
        throw makeWeatherError('unavailable', response.status);
    }
    const payload = await response.json();
    if (!payload?.current) {
        throw makeWeatherError('unavailable');
    }
    return {
        current: {
            weatherCode: payload.current.weather_code,
            temperature: payload.current.temperature_2m,
            feelsLike: payload.current.apparent_temperature,
            humidity: payload.current.relative_humidity_2m,
            windSpeed: payload.current.wind_speed_10m,
            updatedAt: payload.current.time
        },
        units: {
            temperature: payload.current_units?.temperature_2m || '°C',
            feelsLike: payload.current_units?.apparent_temperature || '°C',
            humidity: payload.current_units?.relative_humidity_2m || '%',
            windSpeed: payload.current_units?.wind_speed_10m || 'km/h'
        },
        timezone: payload.timezone || 'UTC'
    };
}

function makeWeatherError(type, details) {
    const error = new Error(type);
    error.type = type;
    if (details !== undefined) {
        error.details = details;
    }
    return error;
}

function handleResize() {
    clearTimeout(chartResizeTimer);
    chartResizeTimer = setTimeout(renderAllCharts, 180);
}

function formatCurrency(value, currencyCode = 'USD') {
    if (!Number.isFinite(value)) {
        return '-';
    }
    const normalized = currencyCode.toUpperCase();
    const meta = currencyMeta[normalized] || currencyMeta.USD;
    return formatCurrencyValue(value, meta);
}

