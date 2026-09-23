<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Multi-year Annual Subscription workbook: a Summary sheet (one row per
// year) followed by one sheet per year listing every fellow.
class SubscriptionReportExport implements WithMultipleSheets
{
    public const FELLOW_HEADINGS = [
        'Fellow', 'Email', 'Country', 'Fellowship Type', 'Status',
        'Due (USD)', 'Paid (USD)', 'Outstanding (USD)', 'Date Paid', 'Mode',
    ];

    public const SUMMARY_HEADINGS = [
        'Year', 'Total Fellows', 'Paid', 'Partial', 'Unpaid', 'No Record', 'Waived', 'Owing',
        'Due (USD)', 'Collected (USD)', 'Outstanding (USD)',
    ];

    /**
     * @param array $summaryRows one array per year, in SUMMARY_HEADINGS order
     * @param array $yearRows    year => list of arrays in FELLOW_HEADINGS order
     */
    public function __construct(protected array $summaryRows, protected array $yearRows) {}

    public function sheets(): array
    {
        $sheets = [new SubscriptionSheet('Summary', self::SUMMARY_HEADINGS, $this->summaryRows)];

        foreach ($this->yearRows as $year => $rows) {
            $sheets[] = new SubscriptionSheet((string) $year, self::FELLOW_HEADINGS, $rows);
        }

        return $sheets;
    }
}
