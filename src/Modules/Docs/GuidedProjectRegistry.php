<?php
declare(strict_types=1);

namespace Modules\Docs;

final class GuidedProjectRegistry
{
    /**
     * @var array<string,GuidedProject>|null
     */
    private ?array $projects = null;

    /**
     * @return array<string,GuidedProject>
     */
    public function all(): array
    {
        if ($this->projects !== null) {
            return $this->projects;
        }

        $this->projects = [
            'forum' => new GuidedProject(
                key: 'forum',
                title: 'Forum project',
                navTitle: 'Forum',
                listEyebrow: 'Featured guided project',
                listTitle: 'Forum with permissions',
                listDescription: 'Build a forum from scratch with SQLite persistence, demo auth, role permissions, protected public-demo writes, admin routes, API endpoints, and a MySQL production path.',
                listTags: ['Beginner to intermediate', '45-60 min'],
                eyebrow: 'Forum guided project',
                baseSlug: 'projects/forum',
                basePath: '/guided-projects/forum',
                view: 'examples/forum',
                searchPlaceholder: 'Search routes, permissions, SQLite...',
                emptySearchText: 'Search for permissions, routes, SQLite, admin, API, or protected demo writes.',
                noResultsText: 'No forum guide pages matched this search.',
                detailTags: ['Beginner to intermediate', 'SQLite to MySQL path'],
                downloadPath: '/public/downloads/forum-completed.zip',
                downloadLabel: 'Download completed app',
                downloadTitle: 'Download project files',
                downloadDescription: 'This download does not include the CorianderPHP framework. Start from a CorianderPHP project, then use the completed app files as a reference.',
                scrollMemoryKey: 'forum-guide-nav',
                groups: [
                    'Overview' => [
                        ['slug' => 'projects/forum/index', 'label' => 'What We Are Building'],
                        ['slug' => 'projects/forum/setup', 'label' => 'Project Structure'],
                    ],
                    'Foundation' => [
                        ['slug' => 'projects/forum/data-model', 'label' => 'SQLite Data Model'],
                        ['slug' => 'projects/forum/routes', 'label' => 'Routes'],
                        ['slug' => 'projects/forum/controllers', 'label' => 'Controllers'],
                        ['slug' => 'projects/forum/views', 'label' => 'Views'],
                    ],
                    'Access Control' => [
                        ['slug' => 'projects/forum/authentication', 'label' => 'Demo Authentication'],
                        ['slug' => 'projects/forum/permissions', 'label' => 'Permissions'],
                        ['slug' => 'projects/forum/admin-area', 'label' => 'Admin Middleware'],
                        ['slug' => 'projects/forum/write-service', 'label' => 'Write Service'],
                    ],
                    'Public Demo Safety' => [
                        ['slug' => 'projects/forum/fake-writes', 'label' => 'Protected Demo Writes'],
                        ['slug' => 'projects/forum/api', 'label' => 'API Endpoints'],
                    ],
                    'Deployment' => [
                        ['slug' => 'projects/forum/real-database', 'label' => 'MySQL And Production'],
                    ],
                ],
                headerActions: [],
                liveDemo: [
                    'path' => '/forum-demo',
                    'title' => 'Open the safe demo',
                    'description' => 'Use the demo when a guide step asks you to verify behavior. Local projects write to SQLite; this public demo validates writes without persisting visitor content.',
                    'cta' => 'Open forum demo',
                    'withReturn' => true,
                ],
            ),
            'shelter-api' => new GuidedProject(
                key: 'shelter-api',
                title: 'Shelter API',
                navTitle: 'Shelter API',
                listEyebrow: 'REST API guided project',
                listTitle: 'Shelter API',
                listDescription: 'Build a complete JSON REST API for shelter animals: cats, dogs, bunnies, and birds. The guide covers SQLite schema scripts, route files, controllers, filtering, validation, errors, and the MySQL path.',
                listTags: ['Intermediate', '25-40 min'],
                eyebrow: 'REST API guided project',
                baseSlug: 'projects/shelter-api',
                basePath: '/guided-projects/shelter-api',
                view: 'examples/shelter-api',
                searchPlaceholder: 'Search routes, validation, JSON...',
                emptySearchText: 'Search for animals, routes, filters, validation, SQLite, MySQL, or JSON errors.',
                noResultsText: 'No shelter API guide pages matched this search.',
                detailTags: ['25-40 min', 'JSON API', 'SQLite to MySQL path'],
                downloadPath: '/public/downloads/shelter-api-completed.zip',
                downloadLabel: 'Download completed API',
                downloadTitle: 'Download completed API',
                downloadDescription: 'This download does not include the CorianderPHP framework. Start from a CorianderPHP project, then copy these app-owned files into it.',
                scrollMemoryKey: 'shelter-api-guide-nav',
                groups: [
                    'Overview' => [
                        ['slug' => 'projects/shelter-api/index', 'label' => 'What We Are Building'],
                        ['slug' => 'projects/shelter-api/setup', 'label' => 'Project Structure'],
                    ],
                    'API Foundation' => [
                        ['slug' => 'projects/shelter-api/data-model', 'label' => 'Shelter Data Model'],
                        ['slug' => 'projects/shelter-api/routes', 'label' => 'REST Routes'],
                        ['slug' => 'projects/shelter-api/controllers', 'label' => 'API Controllers'],
                    ],
                    'Production Behavior' => [
                        ['slug' => 'projects/shelter-api/filtering-validation', 'label' => 'Filtering And Validation'],
                        ['slug' => 'projects/shelter-api/errors', 'label' => 'Errors And Versioning'],
                        ['slug' => 'projects/shelter-api/playground', 'label' => 'API Playground'],
                    ],
                ],
                headerActions: [
                    ['path' => '/guided-projects/shelter-api/playground', 'label' => 'Open playground'],
                ],
                extraSections: [
                    'projects/shelter-api/playground' => 'examples/shelter-api/_playground',
                ],
            ),
        ];

        return $this->projects;
    }

    public function find(string $key): ?GuidedProject
    {
        return $this->all()[$key] ?? null;
    }

    public function projectForSlug(string $slug): ?GuidedProject
    {
        foreach ($this->all() as $project) {
            if ($project->slugBelongsToProject($slug)) {
                return $project;
            }
        }

        return null;
    }
}
