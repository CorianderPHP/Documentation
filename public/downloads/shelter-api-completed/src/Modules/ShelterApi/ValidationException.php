<?php
declare(strict_types=1);

namespace Modules\ShelterApi;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public function __construct(private readonly array $fields)
    {
        parent::__construct('The request body is invalid.');
    }

    public function fields(): array
    {
        return $this->fields;
    }
}
