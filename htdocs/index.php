<?php include('./common-sections/app.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velmora Currency Converter</title>
    <meta name="description" content="Fast single-page currency converter with live exchange rates.">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <style>
        :root {
            color-scheme: light;
            --bg: #061726;
            --card: #0e2235;
            --soft: #12314b;
            --text: #eef6ff;
            --muted: #aac0d8;
            --accent: #18d0b7;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: radial-gradient(circle at top, #163a5a 0%, var(--bg) 60%);
            color: var(--text);
            min-height: 100vh;
        }
        main {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem 3rem;
        }
        .hero {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .hero h1 { margin-bottom: .5rem; }
        .hero p { color: var(--muted); margin-top: 0; }
        .card {
            background: linear-gradient(160deg, var(--card), var(--soft));
            border-radius: 18px;
            padding: 1.2rem;
            box-shadow: 0 14px 40px rgba(0,0,0,.25);
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .9rem;
        }
        label {
            display: block;
            font-size: .85rem;
            color: var(--muted);
            margin-bottom: .35rem;
        }
        input, select, button {
            width: 100%;
            padding: .75rem;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,.14);
            background: rgba(255,255,255,.1);
            color: var(--text);
            font-size: 1rem;
        }
        select option { color: #111; }
        button {
            background: var(--accent);
            border: none;
            color: #042a2a;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1rem;
        }
        .result {
            margin-top: 1rem;
            padding: .9rem;
            border-radius: 12px;
            background: rgba(0,0,0,.24);
            font-size: 1.05rem;
        }
        .small { color: var(--muted); font-size: .9rem; }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .login-link {
            color: #dff9f5;
            text-decoration: none;
            border-bottom: 1px dashed rgba(223,249,245,.6);
        }
        @media (max-width: 700px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main>
    <section class="hero">
        <h1>Velmora Currency Converter</h1>
        <p>Simple, transparent FX conversion for travelers, freelancers, and international payments.</p>
    </section>

    <section class="card">
        <div class="grid">
            <div>
                <label for="amount">Amount</label>
                <input id="amount" type="number" min="0" step="0.01" value="100">
            </div>
            <div>
                <label for="from">From</label>
                <select id="from"></select>
            </div>
            <div>
                <label for="to">To</label>
                <select id="to"></select>
            </div>
            <div>
                <label for="date">Rate Date</label>
                <input id="date" type="date">
            </div>
        </div>
        <button id="convert">Convert Currency</button>
        <div class="result" id="result">Enter amount and select currencies.</div>
        <div class="actions">
            <span class="small" id="meta">Live rates powered by exchangerate.host.</span>
            <a class="login-link" href="/login">Staff login</a>
        </div>
    </section>
</main>

<script>
const currencies = ['USD','EUR','GBP','JPY','CAD','AUD','CHF','CNY','INR','NGN','ZAR','SGD'];
const from = document.getElementById('from');
const to = document.getElementById('to');
const amount = document.getElementById('amount');
const result = document.getElementById('result');
const meta = document.getElementById('meta');
const date = document.getElementById('date');

const today = new Date();
date.value = today.toISOString().slice(0, 10);

function fillSelect(el, defaultCode) {
    currencies.forEach(code => {
        const option = document.createElement('option');
        option.value = code;
        option.textContent = code;
        if (code === defaultCode) option.selected = true;
        el.appendChild(option);
    });
}
fillSelect(from, 'USD');
fillSelect(to, 'EUR');

async function convertCurrency() {
    const base = from.value;
    const target = to.value;
    const value = parseFloat(amount.value || '0');
    const selectedDate = date.value;

    if (value <= 0) {
        result.textContent = 'Please enter an amount greater than zero.';
        return;
    }

    result.textContent = 'Fetching latest rate...';

    try {
        const endpoint = `https://api.exchangerate.host/convert?from=${base}&to=${target}&amount=${value}&date=${selectedDate}`;
        const response = await fetch(endpoint);
        const data = await response.json();

        if (!data || typeof data.result !== 'number') {
            throw new Error('Invalid response');
        }

        result.textContent = `${value.toLocaleString()} ${base} = ${data.result.toLocaleString(undefined, {maximumFractionDigits: 4})} ${target}`;
        meta.textContent = `Rate date: ${data.date || selectedDate}. Source: exchangerate.host`;
    } catch (err) {
        result.textContent = 'Could not fetch rates right now. Please try again.';
        meta.textContent = 'Rate service temporarily unavailable.';
    }
}

document.getElementById('convert').addEventListener('click', convertCurrency);
</script>
</body>
</html>
