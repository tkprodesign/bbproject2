<?php
/**
 * Velmora multi-currency and bank FX helpers.
 *
 * Market reference rates are cached outside the public web root. Customer
 * conversion rates include a small bank spread that varies by currency pair.
 */

function velmoraCurrencies(): array {
    return [
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'precision' => 2, 'spread_bps' => 50],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'precision' => 2, 'spread_bps' => 55],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'precision' => 2, 'spread_bps' => 55],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'precision' => 0, 'spread_bps' => 65],
        'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'precision' => 2, 'spread_bps' => 60],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'precision' => 2, 'spread_bps' => 70],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'precision' => 2, 'spread_bps' => 70],
        'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'precision' => 2, 'spread_bps' => 85],
        'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'precision' => 2, 'spread_bps' => 110],
        'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'precision' => 2, 'spread_bps' => 75],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'precision' => 2, 'spread_bps' => 75],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'precision' => 2, 'spread_bps' => 85],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'precision' => 2, 'spread_bps' => 120],
        'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦', 'precision' => 2, 'spread_bps' => 180],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'precision' => 2, 'spread_bps' => 145],
        'SEK' => ['name' => 'Swedish Krona', 'symbol' => 'kr', 'precision' => 2, 'spread_bps' => 95],
        'NOK' => ['name' => 'Norwegian Krone', 'symbol' => 'kr', 'precision' => 2, 'spread_bps' => 95],
        'DKK' => ['name' => 'Danish Krone', 'symbol' => 'kr', 'precision' => 2, 'spread_bps' => 95],
        'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩', 'precision' => 0, 'spread_bps' => 120],
        'MXN' => ['name' => 'Mexican Peso', 'symbol' => 'MX$', 'precision' => 2, 'spread_bps' => 130],
    ];
}

function velmoraCurrencyCodes(): array {
    return array_keys(velmoraCurrencies());
}

function velmoraIsSupportedCurrency(string $currency): bool {
    return isset(velmoraCurrencies()[strtoupper(trim($currency))]);
}

function velmoraCurrencyMeta(string $currency): array {
    $code = strtoupper(trim($currency));
    $all = velmoraCurrencies();
    return $all[$code] ?? ['name' => $code, 'symbol' => $code, 'precision' => 2, 'spread_bps' => 100];
}

function velmoraCurrencyOptions(string $selected = 'USD'): string {
    $selected = strtoupper(trim($selected));
    $html = '';
    foreach (velmoraCurrencies() as $code => $meta) {
        $isSelected = $code === $selected ? ' selected' : '';
        $label = $code . ' — ' . $meta['name'];
        $html .= '<option value="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '"' . $isSelected . '>'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
}

function velmoraFallbackFxRates(): array {
    return [
        'USD' => 1.0, 'EUR' => 0.92, 'GBP' => 0.79, 'JPY' => 149.0, 'CHF' => 0.88,
        'CAD' => 1.36, 'AUD' => 1.52, 'NZD' => 1.65, 'CNY' => 7.18, 'HKD' => 7.78,
        'SGD' => 1.34, 'AED' => 3.6725, 'INR' => 83.5, 'NGN' => 1600.0, 'ZAR' => 18.2,
        'SEK' => 10.4, 'NOK' => 10.7, 'DKK' => 6.86, 'KRW' => 1360.0, 'MXN' => 18.0,
    ];
}

function velmoraFxRates(): array {
    static $memory = null;
    if (is_array($memory)) {
        return $memory;
    }

    $cacheFile = dirname(__DIR__, 2) . '/.velmora-fx-rates.json';
    $maxAge = 43200; // 12 hours; provider refreshes daily.
    $cached = null;

    if (is_file($cacheFile)) {
        $decoded = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($decoded) && !empty($decoded['rates']) && is_array($decoded['rates'])) {
            $cached = $decoded;
            if (!empty($decoded['fetched_at']) && (time() - (int) $decoded['fetched_at']) < $maxAge) {
                $memory = $decoded['rates'];
                return $memory;
            }
        }
    }

    $fresh = null;
    if (function_exists('curl_init')) {
        $ch = curl_init('https://open.er-api.com/v6/latest/USD');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'VelmoraBankFX/1.0',
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body !== false && $status >= 200 && $status < 300) {
            $payload = json_decode((string) $body, true);
            if (is_array($payload) && ($payload['result'] ?? '') === 'success' && !empty($payload['rates'])) {
                $fresh = [];
                foreach (velmoraCurrencyCodes() as $code) {
                    if (isset($payload['rates'][$code]) && is_numeric($payload['rates'][$code])) {
                        $fresh[$code] = (float) $payload['rates'][$code];
                    }
                }
            }
        }
    }

    if (is_array($fresh) && isset($fresh['USD']) && count($fresh) >= 10) {
        $record = [
            'fetched_at' => time(),
            'provider' => 'ExchangeRate-API',
            'rates' => $fresh,
        ];
        $tmp = $cacheFile . '.tmp';
        if (@file_put_contents($tmp, json_encode($record, JSON_UNESCAPED_SLASHES), LOCK_EX) !== false) {
            @chmod($tmp, 0600);
            @rename($tmp, $cacheFile);
        }
        $memory = array_replace(velmoraFallbackFxRates(), $fresh);
        return $memory;
    }

    if (is_array($cached) && !empty($cached['rates'])) {
        $memory = array_replace(velmoraFallbackFxRates(), $cached['rates']);
        return $memory;
    }

    $memory = velmoraFallbackFxRates();
    return $memory;
}

