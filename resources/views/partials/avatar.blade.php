@php($tam = $tam ?? 40)
@if ($user->foto_url)
    <img class="avatar" src="{{ $user->foto_url }}" alt="Foto de {{ $user->name }}" width="{{ $tam }}" height="{{ $tam }}" style="width:{{ $tam }}px;height:{{ $tam }}px">
@else
    <span class="avatar inicial" style="width:{{ $tam }}px;height:{{ $tam }}px;font-size:{{ round($tam * 0.42) }}px">{{ $user->inicial }}</span>
@endif
