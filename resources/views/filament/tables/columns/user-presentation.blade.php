@php
  $presentation = $getState() ?? [];
  $name = $presentation['name'] ?? '—';
  $initials = $presentation['initials'] ?? '?';
  $avatarUrl = $presentation['avatar_url'] ?? null;
  $email = $presentation['email'] ?? null;
@endphp

<div class="flex items-center gap-3 py-0.5">
  @if ($avatarUrl)
    <img
      src="{{ $avatarUrl }}"
      alt=""
      class="h-9 w-9 shrink-0 rounded-full object-cover bg-white ring-1 ring-gray-200"
    />
  @else
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-100 text-xs font-bold text-orange-700">
      {{ $initials }}
    </span>
  @endif
  <div class="min-w-0">
    <p class="truncate text-sm font-medium text-gray-950">{{ $name }}</p>
    @if ($email)
      <p class="truncate text-xs text-gray-500">{{ $email }}</p>
    @endif
  </div>
</div>
