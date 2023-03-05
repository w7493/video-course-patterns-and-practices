<?php

declare(strict_types=1);

namespace Service\Communication;

class AnotherCreator extends Creator
{
    protected function prepareEmail(): ICommunication
    {
        return new NewEmail(static::QUEUE_EMAIL);
    }
}
