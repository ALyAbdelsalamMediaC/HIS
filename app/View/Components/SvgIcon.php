<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\File;

class SvgIcon extends Component
{
  public string $name;
  public string $size;
  public string $color;
  public ?string $class; // Allow custom CSS classes

  public function __construct(string $name, string $size = '24', string $color = 'currentColor', ?string $class = null)
  {
    $this->name = $name;
    $this->size = $size;
    $this->color = $color;
    $this->class = $class;
  }

  public function render()
  {
    $svgPath = public_path("images/icons/{$this->name}.svg");

    if (!File::exists($svgPath)) {
      return ''; // Return empty if SVG doesn't exist
    }

    $svgContent = File::get($svgPath);

    // Ensure dynamic size
    $attributes = [
      'width="' . $this->size . '"',
      'height="' . $this->size . '"',
    ];

    // Add class if provided
    if ($this->class) {
      $attributes[] = 'class="' . $this->class . '"';
    }

    // Apply attributes inside the <svg> tag
    $svgContent = preg_replace('/<svg/', '<svg ' . implode(' ', $attributes), $svgContent);

    // Ensure the SVG background is transparent
    $svgContent = preg_replace('/fill=["\'](.*?)["\']/', 'fill="none"', $svgContent, 1);

    // Apply dynamic stroke color
    $svgContent = preg_replace('/stroke=["\'](.*?)["\']/', 'stroke="' . $this->color . '"', $svgContent);

    // Apply dynamic color to all paths, circles, and groups
    $svgContent = preg_replace('/<(path|circle|g|rect|line|polygon|polyline)(.*?)fill=["\'](.*?)["\']/', '<$1$2 fill="' . $this->color . '"', $svgContent);

    return $svgContent;
  }
}