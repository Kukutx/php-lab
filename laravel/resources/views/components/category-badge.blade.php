@props(['category'])
<span {{ $attributes->merge(['class' => 'badge text-bg-'.$category->badge()]) }}>{{ $category->label() }}</span>
