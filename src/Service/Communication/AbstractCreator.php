<?php

declare(strict_types=1);

namespace Service\Communication;

use Service\Communication\Exception\CommunicationException;

class AbstractCreator
{
    public function sendMessage(string $type): ICommunication
    {
        if ($type === 'email') {
            return new Email('sender_email');
        }

        if ($type === 'sms') {
            return new Sms('sender_sms');
        }

        throw new CommunicationException('unknown communication type');
    }
}
