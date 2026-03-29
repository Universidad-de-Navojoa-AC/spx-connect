<?php

namespace Unav\SpxConnect\Contracts;

use Unav\SpxConnect\Enums\JournalFileType;

interface JournalServiceInterface
{
    public function getJournalTypes(): array;

    public function postJournalEntry(array $payload): array;

    public function downloadJournalFile(int $journalNumber, string $book, JournalFileType $journalFileType, string $logo = null): ?string;

    public function setUserId(?string $userId = 'global'): self;
}
