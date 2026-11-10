<?php

namespace App\Contracts;

interface SmsGateway
{
    public function send(string $mobile, string $message): void;
}
