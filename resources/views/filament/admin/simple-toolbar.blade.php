@if (filament()->hasDarkMode() && ! filament()->hasDarkModeForced() && ! filament()->auth()->check())
  <div class="fi-phila-simple-toolbar" x-data="{ close() {} }">
    <x-filament-panels::theme-switcher />
  </div>
@endif
