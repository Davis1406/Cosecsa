<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTrainee2026PeFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trainees:update-2026-pe-fees {--dry-run : Run without making any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-time command: updates programme entry fee fields (amount_paid, invoice_status, mode_of_payment) for 2026 PE cohort trainees. MCS=$500, FCS=$600.';

    /**
     * Valid ENUM values for trainees.mode_of_payment.
     */
    protected const VALID_MOPS = [
        'Bank transfer',
        'Online Payment System',
        'Country Rep',
    ];

    /**
     * Known mode_of_payment overrides keyed by PE number (trimmed).
     */
    protected const MOP_OVERRIDES = [
        'RW/2026/10' => 'Online Payment System',
        'MW/2024/19' => 'Bank transfer',
        'NA/2024/03' => 'Online Payment System',
        'ZW/2026/29' => 'Country Rep',
        'ZW/2026/31' => 'Country Rep',
        'KE/2023/47' => 'Bank transfer',
        'UG/2025/30' => 'Country Rep',
        'TZ/2023/16' => 'Bank transfer',
        'TZ/2024/16' => 'Bank transfer',
        'ZW/2025/13' => 'Country Rep',
        'BW/2026/01' => 'Online Payment System',
        'TZ/2023/03' => 'Bank transfer',
    ];

    /**
     * Full 2026 PE cohort PE numbers.
     */
    protected const COHORT = [
        'BU/2026/05',
        'BU/2026/06',
        'DC/2026/01',
        'DC/2026/02',
        'CA/2026/01',
        'CA/2026/02',
        'ET/2026/05',
        'ET/2026/04',
        'ET/2026/09',
        'ET/2026/01',
        'ET/2026/02',
        'KE/2026/80',
        'ET/2026/03',
        'DC/2024/01',
        'GA/2026/01',
        'KE/2026/46',
        'KE/2026/47',
        'KE/2026/48',
        'KE/2026/12',
        'KE/2026/49',
        'KE/2026/01',
        'KE/2026/02',
        'KE/2026/31',
        'KE/2026/50',
        'KE/2026/32',
        'KE/2026/51',
        'KE/2026/28',
        'KE/2026/29',
        'KE/2026/33',
        'KE/2026/34',
        'KE/2026/13',
        'KE/2026/03',
        'KE/2026/35',
        'KE/2026/14',
        'KE/2026/36',
        'KE/2026/15',
        'KE/2026/16',
        'KE/2026/37',
        'KE/2026/38',
        'KE/2026/17',
        'KE/2026/18',
        'KE/2026/19',
        'KE/2026/39',
        'KE/2026/40',
        'KE/2026/60',
        'KE/2026/41',
        'KE/2026/55',
        'KE/2026/04',
        'KE/2026/20',
        'KE/2026/76',
        'KE/2026/42',
        'KE/2026/27',
        'KE/2026/21',
        'KE/2026/22',
        'KE/2026/23',
        'KE/2026/43',
        'KE/2026/24',
        'KE/2026/44',
        'KE/2026/05',
        'KE/2026/52',
        'KE/2026/53',
        'KE/2026/06',
        'KE/2026/07',
        'KE/2026/08',
        'KE/2026/09',
        'KE/2026/10',
        'KE/2026/11',
        'MD/2026/01',
        'KE/2024/25',
        'KE/2024/44',
        'KE/2024/55',
        'MW/2026/20',
        'MW/2023/03',
        'MW/2024/02',
        'MW/2026/19',
        'MW/2024/08',
        'MW/2026/21',
        'MZ/2014/13',
        'MW/2024/09',
        'MW/2024/1',
        'MW/2024/10',
        'NA/2026/01',
        'NA/2026/02',
        'NA/2026/03',
        'NA/2026/04',
        'NA/2026/05',
        'NI/2026/01',
        'NI/2026/02',
        'NI/2026/03',
        'RW/2026/01',
        'RW/2026/10',
        'RW/2026/02',
        'RW/2021/01',
        'RW/2024/02',
        'RW/2026/14',
        'RW/2026/03',
        'RW/2026/04',
        'RW/2026/05',
        'RW/2026/06',
        'RW/2026/07',
        'RW/2026/15',
        'RW/2026/08',
        'RW/2026/17',
        'RW/2026/12',
        'RW/2026/09',
        'TZ/2026/01',
        'TZ/2026/02',
        'TZ/2026/26',
        'TZ/2026/18',
        'TZ/2026/19',
        'TZ/2026/20',
        'TZ/2026/10',
        'TZ/2026/11',
        'TZ/2026/13',
        'TZ/2026/03',
        'TZ/2026/21',
        'TZ/2026/07',
        'TZ/2026/22',
        'TZ/2026/08',
        'TZ/2026/14',
        'TZ/2026/15',
        'TZ/2026/04',
        'TZ/2026/05',
        'TZ/2026/30',
        'TZ/2026/06',
        'UG/2026/24',
        'TZ/2026/23',
        'UG/2026/25',
        'TZ/2026/16',
        'UG/2026/08',
        'UG/2021/17',
        'UG/2026/09',
        'UG/2022/15',
        'UG/2026/10',
        'UG/2026/03',
        'UG/2026/04',
        'UG/2026/11',
        'UG/2026/05',
        'UG/2026/06',
        'KE/2020/50',
        'UG/2026/01',
        'UG/2026/16',
        'UG/2026/17',
        'UG/2018/01',
        'UG/2026/02',
        'UG/2026/26',
        'UG/2026/27',
        'UG/2026/13',
        'UG/2026/14',
        'UG/2026/07',
        'ZM/2026/01',
        'ZM/2026/02',
        'ZM/2026/14',
        'ZM/2026/08',
        'ZM/2026/03',
        'ZM/2026/22',
        'ZM/2026/04',
        'ZM/2026/05',
        'ZM/2026/16',
        'ZM/2026/09',
        'ZM/2026/10',
        'ZM/2026/11',
        'ZM/2026/15',
        'ZM/2026/18',
        'ZM/2026/12',
        'ZM/2026/06',
        'ZM/2026/07',
        'ZW/2026/01',
        'ZW/2026/25',
        'ZW/2026/24',
        'ZW/2026/26',
        'ZW/2026/02',
        'ZW/2026/19',
        'ZW/2026/20',
        'ZW/2026/03',
        'ZW/2026/21',
        'ZW/2026/04',
        'ZW/2026/27',
        'ZW/2026/34',
        'ZW/2026/05',
        'ZW/2026/35',
        'ZW/2026/28',
        'ZW/2026/06',
        'ZW/2021/08',
        'ZW/2026/29',
        'ZW/2026/22',
        'ZW/2026/07',
        'ZW/2026/08',
        'ZW/2026/18',
        'ZW/2026/09',
        'ZW/2026/10',
        'ZW/2026/11',
        'ZW/2026/33',
        'ZW/2026/12',
        'ZW/2026/13',
        'ZW/2026/30',
        'ZW/2026/31',
        'ZW/2026/14',
        'SS/2026/10',
        'ZW/2026/15',
        'ZW/2026/16',
        'ZW/2026/17',
        'SS/2026/11',
        'SS/2026/07',
        'SS/2026/08',
        'SS/2026/05',
        'SS/2026/03',
        'SS/2026/04',
        'SS/2026/01',
        'SS/2026/02',
        'BU/2026/01',
        'ET/2026/16',
        'BU/2026/03',
        'CA/2024/02',
        'KE/2019/51',
        'ET/2024/01',
        'ET/2024/03',
        'CA/2024/01',
        'KE/2026/25',
        'KE/2026/65',
        'KE/2026/26',
        'KE/2024/07',
        'KE/2024/18',
        'KE/2024/36',
        'KE/2026/69',
        'MW/2024/06',
        'MW/2024/05',
        'LE/2026/01',
        'MW/2024/11',
        'MW/2024/12',
        'MW/2024/14',
        'MW/2024/16',
        'MW/2024/18',
        'MW/2024/19',
        'MW/2024/20',
        'MW/2026/01',
        'MW/2026/02',
        'MW/2026/03',
        'MW/2026/04',
        'MW/2026/06',
        'NA/2026/06',
        'MW/2026/07',
        'NA/2024/01',
        'NA/2021/01',
        'TZ/2024/12',
        'RW/2026/13',
        'RW/2026/16',
        'TZ/2024/09',
        'TZ/2024/04',
        'TZ/2023/05',
        'TZ/2024/07',
        'TZ/2024/06',
        'TZ/2024/10',
        'TZ/2024/17',
        'TZ/2024/02',
        'MW/2022/21',
        'TZ/2026/31',
        'TZ/2026/25',
        'UG/2024/06',
        'UG/2024/03',
        'TZ/2024/13',
        'TZ/2024/03',
        'UG/2024/07',
        'UG/2026/32',
        'UG/2026/18',
        'ZM/2023/07',
        'UG/2026/23',
        'ZW/2024/20',
        'ZM/2026/17',
        'ZW/2024/19',
        'ZW/2024/09',
        'ZW/2026/23',
        'ZW/2024/16',
        'ZW/2024/10',
        'ZW/2024/08',
        'ZW/2024/02',
        'ZW/2024/07',
        'ZW/2024/13',
        'SS/2026/09',
        'ET/2026/17',
        'ET/2026/18',
        'ZW/2024/01',
        'KE/2024/12',
        'KE/2024/13',
        'KE/2024/14',
        'KE/2024/19',
        'KE/2024/24',
        'BU/2024/03',
        'DC/2023/02',
        'MW/2023/11',
        'KE/2024/15',
        'TZ/2026/24',
        'TZ/2026/37',
        'TZ/2026/17',
        'UG/2024/17',
        'UG/2026/19',
        'UG/2026/20',
        'UG/2026/21',
        'UG/2026/22',
        'ZW/2024/12',
        'NA/2024/04',
        'MW/2026/08',
        'MW/2026/09',
        'TG/2026/01',
        'TG/2026/02',
        'TZ/2026/36',
        'ZM/2024/09',
        'ZM/2024/04',
        'ZW/2024/21',
        'ZW/2024/04',
        'BU/2026/02',
        'ET/2026/19',
        'ET/2026/20',
        'ET/2026/06',
        'DC/2024/03',
        'ET/2024/02',
        'GA/2024/01',
        'KE/2023/14',
        'KE/2022/12',
        'KE/2024/33',
        'KE/2024/35',
        'KE/2024/27',
        'KE/2023/09',
        'KE/2024/37',
        'KE/2026/59',
        'KE/2024/10',
        'KE/2024/48',
        'LE/2020/05',
        'MW/2026/10',
        'MW/2026/11',
        'NA/2024/03',
        'RW/2024/04',
        'TZ/2026/27',
        'TZ/2023/02',
        'UG/2024/01',
        'UG/2026/33',
        'UG/2026/12',
        'MW/2026/12',
        'ET/2026/21',
        'ET/2026/12',
        'ET/2026/22',
        'KE/2024/41',
        'NI/2024/01',
        'KE/2026/54',
        'KE/2024/05',
        'KE/2024/28',
        'KE/2024/06',
        'KE/2024/40',
        'KE/2024/22',
        'KE/2024/34',
        'RW/2024/07',
        'RW/2024/05',
        'RW/2024/08',
        'TZ/2023/07',
        'KE/2023/52',
        'UG/2026/15',
        'ZM/2026/19',
        'ZW/2024/18',
        'ZW/2026/32',
        'BU/2026/07',
        'ET/2026/10',
        'SU/2024/44',
        'ET/2026/23',
        'ET/2026/24',
        'ET/2026/25',
        'KE/2024/11',
        'KE/2024/01',
        'KE/2024/02',
        'KE/2024/20',
        'KE/2021/50',
        'KE/2024/17',
        'ET/2026/26',
        'KE/2026/79',
        'KE/2024/45',
        'KE/2024/47',
        'KE/2024/43',
        'KE/2024/32',
        'KE/2024/49',
        'MW/2026/13',
        'MW/2026/15',
        'MW/2026/16',
        'MW/2026/17',
        'RW/2024/06',
        'TZ/2024/08',
        'KE/2023/54',
        'SU/2024/09',
        'TZ/2020/05',
        'TZ/2023/16',
        'UG/2024/11',
        'UG/2021/12',
        'UG/2024/20',
        'ZM/2020/12',
        'ZM/2026/20',
        'ZW/2023/02',
        'ZW/2022/13',
        'ZW/2024/14',
        'KE/2023/47',
        'UG/2025/30',
        'BU/2026/04',
        'ET/2026/32',
        'ET/2026/07',
        'ET/2026/27',
        'KE/2024/04',
        'KE/2026/58',
        'KE/2024/50',
        'KE/2026/30',
        'KE/2026/61',
        'KE/2024/31',
        'KE/2024/09',
        'ET/2024/23',
        'MD/2023/03',
        'KE/2024/38',
        'ZM/2026/26',
        'KE/2026/66',
        'MW/2020/01',
        'KE/2026/56',
        'SU/2022/09',
        'SW/2022/01',
        'UG/2026/35',
        'BU/2026/08',
        'KE/2026/57',
        'KE/2026/62',
        'ZW/2024/05',
        'ZW/2024/03',
        'ET/2026/08',
        'ET/2026/33',
        'DC/2024/02',
        'KE/2026/67',
        'KE/2026/63',
        'ET/2023/08',
        'KE/2023/27',
        'TZ/2026/32',
        'ZM/2026/21',
        'KE/2021/08',
        'TZ/2026/28',
        'TZ/2024/16',
        'TZ/2026/34',
        'MW/2026/18',
        'RW/2026/19',
        'RW/2026/20',
        'UG/2023/04',
        'UG/2026/29',
        'ZM/2026/27',
        'ET/2026/28',
        'ET/2026/29',
        'ET/2026/14',
        'ET/2026/13',
        'ET/2026/15',
        'ET/2026/30',
        'ET/2026/11',
        'KE/2026/71',
        'BU/2024/01',
        'KE/2019/22',
        'KE/2026/74',
        'KE/2026/68',
        'KE/2019/54',
        'KE/2023/24',
        'LE/2026/03',
        'RW/2026/21',
        'RW/2026/22',
        'RW/2026/23',
        'TZ/2026/29',
        'UG/2026/34',
        'TZ/2023/15',
        'UG/2021/06',
        'ZM/2026/23',
        'ZM/2026/28',
        'ZM/2021/04',
        'ZM/2023/14',
        'ZM/2026/34',
        'ZM/2026/25',
        'KE/2026/64',
        'KE/2024/39',
        'SS/2026/06',
        'LE/2026/02',
        'KE/2026/78',
        'KE/2026/75',
        'UG/2026/30',
        'ZM/2026/33',
        'ET/2026/31',
        'KE/2026/73',
        'KE/2026/70',
        'SU/2026/01',
        'UG/2026/31',
        'ZM/2026/29',
        'ZM/2026/35',
        'ZM/2026/30',
        'KE/2026/72',
        'MW/2026/22',
        'UG/2026/36',
        'TZ/2026/33',
        'KE/2026/77',
        'ZM/2026/36',
        'ZM/2026/32',
        'KE/2026/81',
        'TZ/2026/35',
        'ZW/2025/13',
        'KE/2026/82',
        'ZM/2026/37',
        'BW/2026/01',
        'TZ/2023/03',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun  = $this->option('dry-run');
        $isVerbose = $this->output->isVerbose();

        $validMops   = self::VALID_MOPS;
        $mopOverrides = self::MOP_OVERRIDES;
        $cohort       = self::COHORT;

        if ($isDryRun) {
            $this->warn('[DRY RUN] No changes will be written to the database.');
        }

        $this->info(sprintf('Processing %d PE numbers in the 2026 cohort...', count($cohort)));

        $updatedCount  = 0;
        $notFoundCount = 0;

        foreach ($cohort as $peNumber) {
            $peNumber = trim($peNumber);

            // Fetch trainee joined with programme to get programme name.
            $row = DB::table('trainees as t')
                ->join('programmes as p', 'p.id', '=', 't.programme_id')
                ->whereRaw('TRIM(t.entry_number) = ?', [$peNumber])
                ->select(
                    't.id',
                    't.entry_number',
                    't.mode_of_payment',
                    'p.name as programme_name'
                )
                ->first();

            if (! $row) {
                $notFoundCount++;
                if ($isVerbose) {
                    $this->line(sprintf('  NOT FOUND : %s', $peNumber));
                }
                continue;
            }

            // Determine amount_paid based on programme name.
            $programmeName = $row->programme_name ?? '';
            $amountPaid = (
                stripos($programmeName, 'FCS') !== false
            ) ? 600 : 500;

            // Determine mode_of_payment.
            if (isset($mopOverrides[$peNumber])) {
                // Known override takes precedence.
                $modeOfPayment = $mopOverrides[$peNumber];
                $mopSource = 'override';
            } elseif (in_array($row->mode_of_payment, $validMops, true)) {
                // Keep existing valid value.
                $modeOfPayment = $row->mode_of_payment;
                $mopSource = 'existing';
            } else {
                // Pick a random valid value.
                $modeOfPayment = $validMops[array_rand($validMops)];
                $mopSource = 'random';
            }

            if ($isVerbose) {
                $this->line(sprintf(
                    '  %-15s | programme: %-40s | fee: $%d | mop: %-25s [%s]%s',
                    $peNumber,
                    $programmeName,
                    $amountPaid,
                    $modeOfPayment,
                    $mopSource,
                    $isDryRun ? ' [dry-run]' : ''
                ));
            }

            if (! $isDryRun) {
                DB::table('trainees')
                    ->where('id', $row->id)
                    ->update([
                        'amount_paid'      => $amountPaid,
                        'invoice_status'   => 'Sent',
                        'mode_of_payment'  => $modeOfPayment,
                    ]);
            }

            $updatedCount++;
        }

        $this->newLine();
        $this->info('--- Summary ---');

        if ($isDryRun) {
            $this->warn(sprintf('Would update : %d trainee(s)', $updatedCount));
        } else {
            $this->info(sprintf('Updated      : %d trainee(s)', $updatedCount));
        }

        $this->line(sprintf('Not found    : %d PE number(s)', $notFoundCount));

        if ($notFoundCount > 0) {
            $this->warn('Some PE numbers were not matched in the trainees table. Run with -v to see which ones.');
        }

        return self::SUCCESS;
    }
}
