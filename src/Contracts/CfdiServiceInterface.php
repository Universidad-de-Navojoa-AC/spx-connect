<?php

namespace Unav\SpxConnect\Contracts;

interface CfdiServiceInterface
{
    public function stamp(string $xml, array|string $emailsSending, bool $useEmailTemplate = false, string $htmlEmailOptional = null, bool $generateFolio = true): ?string;

    public function verify(string $rfc, array $uuidList): array;

    public function link(int $journalNumber, string $rfc, array $uuidList, array $extensionFiles, array $amounts, ?int $line, bool $unlink = false): bool;

    public function multiLink(int $journalNumber, string $rfc, array $lines, bool $unlink = false): bool;

    public function upload(string $rfc, array $xmlFiles): array;

    public function getXml(string $uuid): ?string;

    public function setUserId(?string $userId = 'global'): self;
}
