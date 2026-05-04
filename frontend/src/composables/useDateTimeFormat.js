const NOTIFICATION_DATE_TIME_OPTIONS = {
  day: '2-digit',
  month: 'short',
  hour: '2-digit',
  minute: '2-digit',
}

const RELEASE_DATE_OPTIONS = {
  day: '2-digit',
  month: 'short',
  year: 'numeric',
  timeZone: 'UTC',
}

function langToLocale(lang) {
  if (!lang) return 'en-GB'
  const upper = String(lang).toUpperCase()
  if (upper === 'LV' || upper === 'LVL' || upper === 'LATVIA') return 'lv-LV'
  if (upper === 'ENG' || upper === 'EN') return 'en-GB'
  return String(lang)
}

export function formatDateTime(value, options = NOTIFICATION_DATE_TIME_OPTIONS, lang = 'ENG') {
  if (!value) {
    return ''
  }

  const parsedDate = new Date(value)
  if (Number.isNaN(parsedDate.getTime())) {
    return ''
  }

  const locale = langToLocale(lang)
  return new Intl.DateTimeFormat(locale, options).format(parsedDate)
}

export function formatDateOnly(value, options = RELEASE_DATE_OPTIONS, lang = 'ENG') {
  if (!value) {
    return ''
  }

  const parsedDate = new Date(/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(value) ? `${value}T00:00:00Z` : value)
  if (Number.isNaN(parsedDate.getTime())) {
    return ''
  }

  const locale = langToLocale(lang)
  return new Intl.DateTimeFormat(locale, options).format(parsedDate)
}
