<?php

declare(strict_types=1);

namespace App\Payments\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case MobileMoney = 'mobile_money';
    case Bank = 'bank';
    case Wallet = 'wallet';
    case Ussd = 'ussd';
    case Other = 'other';
}
