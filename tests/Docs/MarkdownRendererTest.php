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
# Hello Docs

Use `php coriander` and [Start](/start).

```php
echo 'ok';
```
MD);

        self::assertStringContainsString('id="hello-docs"', $result['html']);
        self::assertStringContainsString('data-language="php"', $result['html']);
        self::assertStringContainsString('code-lang="php"', $result['html']);
        self::assertStringContainsString('href="/start"', $result['html']);
        self::assertSame('Hello Docs', $result['headings'][0]['text']);
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
}
