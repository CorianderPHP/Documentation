<?php
declare(strict_types=1);

namespace Tests\Docs;

use Modules\Docs\MarkdownRenderer;
use PHPUnit\Framework\TestCase;

final class MarkdownRendererTest extends TestCase
{
    public function testRendersHeadingsCodeFencesAndInlineMarkup(): void
    {
        $result = (new MarkdownRenderer())->render(<<<'MD'
# Hello Documentation

Use `php coriander` and [Documentation](/documentation).

```php
echo 'ok';
```
MD);

        self::assertStringContainsString('id="hello-documentation"', $result['html']);
        self::assertStringContainsString('data-language="php"', $result['html']);
        self::assertStringContainsString('code-lang="php"', $result['html']);
        self::assertStringContainsString('href="/documentation"', $result['html']);
        self::assertSame('Hello Documentation', $result['headings'][0]['text']);
    }

    public function testKeepsStructureCodeFenceLanguage(): void
    {
        $result = (new MarkdownRenderer())->render(<<<'MD'
```structure
src/Modules/Example
```
MD);

        self::assertStringContainsString('data-language="structure"', $result['html']);
    }

    public function testRendersMarkdownTables(): void
    {
        $result = (new MarkdownRenderer())->render(<<<'MD'
| Step | Why |
| --- | --- |
| Routes | URL contract |
| Controllers | Request flow |
MD);

        self::assertStringContainsString('<table', $result['html']);
        self::assertStringContainsString('<th', $result['html']);
        self::assertStringContainsString('Routes', $result['html']);
        self::assertStringNotContainsString('| --- |', $result['html']);
    }

    public function testRendersOrderedLists(): void
    {
        $result = (new MarkdownRenderer())->render(<<<'MD'
1. [CLI](/documentation/cli)
2. [Routing](/documentation/routing)
MD);

        self::assertStringContainsString('<ol', $result['html']);
        self::assertStringContainsString('href="/documentation/cli"', $result['html']);
        self::assertStringNotContainsString('1. ', $result['html']);
    }
}
