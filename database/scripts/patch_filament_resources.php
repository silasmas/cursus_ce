<?php

/**
 * Ajoute le trait HasFrenchFilamentLabels à toutes les ressources Filament.
 */
$base = __DIR__.'/../../app/Filament/Resources';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));

foreach ($iterator as $file) {
  if (! $file->isFile() || ! str_ends_with($file->getFilename(), 'Resource.php')) {
    continue;
  }

  $path = $file->getPathname();
  $content = file_get_contents($path);

  if (str_contains($content, 'HasFrenchFilamentLabels')) {
    continue;
  }

  $content = str_replace(
    "use Filament\Resources\Resource;\n",
    "use App\Filament\Concerns\HasFrenchFilamentLabels;\nuse Filament\Resources\Resource;\n",
    $content,
  );

  $content = preg_replace(
    '/class (\w+) extends Resource\n\{\n/',
    "class $1 extends Resource\n{\n    use HasFrenchFilamentLabels;\n\n",
    $content,
    1,
  );

  file_put_contents($path, $content);
  echo "Patched: {$path}\n";
}

echo "Done.\n";
