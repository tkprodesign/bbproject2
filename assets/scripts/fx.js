(() => {
  'use strict';

  const cfg = window.VelmoraFxConfig || {};
  const currencies = cfg.currencies || {};
  const rates = cfg.rates || {};
  const accountCurrencies = window.VelmoraAccountCurrencies || {};

  const supported = (code) => !!currencies[String(code || '').toUpperCase()];
  const codeOf = (value) => String(value || '').trim().toUpperCase();

  const formatMoney = (amount, code) => {
    code = codeOf(code);
    const meta = currencies[code] || { symbol: code + ' ', precision: 2 };
    const precision = Number.isInteger(meta.precision) ? meta.precision : 2;
    const value = Number(amount || 0);
    const sign = value < 0 ? '-' : '';
    const formatted = Math.abs(value).toLocaleString(undefined, {
      minimumFractionDigits: precision,
      maximumFractionDigits: precision
    });
    return sign + code + ' ' + (meta.symbol || '') + formatted;
  };

  const midRate = (from, to) => {
    from = codeOf(from);
    to = codeOf(to);
    if (from === to) return 1;
    const fromRate = Number(rates[from]);
    const toRate = Number(rates[to]);
    if (!fromRate || !toRate) return null;
    return toRate / fromRate;
  };

  const spreadBps = (from, to) => {
    from = codeOf(from);
    to = codeOf(to);
    if (from === to) return 0;
    return Math.max(
      Number(currencies[from]?.spread_bps || 100),
      Number(currencies[to]?.spread_bps || 100)
    );
  };

  const customerRate = (from, to) => {
    const mid = midRate(from, to);
    if (mid === null) return null;
    const spread = spreadBps(from, to) / 10000;
    return codeOf(from) === codeOf(to) ? 1 : mid * (1 - spread);
  };

  const updateForm = (form) => {
    const mode = form.dataset.fxMode || 'transfer';
    const amountInput = form.querySelector('[data-fx-amount]');
    const accountSelect = form.querySelector('[data-fx-source-account]');
    const accountInput = form.querySelector('[data-fx-account-number]');
    const currencySelect = form.querySelector('[data-fx-currency]');
    const fromEl = form.querySelector('[data-fx-from]');
    const toEl = form.querySelector('[data-fx-to]');
    const sendEl = form.querySelector('[data-fx-send]');
    const receiveEl = form.querySelector('[data-fx-receive]');
    const rateEl = form.querySelector('[data-fx-rate]');
    const spreadEl = form.querySelector('[data-fx-spread]');
    const quoteBox = form.querySelector('[data-fx-quote]');

    const amount = Number(amountInput?.value || 0);
    let accountCurrency = '';

    if (accountSelect) {
      const option = accountSelect.options[accountSelect.selectedIndex];
      accountCurrency = codeOf(option?.dataset.currency || '');
    } else if (accountInput) {
      accountCurrency = codeOf(accountCurrencies[String(accountInput.value || '').trim()] || '');
    }

    const selectedCurrency = codeOf(currencySelect?.value || '');

    let from = '';
    let to = '';
    let sendAmount = amount;
    let receiveAmount = 0;

    if (mode === 'deposit') {
      from = selectedCurrency;
      to = accountCurrency;
      const rate = customerRate(from, to);
      receiveAmount = rate === null ? 0 : amount * rate;
    } else if (mode === 'withdrawal') {
      from = accountCurrency;
      to = selectedCurrency;
      const rate = customerRate(from, to);
      receiveAmount = amount;
      sendAmount = rate ? amount / rate : 0;
    } else {
      from = accountCurrency;
      to = selectedCurrency;
      const rate = customerRate(from, to);
      receiveAmount = rate === null ? 0 : amount * rate;
    }

    const rate = customerRate(from, to);
    const bps = supported(from) && supported(to) ? spreadBps(from, to) : 0;

    if (fromEl) fromEl.textContent = from || '—';
    if (toEl) toEl.textContent = to || '—';
    if (sendEl) sendEl.textContent = from && sendAmount > 0 ? formatMoney(sendAmount, from) : '—';
    if (receiveEl) receiveEl.textContent = to && receiveAmount > 0 ? formatMoney(receiveAmount, to) : '—';
    if (rateEl) rateEl.textContent = rate && from && to ? `1 ${from} = ${rate.toFixed(6)} ${to}` : '—';
    if (spreadEl) spreadEl.textContent = from && to ? (bps / 100).toFixed(2) + '%' : '—';

    if (quoteBox) {
      const ready = amount > 0 && supported(from) && supported(to) && rate;
      quoteBox.classList.toggle('is-ready', !!ready);
      quoteBox.classList.toggle('is-waiting', !ready);
    }
  };

  document.querySelectorAll('[data-fx-form]').forEach((form) => {
    const fields = form.querySelectorAll('input, select');
    fields.forEach((field) => {
      field.addEventListener('input', () => updateForm(form));
      field.addEventListener('change', () => updateForm(form));
    });
    updateForm(form);
  });
})();
