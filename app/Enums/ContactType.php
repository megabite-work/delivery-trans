<?php
namespace App\Enums;

enum ContactType: string
{
    case PHONE = 'PHONE';
    case EMAIL = 'EMAIL';
    case PERSON = 'PERSON';
    case MESSENGER = 'MESSENGER';
    case ADDRESS = 'ADDRESS';
    case WEB = 'WEB';
    case OTHER = 'OTHER';

}
