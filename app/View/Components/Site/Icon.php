<?php

namespace App\View\Components\Site;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

/**
 * Inline, self-hosted Material Symbols icon: <x-site.icon name="calendar_month" class="size-5" />.
 * Icons live in resources/svg/icons (no third-party font requests).
 */
class Icon extends Component
{
    /** @var array<string, string> */
    private static array $cache = [];

    public function __construct(public string $name) {}

    public function svg(string $class = ''): string
    {
        $svg = self::$cache[$this->name] ??= $this->load();

        return str_replace(
            '<svg ',
            '<svg class="'.e(trim('shrink-0 '.$class)).'" fill="currentColor" aria-hidden="true" focusable="false" ',
            $svg,
        );
    }

    public function render(): View
    {
        return view('components.site.icon');
    }

    private function load(): string
    {
        $path = resource_path("svg/icons/{$this->name}.svg");

        if (! is_file($path)) {
            throw new InvalidArgumentException("Icon [{$this->name}] does not exist.");
        }

        $svg = (string) file_get_contents($path);

        return (string) preg_replace('/\s(height|width)="\d+"/', '', $svg);
    }
}
