<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Escrow release window (business days)
    |--------------------------------------------------------------------------
    | A digital product order's funds are released to the vendor this many
    | *business* working days after purchase, provided no support ticket is
    | open for that purchase. Weekends and the holidays below are skipped.
    */
    'release_days' => 3,

    /*
    |--------------------------------------------------------------------------
    | Support ticket window (hours)
    |--------------------------------------------------------------------------
    | A buyer may open a support ticket against a purchase only within this
    | many hours of paying. Measured in calendar hours from when the escrow
    | was held (i.e. payment success).
    */
    'ticket_window_hours' => 24,

    /*
    |--------------------------------------------------------------------------
    | Physical return window (calendar days)
    |--------------------------------------------------------------------------
    | A buyer may request a return for a PHYSICAL order this many days after it
    | is marked delivered — provided the funds are still held in escrow (i.e.
    | the buyer hasn't already confirmed receipt and the funds haven't
    | auto-released). Opening a return freezes the escrow auto-release.
    */
    'return_window_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | Nigerian public holidays
    |--------------------------------------------------------------------------
    | 'annual' entries (format m-d) recur every year. 'dates' entries
    | (format Y-m-d) are movable feasts — Christian (Easter) and Islamic
    | (Eid) holidays whose dates shift year to year. The Islamic dates are
    | declared by moon sighting, so the values below are approximate and
    | should be confirmed/extended each year by an admin.
    */
    'holidays' => [

        'annual' => [
            '01-01', // New Year's Day
            '05-01', // Workers' Day
            '06-12', // Democracy Day
            '10-01', // Independence Day
            '12-25', // Christmas Day
            '12-26', // Boxing Day
        ],

        'dates' => [
            // 2026
            '2026-03-20', // Eid-el-Fitr (approx.)
            '2026-03-23', // Eid-el-Fitr holiday (approx.)
            '2026-04-03', // Good Friday
            '2026-04-06', // Easter Monday
            '2026-05-27', // Eid-el-Kabir (approx.)
            '2026-05-28', // Eid-el-Kabir holiday (approx.)
            '2026-08-26', // Eid-el-Maulud (approx.)

            // 2027
            '2027-03-10', // Eid-el-Fitr (approx.)
            '2027-03-26', // Good Friday
            '2027-03-29', // Easter Monday
            '2027-05-16', // Eid-el-Kabir (approx.)
            '2027-08-15', // Eid-el-Maulud (approx.)
        ],
    ],
];
