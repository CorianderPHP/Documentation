<?php
declare(strict_types=1);

namespace Modules\Docs;

final class GuidedProjectNavigation
{
    public function __construct(private readonly GuidedProject $project)
    {
    }

    /**
     * @param DocumentationPage[] $pages
     * @return array<string,array<int,array{page:DocumentationPage,number:int,label:string,path:string}>>
     */
    public function grouped(array $pages): array
    {
        $bySlug = $this->bySlug($pages);
        $groups = [];
        $number = 1;

        foreach ($this->project->groups as $group => $items) {
            foreach ($items as $item) {
                $page = $bySlug[$item['slug']] ?? null;
                if ($page === null) {
                    continue;
                }

                $groups[$group] ??= [];
                $groups[$group][] = [
                    'page' => $page,
                    'number' => $number,
                    'label' => $item['label'],
                    'path' => $this->project->pathForSlug($page->slug),
                ];
                $number++;
            }
        }

        return $groups;
    }

    /**
     * @param DocumentationPage[] $pages
     * @return DocumentationPage[]
     */
    public function orderedPages(array $pages): array
    {
        $ordered = [];
        foreach ($this->grouped($pages) as $groupItems) {
            foreach ($groupItems as $item) {
                $ordered[] = $item['page'];
            }
        }

        return $ordered;
    }

    /**
     * @param DocumentationPage[] $pages
     * @return array{previous:?array{page:DocumentationPage,path:string,label:string},next:?array{page:DocumentationPage,path:string,label:string}}
     */
    public function adjacent(array $pages, string $activeSlug): array
    {
        $flat = [];
        foreach ($this->grouped($pages) as $groupItems) {
            foreach ($groupItems as $item) {
                $flat[] = $item;
            }
        }

        foreach ($flat as $index => $item) {
            if ($item['page']->slug !== $activeSlug) {
                continue;
            }

            $previous = $flat[$index - 1] ?? null;
            $next = $flat[$index + 1] ?? null;

            return [
                'previous' => $previous !== null ? $this->adjacentItem($previous) : null,
                'next' => $next !== null ? $this->adjacentItem($next) : null,
            ];
        }

        return ['previous' => null, 'next' => null];
    }

    /**
     * @param DocumentationPage[] $pages
     * @return array<string,DocumentationPage>
     */
    private function bySlug(array $pages): array
    {
        $bySlug = [];
        foreach ($pages as $page) {
            $bySlug[$page->slug] = $page;
        }

        return $bySlug;
    }

    /**
     * @param array{page:DocumentationPage,number:int,label:string,path:string} $item
     * @return array{page:DocumentationPage,path:string,label:string}
     */
    private function adjacentItem(array $item): array
    {
        return [
            'page' => $item['page'],
            'path' => $item['path'],
            'label' => $item['label'],
        ];
    }
}
