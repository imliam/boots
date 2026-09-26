<?php

namespace Boots;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class GuidelineComposer
{
    protected string $userGuidelineDir = '.ai/guidelines';

    /** @var Collection<string, array> */
    protected Collection $guidelines;

    private array $storedSnippets = [];

    /**
     * Auto discovers the guideline files and composes them into one string.
     */
    public function compose(): string
    {
        return self::composeGuidelines($this->guidelines());
    }

    public function customGuidelinePath(string $path = ''): string
    {
        return File::basePath($this->userGuidelineDir.'/'.ltrim($path, '/'));
    }

    /**
     * Static method to compose guidelines from a collection.
     *
     * @param  Collection<string, array{content: string, name: string, path: ?string, custom: bool}>  $guidelines
     */
    public static function composeGuidelines(Collection $guidelines): string
    {
        return str_replace("\n\n\n\n", "\n\n", trim($guidelines
            ->filter(fn ($guideline) => ! empty(trim($guideline['content'])))
            ->map(fn ($guideline, $key) => "\n=== {$key} rules ===\n\n".trim($guideline['content']))
            ->join("\n\n"))
        );
    }

    /**
     * @return string[]
     */
    public function used(): array
    {
        return $this->guidelines()->keys()->toArray();
    }

    /**
     * @return Collection<string, array>
     */
    public function guidelines(): Collection
    {
        if (! empty($this->guidelines)) {
            return $this->guidelines;
        }

        return $this->guidelines = $this->find();
    }

    /**
     * Key is the 'guideline key' and value is the rendered content.
     *
     * @return Collection<string, array>
     */
    protected function find(): Collection
    {
        $guidelines = collect();

        $userGuidelines = $this->guidelinesDir($this->customGuidelinePath());
        $pathsUsed = $guidelines->pluck('path');

        foreach ($userGuidelines as $guideline) {
            if ($pathsUsed->contains($guideline['path'])) {
                continue; // Don't include this twice if it's an override
            }
            $guidelines->put('.ai/'.$guideline['name'], $guideline);
        }

        return $guidelines
            ->where(fn (array $guideline) => ! empty(trim($guideline['content'])));
    }

    /**
     * @return array<array{content: string, name: string, path: ?string, custom: bool}>
     */
    protected function guidelinesDir(string $dirPath): array
    {
        if (! is_dir($dirPath)) {
            $dirPath = str_replace('/', DIRECTORY_SEPARATOR, __DIR__.'/../../.ai/'.$dirPath);
        }

        try {
            $finder = Finder::create()
                ->files()
                ->in($dirPath)
                ->name('*.md');
        } catch (DirectoryNotFoundException $e) {
            return [];
        }

        return array_map(fn ($file) => $this->guideline($file->getRealPath()), iterator_to_array($finder));
    }

    /**
     * @return array{content: string, name: string, path: ?string, custom: bool}
     */
    protected function guideline(string $path): array
    {
        $path = $this->guidelinePath($path);
        if (is_null($path)) {
            return ['content' => '', 'name' => '', 'path' => null, 'custom' => false];
        }

        $content = file_get_contents($path);
        $content = $this->processBootsSnippets($content);

        $content = str_replace(array_keys($this->storedSnippets), array_values($this->storedSnippets), $content);
        $this->storedSnippets = []; // Clear for next use

        return [
            'content' => trim($content),
            'name' => str_replace('.md', '', basename($path)),
            'path' => $path,
            'custom' => str_contains($path, $this->customGuidelinePath()),
        ];
    }

    private function processBootsSnippets(string $content): string
    {
        return preg_replace_callback('/(?<!@)@bootssnippet\(\s*(?P<nameQuote>[\'"])(?P<name>[^\1]*?)\1(?:\s*,\s*(?P<langQuote>[\'"])(?P<lang>[^\3]*?)\3)?\s*\)(?P<content>.*?)@endbootssnippet/s', function ($matches) {
            $name = $matches['name'];
            $lang = ! empty($matches['lang']) ? $matches['lang'] : 'html';
            $snippetContent = $matches['content'];

            $placeholder = '___BOOTS_SNIPPET_'.count($this->storedSnippets).'___';

            $this->storedSnippets[$placeholder] = '<code-snippet name="'.$name.'" lang="'.$lang.'">'."\n".$snippetContent."\n".'</code-snippet>'."\n\n";

            return $placeholder;
        }, $content);
    }

    protected function prependPackageGuidelinePath(string $path): string
    {
        $path = preg_replace('/\.md$/', '', $path);
        $path = str_replace('/', DIRECTORY_SEPARATOR, __DIR__.'/../../.ai/'.$path.'.md');

        return $path;
    }

    protected function prependUserGuidelinePath(string $path): string
    {
        $path = preg_replace('/\.md$/', '', $path);
        $path = str_replace('/', DIRECTORY_SEPARATOR, $this->customGuidelinePath($path.'.md'));

        return $path;
    }

    protected function guidelinePath(string $path): ?string
    {
        // Relative path, prepend our package path to it
        if (! file_exists($path)) {
            $path = $this->prependPackageGuidelinePath($path);
            if (! file_exists($path)) {
                return null;
            }
        }

        $path = realpath($path);

        // If this is a custom guideline, return it unchanged
        if (str_contains($path, $this->customGuidelinePath())) {
            return $path;
        }

        // The path is not a custom guideline, check if the user has an override for this
        $basePath = realpath(__DIR__.'/../../');
        $relativePath = ltrim(str_replace([$basePath, '.ai'.DIRECTORY_SEPARATOR, '.ai/'], '', $path), '/\\');
        $customPath = $this->prependUserGuidelinePath($relativePath);

        return file_exists($customPath) ? $customPath : $path;
    }
}
