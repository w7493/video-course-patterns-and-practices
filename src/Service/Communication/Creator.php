<?php

declare(strict_types=1);

namespace Service\Communication;

class Creator extends AbstractCreator
{
    public function sendMessage(string $type): ICommunication
    {
        if ($type === 'push') {
            return new Sms('sender_push');
        }

        return self::sendMessage($type);
    }
}
