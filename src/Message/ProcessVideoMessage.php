<?php

declare(strict_types=1);

namespace App\Message;

final readonly class ProcessVideoMessage
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
