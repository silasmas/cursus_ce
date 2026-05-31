<?php

/**
 * Enveloppe les champs Filament dans des sections (cartes).
 */
$base = __DIR__.'/../../app/Filament/Resources';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));

foreach ($iterator as $file) {
  if (! $file->isFile() || ! str_ends_with($file->getFilename(), 'Form.php')) {
    continue;
  }

  $path = $file->getPathname();
  $content = file_get_contents($path);

  if (str_contains($content, 'Section::make')) {
    continue;
  }

  if (! str_contains($content, '->components([')) {
    continue;
  }

  if (! str_contains($content, 'use Filament\\Schemas\\Schema;')) {
    continue;
  }

  $content = str_replace(
    "use Filament\\Schemas\\Schema;\n",
    "use Filament\\Schemas\\Components\\Section;\nuse Filament\\Schemas\\Schema;\n",
    $content,
  );

  $content = preg_replace(
    '/return \$schema\s*\n\s*->components\(\[\s*\n/s',
    "return \$schema\n            ->components([\n                Section::make('Informations générales')\n                    ->description('Renseignez les champs ci-dessous.')\n                    ->schema([\n",
    $content,
    1,
  );

  $content = preg_replace(
    '/\n            \]\);\s*\n    \}\s*\n\}/s',
    "\n                    ])\n                    ->columns(2),\n            ]);\n    }\n}\n",
    $content,
    1,
  );

  file_put_contents($path, $content);
  echo "Form patched: {$path}\n";
}

echo "Forms done.\n";
