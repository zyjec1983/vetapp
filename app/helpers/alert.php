<?php
/**
 * Location: vetapp/app/helpers/alert.php
 */
class Alert
{
    public static function success($message)
    {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => $message
        ];
    }

    public static function error($message)
    {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => $message
        ];
    }

    public static function warning($message)
    {
        $_SESSION['alert'] = [
            'type' => 'warning',
            'message' => $message
        ];
    }
}
?>