<?php
// app/Domain/Leads/DTOs/LeadData.php
namespace App\Domain\Leads\DTOs;

class LeadData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $company,
        public string $message,
        public string $status = 'new',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            company: $data['company'] ?? null,
            message: $data['message'],
            status: $data['status'] ?? 'new',
        );
    }
}
