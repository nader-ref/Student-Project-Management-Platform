@props([
    'title',
    'description' => null,
    'breadcrumb' => null,
])

<section class="acceptance-hero admin-page-hero">
    <div class="acceptance-hero-inner">
        <div>
            @if ($breadcrumb)
                <nav class="breadcrumb" aria-label="Breadcrumb">{!! $breadcrumb !!}</nav>
            @endif
            <h1>{{ $title }}</h1>
            @if ($description)
                <p>{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="hero-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>

@include('admin.partials.tabs')
