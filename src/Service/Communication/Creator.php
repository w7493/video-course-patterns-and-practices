<?php

declare(strict_types=1);

namespace Service\Communication;

use InvalidArgumentException;

class Creator extends AbstractCreator
{
    public function sendMessage(string $type): ICommunication
    {
        if (strlen($type) > 3) {
            throw new InvalidArgumentException('Invalid type');
        }

        if ($type === 'push') {
            return new Sms('sender_push');
        }

        try {
            $communcation = parent::sendMessage($type);
        } catch (\Throwable) {
            return new Email('sender_email');
        }

        return $communcation;
    }
}
