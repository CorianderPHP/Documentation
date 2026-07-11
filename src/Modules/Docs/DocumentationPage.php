<?php
declare(strict_types=1);

namespace Modules\Docs;

final class DocumentationPage
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $description,
        public readonly string $section,
        public readonly string $body,
        public readonly string $html,
        public readonly array $headings,
    ) {
    }
}
