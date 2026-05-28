<?php

namespace App\Widgets;

use Akaunting\Apexcharts\Chart;
use App\Abstracts\Widget;
use App\Models\Banking\Transaction;
use App\Traits\Charts;
use App\Traits\Currencies;
use App\Traits\DateTime;
use App\Utilities\Date;
use Balping\JsonRaw\Raw;

class CashFlow extends Widget
{
    use Charts, Currencies, DateTime;

    public $default_name = 'widgets.cash_flow';

    public $default_settings = [
        'width' => '100',
    ];

    public $description = 'widgets.description.cash_flow';

    public $report_class = 'Modules\CashFlowStatement\Reports\CashFlowStatement';

    public $start_date;

    public $end_date;

    public $period;

    public function show()
    {
        $this->setFilter();

        $income = array_values($this->calculateTotals('income'));
        $expense = array_values($this->calculateTotals('expense'));
        $profit = array_values($this->calculateProfit($income, $expense));

        $chart = new Chart();

        $chart->setType('line')
            ->setDefaultLocale($this->getDefaultLocaleOfChart())
            ->setLocales($this->getLocaleTranslationOfChart())
            ->setStacked(true)
            ->setBar(['columnWidth' => '40%'])
            ->setLegendPosition('top')
            ->setYaxisLabels(['formatter' => $this->getChartLabelFormatter()])
            ->setLabels(array_values($this->getLabels()))
            ->setColors($this->getColors())
            ->setMarkersSize(5)
            ->setMarkersHover(['size' => 7])
            ->setDataLabelsEnabled(true)
            ->setDataLabelsEnabledOnSeries([2])
            ->setDataLabelsFormatter($this->getChartLabelFormatter())
            ->setDataLabelsBackground(['enabled' => true, 'foreColor' => '#7779A2', 'padding' => 4, 'borderRadius' => 2, 'borderColor' => '#7779A2'])
            ->setDataLabelsOffsetY(-8)
            ->setTooltipShared(true)
            ->setTooltipIntersect(false)
            ->setTooltipCustom($this->getCustomTooltip())
            ->setDataset(trans('general.incoming'), 'column', $income)
            ->setDataset(trans('general.outgoing'), 'column', $expense)
            ->setDataset(trans_choice('general.profits', 1), 'line', $profit);

        $incoming_amount = money(array_sum($income));
        $outgoing_amount = money(abs(array_sum($expense)));
        $profit_amount = money(array_sum($profit));

        $totals = [
            'incoming_exact'        => $incoming_amount->format(),
            'incoming_for_humans'   => $incoming_amount->formatForHumans(),
            'outgoing_exact'        => $outgoing_amount->format(),
            'outgoing_for_humans'   => $outgoing_amount->formatForHumans(),
            'profit_exact'          => $profit_amount->format(),
            'profit_for_humans'     => $profit_amount->formatForHumans(),
        ];

        return $this->view('widgets.cash_flow', [
            'chart' => $chart,
            'totals' => $totals,
        ]);
    }

    public function setFilter(): void
    {
        $financial_year = $this->getFinancialYear();

        $this->start_date = Date::parse(request('start_date', $financial_year->copy()->getStartDate()->toDateString()))->startOfDay();
        $this->end_date = Date::parse(request('end_date', $financial_year->copy()->getEndDate()->toDateString()))->endOfDay();
        $this->period = request('period', 'month');
    }

    public function getLabels(): array
    {
        $labels = [];

        $start_date = $this->start_date->copy();

        $counter = $this->end_date->diffInMonths($this->start_date);

        for ($j = 0; $j <= $counter; $j++) {
            $labels[$j] = $start_date->format($this->getMonthlyDateFormat());

            if ($this->period == 'month') {
                $start_date->addMonth();
            } else {
                $start_date->addMonths(3);
                $j += 2;
            }
        }

        return $labels;
    }

    public function getColors(): array
    {
        return [
            '#8bb475',
            '#fb7185',
            '#7779A2',
        ];
    }

