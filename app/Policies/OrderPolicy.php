<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $order->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Order $order): bool
    {
        return $order->customer_id === $user->id
            && $order->status === OrderStatus::PENDING;
    }

    public function pay(User $user, Order $order): bool
    {
        return $this->view($user, $order)
            && in_array($order->status, [OrderStatus::PENDING, OrderStatus::PAYMENT_PENDING]);
    }
}