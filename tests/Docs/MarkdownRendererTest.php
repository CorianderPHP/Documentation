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

    public function testRendersWorkflowFencesAsStepBlocks(): void
    {
        $result = (new MarkdownRenderer())->render(<<<'MD'
```workflow
Route|Matches `/articles/{id}`.
Controller|Loads the article and prepares view data.
View|Renders public/public_views/articles/show/index.php
```
MD);

        self::assertStringContainsString('class="workflow"', $result['html']);
        self::assertStringContainsString('role="listitem"', $result['html']);
        self::assertStringContainsString('class="workflow-marker">1</div>', $result['html']);
        self::assertStringContainsString('Route', $result['html']);
        self::assertStringContainsString('Matches <code', $result['html']);
        self::assertStringNotContainsString('<pre', $result['html']);
    }

    public function testRendersChoicesFencesAsNonNumberedOptions(): void
    {
        $result = (new MarkdownRenderer())->render(<<<'MD'
```choices
Static view|Fixed content pages.|/documentation/static-views
Assets|Images and public files.
```
MD);

        self::assertStringContainsString('class="choices"', $result['html']);
        self::assertStringContainsString('class="choices-item choices-link"', $result['html']);
        self::assertStringContainsString('role="listitem"', $result['html']);
        self::assertStringContainsString('href="/documentation/static-views"', $result['html']);
        self::assertStringContainsString('Fixed content pages.', $result['html']);
        self::assertStringContainsString('Images and public files.', $result['html']);
        self::assertStringNotContainsString('workflow-marker', $result['html']);
        self::assertStringNotContainsString('<pre', $result['html']);
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
