@php
    $wrapperClass = $wrapperClass ?? 'empty-state';
    $iconClass = $iconClass ?? 'empty-state-icon';
    $heading = $heading ?? 'h3';
@endphp

<div class="{{ $wrapperClass }}">
    <div class="{{ $iconClass }}"><i class="{{ $icon }}"></i></div>
    @if ($heading === 'h2')
        <h2>{{ $title }}</h2>
    @else
        <h3>{{ $title }}</h3>
    @endif
    @if (! empty($message))
        <p>{{ $message }}</p>
    @endif
    @if (! empty($actions))
        <div class="empty-state-actions">{!! $actions !!}</div>
    @endif
</div>
