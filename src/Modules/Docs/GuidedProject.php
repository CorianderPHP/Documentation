<?php
declare(strict_types=1);

namespace Modules\Docs;

final class GuidedProject
{
    /**
     * @param array<int,string> $listTags
     * @param array<int,string> $detailTags
     * @param array<string,array<int,array{slug:string,label:string}>> $groups
     * @param array<int,array{path:string,label:string}> $headerActions
     * @param array{path:string,title:string,description:string,cta:string,withReturn:bool}|null $liveDemo
     * @param array<string,string> $extraSections
     */
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $navTitle,
        public readonly string $listEyebrow,
        public readonly string $listTitle,
        public readonly string $listDescription,
        public readonly array $listTags,
        public readonly string $eyebrow,
        public readonly string $baseSlug,
        public readonly string $basePath,
        public readonly string $view,
        public readonly string $searchPlaceholder,
        public readonly string $emptySearchText,
        public readonly string $noResultsText,
        public readonly array $detailTags,
        public readonly string $downloadPath,
        public readonly string $downloadLabel,
        public readonly string $downloadTitle,
        public readonly string $downloadDescription,
        public readonly string $scrollMemoryKey,
        public readonly array $groups,
        public readonly array $headerActions = [],
        public readonly ?array $liveDemo = null,
        public readonly array $extraSections = [],
    ) {
    }

    public function indexSlug(): string
    {
        return $this->baseSlug . '/index';
    }

    public function docSlug(?string $slug = null): string
    {
        $suffix = $slug !== null && $slug !== '' ? '/' . trim($slug, '/') : '/index';
        return $this->baseSlug . $suffix;
    }

    public function pathForSlug(string $slug): string
    {
        if ($slug === $this->indexSlug()) {
            return $this->basePath;
        }

        return $this->basePath . '/' . substr($slug, strlen($this->baseSlug . '/'));
    }

    public function searchPath(): string
    {
        return $this->basePath . '/search';
    }

    public function slugBelongsToProject(string $slug): bool
    {
        return str_starts_with($slug, $this->baseSlug . '/');
    }
}
