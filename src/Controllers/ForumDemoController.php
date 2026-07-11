<?php
declare(strict_types=1);

namespace Controllers;

use CorianderCore\Core\Router\ViewRenderer;
use Modules\ForumDemo\Auth\DemoAuth;
use Modules\ForumDemo\Data\DemoForumRepository;
use Modules\ForumDemo\Data\DemoUserRepository;
use Modules\ForumDemo\Permissions\DemoPermissionService;
use Modules\ForumDemo\Writes\DemoWriteGuard;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class ForumDemoController
{
    private const FLASH_KEY = 'forum_demo_flash';

    private ViewRenderer $view;
    private DemoAuth $auth;
    private DemoForumRepository $forum;
    private DemoUserRepository $users;
    private DemoPermissionService $permissions;
    private DemoWriteGuard $writeGuard;

    public function __construct()
    {
        $this->view = new ViewRenderer();
        $this->auth = new DemoAuth();
        $this->forum = new DemoForumRepository();
        $this->users = new DemoUserRepository();
        $this->permissions = new DemoPermissionService();
        $this->writeGuard = new DemoWriteGuard($this->permissions);
    }

    public function index(ServerRequestInterface $request): void
    {
        $this->captureGuideReturn($request);

        $this->render('forum-demo', [
            'topics' => $this->forum->topics(),
        ]);
    }

    public function login(): void
    {
        $this->render('forum-demo/login', [
            'error' => null,
            'demoAccounts' => $this->demoAccounts(),
        ]);
    }

    public function authenticate(ServerRequestInterface $request): ?Response
    {
        $body = $request->getParsedBody();
        $data = is_array($body) ? $body : [];

        $quickRole = is_string($data['quick_role'] ?? null) ? $data['quick_role'] : '';
        $authenticated = $quickRole !== ''
            ? $this->auth->loginAs($quickRole)
            : $this->auth->login((string) ($data['email'] ?? ''), (string) ($data['password'] ?? ''));

        if ($authenticated) {
            return new Response(302, ['Location' => '/forum-demo'], '');
        }

        $this->render('forum-demo/login', [
            'error' => 'Invalid demo credentials.',
            'demoAccounts' => $this->demoAccounts(),
        ]);

        return null;
    }

    public function logout(): Response
    {
        $this->auth->logout();
        return new Response(302, ['Location' => '/forum-demo'], '');
    }

    public function topics(): void
    {
        $this->render('forum-demo/topics', [
            'categories' => $this->forum->categories(),
            'topics' => $this->forum->topics(),
            'flash' => $this->consumeFlash(),
        ]);
    }

    public function showTopic(string $id): ?Response
    {
        $topic = $this->forum->topic((int) $id);
        if ($topic === null) {
            return new Response(404, [], 'Topic not found.');
        }

        $this->render('forum-demo/topic', [
            'topic' => $topic,
            'replies' => $this->forum->repliesForTopic($topic['id']),
            'flash' => $this->consumeFlash(),
        ]);

        return null;
    }

    public function storeTopic(ServerRequestInterface $request): Response
    {
        $result = $this->fakeWriteFromRequest($request, 'topic.create', 'create topic');
        return $this->flashAndRedirect($result, '/forum-demo/topics');
    }

    public function storeReply(ServerRequestInterface $request, string $id): Response
    {
        $topic = $this->forum->topic((int) $id);
        if ($topic === null) {
            return new Response(404, [], 'Topic not found.');
        }

        $result = $this->fakeWriteFromRequest($request, 'reply.create', 'create reply');
        return $this->flashAndRedirect($result, '/forum-demo/topics/' . (int) $topic['id']);
    }

    public function admin(): void
    {
        $this->renderAdmin($this->consumeFlash());
    }

    public function adminUsers(): void
    {
        $this->render('forum-demo/admin-users', [
            'users' => $this->users->all(),
            'flash' => $this->consumeFlash(),
        ]);
    }

    public function updateUserRole(ServerRequestInterface $request): Response
    {
        $result = $this->fakeWriteFromRequest($request, 'user.manage', 'update user role');
        return $this->flashAndRedirect($result, '/forum-demo/admin/users');
    }

    public function moderateTopic(ServerRequestInterface $request): Response
    {
        $payload = $this->payloadFromRequest($request);
        $result = $this->writeGuard->fakeWrite($this->auth->currentUser(), 'topic.lock', 'moderate topic', $payload);

        if (($payload['return_to'] ?? '') === 'topic') {
            return $this->flashAndRedirect($result, '/forum-demo/topics/' . (int) ($payload['topic_id'] ?? 0));
        }

        return $this->flashAndRedirect($result, '/forum-demo/admin');
    }

    public function moderateReply(ServerRequestInterface $request): Response
    {
        $payload = $this->payloadFromRequest($request);
        $result = $this->writeGuard->fakeWrite($this->auth->currentUser(), 'reply.moderate', 'moderate reply', $payload);

        if (($payload['return_to'] ?? '') === 'topic') {
            return $this->flashAndRedirect($result, '/forum-demo/topics/' . (int) ($payload['topic_id'] ?? 0));
        }

        return $this->flashAndRedirect($result, '/forum-demo/admin');
    }

    /**
     * @param array<string,mixed> $data
     */
    private function render(string $view, array $data): void
    {
        $user = $this->auth->currentUser();
        $this->view->render($view, $data + [
            'currentUser' => $user,
            'permissions' => $this->permissions->matrix($user),
            'demoAccounts' => $this->demoAccounts(),
            'forumGuideReturn' => $this->guideReturn(),
        ]);
    }

    /**
     * @return array<int,array{role:string,email:string,password:string,label:string}>
     */
    private function demoAccounts(): array
    {
        return [
            ['role' => 'admin', 'email' => 'admin@example.com', 'password' => 'demo-admin', 'label' => 'Admin'],
            ['role' => 'member', 'email' => 'user@example.com', 'password' => 'demo-user', 'label' => 'Member'],
        ];
    }

    /**
     * @return array{ok:bool,demo:bool,status:int,message:string,action:string}
     */
    private function fakeWriteFromRequest(ServerRequestInterface $request, string $ability, string $action): array
    {
        return $this->writeGuard->fakeWrite($this->auth->currentUser(), $ability, $action, $this->payloadFromRequest($request));
    }

    /**
     * @return array<string,mixed>
     */
    private function payloadFromRequest(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();
        return is_array($body) ? $body : [];
    }

    /**
     * @param array{ok:bool,demo:bool,status:int,message:string,action:string}|null $flash
     */
    private function renderAdmin(?array $flash): void
    {
        $this->render('forum-demo/admin', [
            'users' => $this->users->all(),
            'categories' => $this->forum->categories(),
            'topics' => $this->forum->topics(),
            'moderationQueue' => $this->forum->moderationQueue(),
            'flash' => $flash,
        ]);
    }

    /**
     * @param array{ok:bool,demo:bool,status:int,message:string,action:string} $flash
     */
    private function flashAndRedirect(array $flash, string $location): Response
    {
        $_SESSION[self::FLASH_KEY] = $flash;
        return new Response(302, ['Location' => $location], '');
    }

    /**
     * @return array{ok:bool,demo:bool,status:int,message:string,action:string}|null
     */
    private function consumeFlash(): ?array
    {
        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        if (!is_array($flash)) {
            return null;
        }

        return [
            'ok' => (bool) ($flash['ok'] ?? false),
            'demo' => (bool) ($flash['demo'] ?? false),
            'status' => (int) ($flash['status'] ?? 500),
            'message' => (string) ($flash['message'] ?? ''),
            'action' => (string) ($flash['action'] ?? ''),
        ];
    }

    private function captureGuideReturn(ServerRequestInterface $request): void
    {
        $from = $request->getQueryParams()['from'] ?? null;
        if (!is_string($from)) {
            return;
        }

        $path = parse_url($from, PHP_URL_PATH);
        if (!is_string($path) || !str_starts_with($path, '/guided-projects/forum')) {
            return;
        }

        $query = parse_url($from, PHP_URL_QUERY);
        $_SESSION['forum_demo_guide_return'] = $path . (is_string($query) && $query !== '' ? '?' . $query : '');
    }

    private function guideReturn(): string
    {
        $return = $_SESSION['forum_demo_guide_return'] ?? '/guided-projects/forum';
        return is_string($return) && str_starts_with($return, '/guided-projects/forum') ? $return : '/guided-projects/forum';
    }
}
