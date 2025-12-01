<?php
class Currency {
    private $baseCurrency;
    private $ratesCache = [];

    public function __construct($baseCurrency = 'INR') {
        $this->baseCurrency = strtoupper($baseCurrency);
    }

    public function setBaseCurrency($currency) {
        $this->baseCurrency = strtoupper($currency);
    }

    public function getBaseCurrency() {
        return $this->baseCurrency;
    }

    // Format amount with currency symbol and locale-friendly formatting
    public function format($amount, $currency = null) {
        $currency = strtoupper($currency ?: $this->baseCurrency);
        $symbol = $this->symbolFor($currency);
        $formatted = number_format((float)$amount, 2, '.', ',');
        return "$symbol$formatted";
    }

    public function symbolFor($currency) {
        switch (strtoupper($currency)) {
            case 'USD': return '$';
            case 'EUR': return '€';
            case 'GBP': return '£';
            case 'INR': return '₹';
            case 'JPY': return '¥';
            case 'AUD': return 'A$';
            case 'CAD': return 'C$';
            case 'CHF': return 'CHF ';
            case 'CNY': return '¥';
            default: return "$currency ";
        }
    }

    // Convert amount from one currency to another using exchangerate.host
    public function convert($amount, $from, $to) {
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from === $to) return (float)$amount;

        $rate = $this->getRate($from, $to);
        if ($rate === null) {
            // Fallback: return original amount if rate fetch fails
            return (float)$amount;
        }
        return (float)$amount * (float)$rate;
    }

    private function getRate($from, $to) {
        $key = "$from-$to";
        if (isset($this->ratesCache[$key])) {
            return $this->ratesCache[$key];
        }
        $url = "https://api.exchangerate.host/convert?from=$from&to=$to";
        try {
            $resp = @file_get_contents($url);
            if ($resp === false) return null;
            $json = json_decode($resp, true);
            if (isset($json['result'])) {
                $this->ratesCache[$key] = (float)$json['result'];
                return $this->ratesCache[$key];
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }
}
?>
