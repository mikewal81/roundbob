<?php
/**
 * Handles Errors throughout the application
 *
 * @param $err_no int
 * @param $type string
 * @param $message string
 *
 * @return array
 */
function handleError( $err_no, $type, $message = null ) {
    // TODO: All errors will be logged here since this will be the central error handle
    // Set default error message
    $message = $message ?? 'An error occurred';
    if ( $type === 'mysql' ) {
        switch ( $err_no ) {
            default:
                $message = 'A database error occurred';
                break;
        }
    }
    if ( $type === 'verification' ) {
        switch ( $err_no ) {
            case 100:
                $message = 'Could not verify the transaction';
                break;
        }
    }
    if ( $type === 'login' ) {
        switch ( $err_no ) {
            case 100:
                $message = 'The user could not be found';
                break;
            case 101:
                $message = 'Incorrect password provided';
                break;
        }
    }
    if ( $type === 'register' ) {
        switch ( $err_no ) {
            case 100:
                $message = 'The email address already exists';
                break;
        }
    }
    return [
        'err_no'      => $err_no,
        'err_message' => $message
    ];
}