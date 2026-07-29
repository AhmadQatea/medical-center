@props([
    'theme' => [],
])

@php
    use App\Support\CssSanitizer;

    $map = config('theme.css_variables', []);
    $base = config('theme.default', []);
    $tokens = array_merge($base, is_array($theme) ? $theme : []);

    $declarations = [];

    foreach ($map as $key => $cssVar) {
        if (! array_key_exists($key, $tokens) || $tokens[$key] === null || $tokens[$key] === '') {
            continue;
        }

        $value = CssSanitizer::declarationValue((string) $tokens[$key]);

        if ($value === '') {
            continue;
        }

        if (in_array($key, ['logo', 'logo_dark', 'favicon'], true)) {
            $path = str_starts_with($value, 'http') || str_starts_with($value, 'data:')
                ? CssSanitizer::url($value)
                : asset(ltrim($value, '/'));

            if ($path === '') {
                continue;
            }

            $declarations[] = $cssVar.': url(\''.CssSanitizer::declarationValue($path).'\')';
            continue;
        }

        $declarations[] = $cssVar.': '.$value;
    }
@endphp

@if (count($declarations))
    <style data-theme-provider="clinic">
        :root {
            {!! implode(";\n            ", $declarations) !!};
        }
    </style>
@endif

@if (! empty($tokens['favicon']))
    @php
        $favicon = CssSanitizer::url((string) $tokens['favicon']);
        $faviconHref = str_starts_with($favicon, 'http') || str_starts_with($favicon, 'data:')
            ? $favicon
            : asset(ltrim($favicon, '/'));
    @endphp
    @if ($faviconHref !== '')
        <link rel="icon" href="{{ $faviconHref }}">
    @endif
@endif
