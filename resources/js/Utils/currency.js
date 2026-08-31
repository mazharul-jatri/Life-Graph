export const CURRENCY_SYMBOLS = {
  USD: '$',
  BDT: '৳',
  EUR: '€',
  GBP: '£',
  INR: '₹',
  CAD: 'C$',
  AUD: 'A$',
  JPY: '¥',
};

export const SUPPORTED_CURRENCIES = [
  { code: 'USD', symbol: '$', label: 'USD ($) - US Dollar' },
  { code: 'BDT', symbol: '৳', label: 'BDT (৳) - Bangladeshi Taka' },
  { code: 'EUR', symbol: '€', label: 'EUR (€) - Euro' },
  { code: 'GBP', symbol: '£', label: 'GBP (£) - British Pound' },
  { code: 'INR', symbol: '₹', label: 'INR (₹) - Indian Rupee' },
  { code: 'CAD', symbol: 'C$', label: 'CAD (C$) - Canadian Dollar' },
  { code: 'AUD', symbol: 'A$', label: 'AUD (A$) - Australian Dollar' },
  { code: 'JPY', symbol: '¥', label: 'JPY (¥) - Japanese Yen' },
];

export function getCurrencySymbol(code = 'USD') {
  return CURRENCY_SYMBOLS[code] || '$';
}

export function formatMoney(val, currencyCode = 'USD') {
  if (val === null || val === undefined || isNaN(val)) return `${getCurrencySymbol(currencyCode)}0`;
  const sym = getCurrencySymbol(currencyCode);

  if (val >= 1000000) {
    return `${sym}${(val / 1000000).toFixed(2)}M`;
  }
  if (val >= 1000) {
    return `${sym}${(val / 1000).toFixed(0)}k`;
  }
  return `${sym}${Number(val).toLocaleString()}`;
}
