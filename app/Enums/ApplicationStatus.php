<?php

namespace App\Enums;

/**
 * The stages a job application moves through.
 * Each case is one column on the kanban board, in this order.
 */
enum ApplicationStatus: string
{
    case Wishlist = 'wishlist';
    case Applied = 'applied';
    case Interview = 'interview';
    case Offer = 'offer';
    case Rejected = 'rejected';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Tailwind classes for the small coloured dot shown on each column. */
    public function dotColor(): string
    {
        return match ($this) {
            self::Wishlist => 'bg-slate-400',
            self::Applied => 'bg-sky-500',
            self::Interview => 'bg-amber-500',
            self::Offer => 'bg-emerald-500',
            self::Rejected => 'bg-rose-500',
        };
    }
}
