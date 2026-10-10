<?php
declare(strict_types=1);

namespace Tests\Docs;

use PHPUnit\Framework\TestCase;

final class DownloadedForumTest extends TestCase
{
    public function testCompletedDemoRunsWithoutWebsiteClassesOrLayouts(): void
    {
        $process = proc_open(
            [PHP_BINARY, PROJECT_ROOT . '/tests/fixtures/downloaded-forum.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            PROJECT_ROOT
        );
        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process), $errors . $output);
        self::assertSame('', $errors);
        $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(200, $result['guest']);
        self::assertTrue($result['guideLink']);
        self::assertSame('/forum-demo/login', $result['guestAdmin']);
        self::assertSame(403, $result['blocked']);
        self::assertSame('/forum-demo', $result['login']);
        self::assertSame(200, $result['admin']);
        self::assertTrue($result['write']['ok']);
        self::assertTrue($result['write']['demo']);
        self::assertFalse($result['persisted']);
        self::assertSame('/forum-demo/topics/1', $result['moderationReturn']);
        self::assertTrue($result['flashShown']);
        self::assertFalse($result['flashRepeated']);
    }
}
