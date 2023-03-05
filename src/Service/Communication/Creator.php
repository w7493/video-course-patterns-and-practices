<?php

declare(strict_types=1);

namespace Service\Communication;

use Service\Communication\Exception\CommunicationException;

class Creator
{
    public const TYPE_EMAIL = 'email';
    public const QUEUE_EMAIL = 'sender_email';
    public const TYPE_SMS = 'sms';
    public const QUEUE_SMS = 'sender_sms';

    public function sendMessage(string $type): ICommunication
    {
        if ($type === self::TYPE_EMAIL) {
            return new Email(self::QUEUE_EMAIL);
        }

        if ($type === self::TYPE_SMS) {
            return new Sms(self::QUEUE_SMS);
        }

        throw new CommunicationException('unknown communication type');
    }
}
