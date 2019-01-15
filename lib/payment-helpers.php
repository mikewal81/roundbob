<?php
/**
 * Manages inserting transactions into the database
 *
 * @param resource[$db]
 * @param array[$tx_data]
 * @param int[$wallet_id]
 *
 * @return array
 */
function createTransaction( $db, $tx_data, $wallet_id ) {
    // Insert the transaction data
    $db->insert( 'transactions', $tx_data );
    // TODO: Record this transaction in the logs
    // Now get the current wallet balance
    $current_balance = $db->get( 'wallets','balance',[ 'id' => $wallet_id ]);
    // Update the wallet with the new Balance
    $db->update( 'wallets',
        [
            'balance[+]' => $tx_data['amount'],
            'activated'  => 1
        ],
        [ 'id' => $wallet_id ]
    );
    // If all went well, return an array with the transaction status
    return [
        'success' => true,
        'data'    => [
            'ref_no'   => $tx_data['ref_no'],
            'prev_bal' => $current_balance,
            'currency' => $tx_data['currency'],
            'new_bal'  => ( $current_balance + $tx_data['amount'] )
        ]
    ];
}