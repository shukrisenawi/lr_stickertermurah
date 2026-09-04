<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Notifications\AdminUpdateNotification;

final class CustomerNotifier
{
    public static function forOrder(
        Order $order,
        string $title,
        string $message,
        string $url,
        string $type = 'order',
    ): void {
        $order->loadMissing('user');

        self::send($order->user, $title, $message, $url, $type);
    }

    public static function forInvoice(
        Invoice $invoice,
        string $title,
        string $message,
        string $url,
        string $type = 'invoice',
    ): void {
        $invoice->loadMissing(['user', 'order.user']);

        self::send($invoice->user ?? $invoice->order?->user, $title, $message, $url, $type);
    }

    public static function forCompletedTracking(Order $order): void
    {
        $order->loadMissing('invoice');
        $trackingNo = $order->customerTrackingNo();

        if (! $trackingNo) {
            return;
        }

        $message = "Order {$order->order_no} telah selesai. No. tracking: {$trackingNo}.";

        if ($order->invoice) {
            self::forInvoice(
                $order->invoice,
                'No. tracking tersedia',
                $message,
                route('member.invoices.show', $order->invoice),
                'tracking',
            );

            return;
        }

        self::forOrder(
            $order,
            'No. tracking tersedia',
            $message,
            route('member.orders.show', $order),
            'tracking',
        );
    }

    private static function send(
        ?User $user,
        string $title,
        string $message,
        string $url,
        string $type,
    ): void {
        if (! $user || $user->is_admin) {
            return;
        }

        $user->notify(new AdminUpdateNotification($title, $message, $url, $type));
    }
}
