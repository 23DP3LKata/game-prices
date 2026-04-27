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

export function formatDateTime(value, options = NOTIFICATION_DATE_TIME_OPTIONS) {
  if (!value) {
    return ''
  }

  const parsedDate = new Date(value)
  if (Number.isNaN(parsedDate.getTime())) {
    return ''
  }

  return new Intl.DateTimeFormat('en-GB', options).format(parsedDate)
}

export function formatDateOnly(value, options = RELEASE_DATE_OPTIONS) {
  if (!value) {
    return ''
  }

  const parsedDate = new Date(/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(value) ? `${value}T00:00:00Z` : value)
  if (Number.isNaN(parsedDate.getTime())) {
    return ''
  }

  return new Intl.DateTimeFormat('en-GB', options).format(parsedDate)
}
