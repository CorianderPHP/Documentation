<?php
declare(strict_types=1);

namespace App\Modules\ForumDemo\Data;

final class DemoForumRepository
{
    /**
     * @return array<int,array{id:int,name:string,description:string,visibility:string}>
     */
    public function categories(): array
    {
        return [
            ['id' => 1, 'name' => 'Getting Started', 'description' => 'Install the framework and understand the project layout.', 'visibility' => 'public'],
            ['id' => 2, 'name' => 'Handlers and Routes', 'description' => 'Questions about mapping URLs to method files and request handlers.', 'visibility' => 'public'],
            ['id' => 3, 'name' => 'Moderation Queue', 'description' => 'Admin and moderator-only review workflow.', 'visibility' => 'staff'],
        ];
    }

    /**
     * @return array<int,array{id:int,category_id:int,category:string,title:string,author:string,role:string,replies:int,locked:bool,status:string,excerpt:string,body:string,created_at:string,updated_at:string}>
     */
    public function topics(): array
    {
        return [
            [
                'id' => 1,
                'category_id' => 1,
                'category' => 'Getting Started',
                'title' => 'How do I create my first view?',
                'author' => 'Sam Member',
                'role' => 'member',
                'replies' => 2,
                'locked' => false,
                'status' => 'Open',
                'excerpt' => 'I generated a view, but I am not sure which files I should edit first.',
                'body' => 'I ran `php coriander make:view dashboard` and now I have `src/Views/dashboard.php`. Should I return `Responses::view()` from a route, or create an action class first when the page needs data?',
                'created_at' => '2026-07-10 09:12',
                'updated_at' => '2026-07-10 09:41',
            ],
            [
                'id' => 2,
                'category_id' => 2,
                'category' => 'Handlers and Routes',
                'title' => 'When should I use src/Routes?',
                'author' => 'Mira Admin',
                'role' => 'admin',
                'replies' => 1,
                'locked' => false,
                'status' => 'Open',
                'excerpt' => 'Method files under src/Routes define URLs; directory middleware protects related endpoints.',
                'body' => 'Create a method file under `src/Routes` for each endpoint, such as `forum-demo/topics/index.get.php`. CorianderPHP discovers it automatically. Put `_middleware.php` in a directory when all its child endpoints need shared protection.',
                'created_at' => '2026-07-10 10:03',
                'updated_at' => '2026-07-10 10:24',
            ],
            [
                'id' => 3,
                'category_id' => 3,
                'category' => 'Moderation Queue',
                'title' => 'Review flagged onboarding replies',
                'author' => 'Nora Moderator',
                'role' => 'moderator',
                'replies' => 1,
                'locked' => true,
                'status' => 'Locked',
                'excerpt' => 'Staff-only moderation example showing permissions in the demo project.',
                'body' => 'This topic demonstrates a locked discussion. Members can read it, but only staff actions such as hiding a reply or unlocking the topic should appear to administrators.',
                'created_at' => '2026-07-10 11:17',
                'updated_at' => '2026-07-10 11:20',
            ],
        ];
    }

    /**
     * @return array{id:int,category_id:int,category:string,title:string,author:string,role:string,replies:int,locked:bool,status:string,excerpt:string,body:string,created_at:string,updated_at:string}|null
     */
    public function topic(int $id): ?array
    {
        foreach ($this->topics() as $topic) {
            if ($topic['id'] === $id) {
                return $topic;
            }
        }

        return null;
    }

    /**
     * @return array<int,array{id:int,topic_id:int,author:string,role:string,status:string,body:string,created_at:string}>
     */
    public function repliesForTopic(int $topicId): array
    {
        $replies = [
            ['id' => 1, 'topic_id' => 1, 'author' => 'Mira Admin', 'role' => 'admin', 'status' => 'Visible', 'created_at' => '2026-07-10 09:29', 'body' => 'Keep display markup in the private view. Return Responses::view() from a method file, passing title and prepared data. Extract an action class when request coordination grows.'],
            ['id' => 2, 'topic_id' => 1, 'author' => 'Sam Member', 'role' => 'member', 'status' => 'Visible', 'created_at' => '2026-07-10 09:41', 'body' => 'That makes sense. I will keep the first static page simple and move reusable behavior into modules when the feature grows.'],
            ['id' => 3, 'topic_id' => 2, 'author' => 'Nora Moderator', 'role' => 'moderator', 'status' => 'Visible', 'created_at' => '2026-07-10 10:24', 'body' => 'An admin directory can declare its permission middleware once in _middleware.php. All child method files inherit it, along with root CSRF protection.'],
            ['id' => 4, 'topic_id' => 3, 'author' => 'Mira Admin', 'role' => 'admin', 'status' => 'Needs review', 'created_at' => '2026-07-10 11:20', 'body' => 'This reply is intentionally marked for review so the admin UI can show a hide-reply action without saving public visitor content.'],
        ];

        return array_values(array_filter($replies, static fn(array $reply): bool => $reply['topic_id'] === $topicId));
    }

    /**
     * @return array<int,array{id:int,type:string,title:string,subject:string,status:string,author:string,action:string}>
     */
    public function moderationQueue(): array
    {
        return [
            ['id' => 3, 'type' => 'topic', 'title' => 'Locked topic', 'subject' => 'Review flagged onboarding replies', 'status' => 'Locked', 'author' => 'Nora Moderator', 'action' => 'unlock topic'],
            ['id' => 4, 'type' => 'reply', 'title' => 'Reply flagged for review', 'subject' => 'This reply is intentionally marked for review...', 'status' => 'Needs review', 'author' => 'Mira Admin', 'action' => 'hide reply'],
            ['id' => 2, 'type' => 'topic', 'title' => 'Open topic', 'subject' => 'When should I use src/Routes?', 'status' => 'Open', 'author' => 'Mira Admin', 'action' => 'lock topic'],
        ];
    }
}
