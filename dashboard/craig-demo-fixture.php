<?php
function getCraigSupervisorDemoFixture(bool $isDemoAccount, string $userName): ?array
{
    if (!$isDemoAccount || strcasecmp(trim($userName), 'Craig Fisher') !== 0) {
        return null;
    }

    $rows = [
        ['date'=>'Jan 15, 2026','timestamp'=>strtotime('2026-01-15 10:20:00'),'description'=>'DEMO — Opening Balance Transfer','category'=>'Income','status'=>'Successful','amount'=>450000.00],
        ['date'=>'Feb 20, 2026','timestamp'=>strtotime('2026-02-20 13:10:00'),'description'=>'DEMO — Consulting & Advisory Income','category'=>'Income','status'=>'Successful','amount'=>320000.00],
        ['date'=>'Mar 12, 2026','timestamp'=>strtotime('2026-03-12 09:45:00'),'description'=>'DEMO — Property Tax & Legal Fees','category'=>'Bills','status'=>'Successful','amount'=>-85000.00],
        ['date'=>'Apr 19, 2026','timestamp'=>strtotime('2026-04-19 14:05:00'),'description'=>'DEMO — Investment Distribution','category'=>'Investment','status'=>'Successful','amount'=>510000.00],
        ['date'=>'May 07, 2026','timestamp'=>strtotime('2026-05-07 11:30:00'),'description'=>'DEMO — Real Estate Acquisition Costs','category'=>'Transfer','status'=>'Successful','amount'=>-115000.00],
        ['date'=>'Jun 23, 2026','timestamp'=>strtotime('2026-06-23 15:15:00'),'description'=>'DEMO — Commercial Lease Proceeds','category'=>'Income','status'=>'Successful','amount'=>620000.00],
        ['date'=>'Jul 17, 2026','timestamp'=>strtotime('2026-07-17 12:40:00'),'description'=>'DEMO — Property Management & Travel','category'=>'Bills','status'=>'Successful','amount'=>-96400.00],
        ['date'=>'Aug 29, 2026','timestamp'=>strtotime('2026-08-29 10:50:00'),'description'=>'DEMO — Private Portfolio Distribution','category'=>'Investment','status'=>'Successful','amount'=>300000.00],
        ['date'=>'Sep 22, 2026','timestamp'=>strtotime('2026-09-22 14:35:00'),'description'=>'DEMO — Transfer to Skyline Construction — Project Payment','category'=>'Transfer','status'=>'Successful','amount'=>-700000.00],
    ];

    $balance=0.0;$credits=0.0;$debits=0.0;$creditCount=0;$debitCount=0;
    foreach ($rows as &$row) {
        $balance += (float)$row['amount'];
        $row['balance']=$balance;
        $row['currency']='EUR';
        $row['counter_currency']='';
        $row['counter_amount']=null;
        $row['fx_rate']=null;
        $row['fx_spread_bps']=null;
        $row['account_number']='DEMO';
        $row['status_key']='successful';
        if ($row['amount'] >= 0) { $credits += (float)$row['amount']; $creditCount++; }
        else { $debits += abs((float)$row['amount']); $debitCount++; }
    }
    unset($row);

    return [
        'currency'=>'EUR',
        'balance'=>$balance,
        'credits'=>$credits,
        'debits'=>$debits,
        'credit_count'=>$creditCount,
        'debit_count'=>$debitCount,
        'rows'=>array_reverse($rows),
    ];
}
