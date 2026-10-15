@extends('emails.layout')

@section('contenido')
    <p style="margin:0 0 12px;">✅ <strong>{{ $mensaje }}</strong></p>
    <p style="margin:0 0 16px;color:#6e5a5f;">Enviado el {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.</p>
@endsection