    public function getCustomTooltip(): Raw
    {
        $decimal_mark = str_replace("'", "\\'", currency()->getDecimalMark());
        $thousands_separator = str_replace("'", "\\'", currency()->getThousandsSeparator());
        $symbol = str_replace("'", "\\'", currency()->getSymbol());
        $symbol_first = currency()->isSymbolFirst() ? 'true' : 'false';
        $precision = (int) currency()->getPrecision();

        $incoming_label = trans('general.incoming');
        $outgoing_label = trans('general.outgoing');
        $profit_label = trans_choice('general.profits', 1);

        return new Raw("function({ series, seriesIndex, dataPointIndex, w }) {
            const decimal = '" . $decimal_mark . "';
            const thousands = '" . $thousands_separator . "';
            const symbol = '" . $symbol . "';
            const symbolFirst = " . $symbol_first . ";
            const precision = " . $precision . ";

            function fmt(v) {
                const sign = v < 0 ? '-' : '';
                let n = Math.abs(Number(v)).toFixed(precision);
                let [int, dec] = n.split('.');
                int = int.replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
                let out = dec ? int + decimal + dec : int;
                return symbolFirst ? sign + symbol + out : sign + out + symbol;
            }

            const labels = ['" . $incoming_label . "', '" . $outgoing_label . "', '" . $profit_label . "'];
            const colors = ['#8bb475', '#fb7185', '#7779A2'];
            const xLabel = w.globals.labels[dataPointIndex];

            const incoming = Number(series[0][dataPointIndex]);
            const outgoing = Number(series[1][dataPointIndex]);
            const profit = Number(series[2][dataPointIndex]);

            let rows = '';

            if (incoming !== 0) {
                rows += '<div class=\"apexcharts-tooltip-series-group apexcharts-active\" style=\"display:flex;order:1;\">'
                    + '<span class=\"apexcharts-tooltip-marker\" style=\"background-color:' + colors[0] + ';\"></span>'
                    + '<div class=\"apexcharts-tooltip-text\"><div class=\"apexcharts-tooltip-y-group\">'
                    + '<span class=\"apexcharts-tooltip-text-y-label\">' + labels[0] + ': </span>'
                    + '<span class=\"apexcharts-tooltip-text-y-value\">' + fmt(incoming) + '</span>'
                    + '</div></div></div>';
            }

            if (outgoing !== 0) {
                rows += '<div class=\"apexcharts-tooltip-series-group apexcharts-active\" style=\"display:flex;order:2;\">'
                    + '<span class=\"apexcharts-tooltip-marker\" style=\"background-color:' + colors[1] + ';\"></span>'
                    + '<div class=\"apexcharts-tooltip-text\"><div class=\"apexcharts-tooltip-y-group\">'
                    + '<span class=\"apexcharts-tooltip-text-y-label\">' + labels[1] + ': </span>'
                    + '<span class=\"apexcharts-tooltip-text-y-value\">' + fmt(Math.abs(outgoing)) + '</span>'
                    + '</div></div></div>';
            }

            const profitColor = profit < 0 ? '#f97316' : colors[2];
            rows += '<div class=\"apexcharts-tooltip-series-group apexcharts-active\" style=\"display:flex;order:3;\">'
                + '<span class=\"apexcharts-tooltip-marker\" style=\"background-color:' + profitColor + ';\"></span>'
                + '<div class=\"apexcharts-tooltip-text\"><div class=\"apexcharts-tooltip-y-group\">'
                + '<span class=\"apexcharts-tooltip-text-y-label\">' + labels[2] + ': </span>'
                + '<span class=\"apexcharts-tooltip-text-y-value\" style=\"color:' + profitColor + ';\">' + fmt(profit) + '</span>'
                + '</div></div></div>';

            return '<div class=\"apexcharts-tooltip-title\" style=\"font-family: Helvetica, Arial, sans-serif; font-size: 12px;\">' + xLabel + '</div>' + rows;
        }");
    }

    private function calculateTotals($type): array
    {
        $totals = [];

        $date_format = 'Y-m';

        if ($this->period == 'month') {
            $n = 1;
            $start_date = $this->start_date->format($date_format);
            $end_date = $this->end_date->format($date_format);
            $next_date = $start_date;
        } else {
            $n = 3;
            $start_date = $this->start_date->quarter;
            $end_date = $this->end_date->quarter;
            $next_date = $start_date;
        }

        $s = clone $this->start_date;

        //$totals[$start_date] = 0;
        while ($next_date <= $end_date) {
            $totals[$next_date] = 0;

            if ($this->period == 'month') {
                $next_date = $s->addMonths($n)->format($date_format);
            } else {
                if (isset($totals[4])) {
                    break;
                }

                $next_date = $s->addMonths($n)->quarter;
            }
        }

        $items = $this->applyFilters(Transaction::$type()->whereBetween('paid_at', [$this->start_date, $this->end_date])->isNotTransfer())->get();

        $this->setTotals($totals, $items, $date_format);

        return $totals;
    }

    private function setTotals(&$totals, $items, $date_format): void
    {
        $type = 'income';

        foreach ($items as $item) {
            $type = $item->type;

            if ($this->period == 'month') {
                $i = Date::parse($item->paid_at)->format($date_format);
            } else {
                $i = Date::parse($item->paid_at)->quarter;
            }

            if (!isset($totals[$i])) {
                continue;
            }

            $totals[$i] += $item->getAmountConvertedToDefault();
        }

        $precision = currency()->getPrecision();

        foreach ($totals as $key => $value) {
            if ($type == 'expense') {
                $value = -1 * $value;
            }

            $totals[$key] = round($value, $precision);
        }
    }

    private function calculateProfit($incomes, $expenses): array
    {
        $profit = [];

        $precision = currency()->getPrecision();

        foreach ($incomes as $key => $income) {
            $value = $income - abs($expenses[$key]);

            $profit[$key] = round($value, $precision);
        }

        return $profit;
    }
}