function velmoraFxMidRate(string $from, string $to): float {
    $from = strtoupper(trim($from));
    $to = strtoupper(trim($to));
    if ($from === $to) {
        return 1.0;
    }

    $rates = velmoraFxRates();
    if (!isset($rates[$from], $rates[$to]) || (float) $rates[$from] <= 0) {
        throw new InvalidArgumentException('Unsupported currency pair.');
    }

    return (float) $rates[$to] / (float) $rates[$from];
}

function velmoraFxSpreadBps(string $from, string $to): int {
    if (strtoupper($from) === strtoupper($to)) {
        return 0;
    }
    $fromMeta = velmoraCurrencyMeta($from);
    $toMeta = velmoraCurrencyMeta($to);
    return max((int) ($fromMeta['spread_bps'] ?? 100), (int) ($toMeta['spread_bps'] ?? 100));
}

function velmoraFxQuote(float $amount, string $from, string $to): array {
    $from = strtoupper(trim($from));
    $to = strtoupper(trim($to));
    if ($amount < 0 || !velmoraIsSupportedCurrency($from) || !velmoraIsSupportedCurrency($to)) {
        throw new InvalidArgumentException('Invalid FX quote request.');
    }

    $midRate = velmoraFxMidRate($from, $to);
    $spreadBps = velmoraFxSpreadBps($from, $to);
    $spread = $spreadBps / 10000;
    $customerRate = $from === $to ? 1.0 : $midRate * (1 - $spread);
    $bankSellRate = $from === $to ? 1.0 : $midRate * (1 + $spread);

    return [
        'from' => $from,
        'to' => $to,
        'amount_in' => $amount,
        'amount_out' => round($amount * $customerRate, 2),
        'mid_rate' => $midRate,
        'customer_rate' => $customerRate,
        'bank_buy_rate' => $customerRate,
        'bank_sell_rate' => $bankSellRate,
        'spread_bps' => $spreadBps,
        'spread_percent' => $spreadBps / 100,
    ];
}

function velmoraFxRequiredSource(float $targetAmount, string $from, string $to): array {
    $unit = velmoraFxQuote(1.0, $from, $to);
    $rate = (float) $unit['customer_rate'];
    if ($rate <= 0) {
        throw new RuntimeException('Invalid FX rate.');
    }

    $unit['amount_out'] = $targetAmount;
    $unit['amount_in'] = round($targetAmount / $rate, 2);
    return $unit;
}

function velmoraConvertForValuation(float $amount, string $from, string $to): float {
    return $amount * velmoraFxMidRate($from, $to);
}

function velmoraFormatCurrency(float $amount, string $currency, bool $withCode = true): string {
    $currency = strtoupper(trim($currency));
    $meta = velmoraCurrencyMeta($currency);
    $precision = (int) ($meta['precision'] ?? 2);
    $symbol = (string) ($meta['symbol'] ?? $currency);
    $sign = $amount < 0 ? '-' : '';
    $formatted = number_format(abs($amount), $precision);
    $money = $symbol . $formatted;
    return $sign . ($withCode ? $currency . ' ' : '') . $money;
}

function velmoraFxClientConfig(): array {
    $currencies = [];
    foreach (velmoraCurrencies() as $code => $meta) {
        $currencies[$code] = [
            'name' => $meta['name'],
            'symbol' => $meta['symbol'],
            'precision' => $meta['precision'],
            'spread_bps' => $meta['spread_bps'],
        ];
    }

    return [
        'currencies' => $currencies,
        'rates' => velmoraFxRates(),
        'provider' => 'ExchangeRate-API',
    ];
}
