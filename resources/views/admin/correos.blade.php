@extends('layouts.app')

@section('titulo', 'Correos')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Correos enviados</h1>
            <p class="subtitulo" style="margin:0">Cada correo que manda CafeQR: si salió hacia Gmail o si falló y por qué. Los fallidos se pueden reenviar.</p>
        </div>
    </div>

    @if ($modoPrueba)
        <div class="aviso">⚠️ El correo está en <strong>modo prueba</strong> (MAIL_MAILER=log): ningún correo sale a internet. Configura Gmail en el <code>.env</code> y prueba con <code>php artisan correo:probar tucorreo@gmail.com</code>.</div>
    @elseif ($remitente && $usuarioSmtp && strtolower($remitente) !== strtolower($usuarioSmtp))
        <div class="aviso">⚠️ <strong>MAIL_FROM_ADDRESS</strong> ({{ $remitente }}) no es igual a <strong>MAIL_USERNAME</strong> ({{ $usuarioSmtp }}). Gmail puede mandar los correos a spam: pon el mismo Gmail en los dos y ejecuta <code>php artisan config:clear</code>.</div>
    @endif

    <div class="kpis">
        <div class="kpi verde-kpi"><div class="num">{{ $resumen['enviado'] ?? 0 }}</div><div class="rotulo">Enviados</div></div>
        <div class="kpi {{ ($resumen['fallido'] ?? 0) ? 'alerta-kpi' : '' }}"><div class="num">{{ $resumen['fallido'] ?? 0 }}</div><div class="rotulo">Fallidos</div></div>
        <div class="kpi"><div class="num">{{ $resumen['prueba'] ?? 0 }}</div><div class="rotulo">En modo prueba (no salieron)</div></div>
    </div>

    <section class="tarjeta">
        <form class="filtros" method="GET" action="{{ route('admin.correos') }}">
            <div class="campo">
                <label for="q">Buscar</label>
                <input type="search" id="q" name="q" value="{{ $buscar }}" placeholder="Correo o asunto" maxlength="60">
            </div>
            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="enviado" {{ $estado === 'enviado' ? 'selected' : '' }}>Enviados</option>
                    <option value="fallido" {{ $estado === 'fallido' ? 'selected' : '' }}>Fallidos</option>
                    <option value="prueba" {{ $estado === 'prueba' ? 'selected' : '' }}>Modo prueba</option>
                </select>
            </div>
            <button type="submit" class="btn">Filtrar</button>
        </form>

        @if ($correos->isEmpty())
            <p class="ayuda">Todavía no hay correos{{ $estado ? ' con ese estado' : '' }}.</p>
        @else
            <div class="tabla-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Para</th><th>Correo</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($correos as $correo)
                            <tr>
                                <td>{{ $correo->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $correo->para }}</td>
                                <td><strong>{{ $correo->tipo_texto }}</strong><br><span class="ayuda">{{ $correo->asunto }}</span></td>
                                <td>
                                    @if ($correo->estado === 'enviado')
                                        <span class="etiqueta disponible">Enviado</span>
                                    @elseif ($correo->estado === 'fallido')
                                        <span class="etiqueta agotado">Falló</span>
                                        <br><span class="ayuda" style="display:inline-block;max-width:340px;word-break:break-word">{{ \Illuminate\Support\Str::limit($correo->error, 220) }}</span>
                                    @else
                                        <span class="etiqueta">Modo prueba</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($correo->html && $correo->estado !== 'enviado')
                                        <form method="POST" action="{{ route('admin.correos.reenviar', $correo) }}">
                                            @csrf
                                            <button type="submit" class="btn chico">Reenviar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $correos->links() }}
        @endif
    </section>

    <section class="tarjeta">
        <h2>Si un correo dice “Enviado” pero no aparece</h2>
        <p class="ayuda" style="margin:0">“Enviado” significa que Gmail lo aceptó. Si el cliente no lo ve: que revise <strong>Spam</strong> y <strong>Promociones</strong>, y que su correo institucional esté bien escrito y exista. Desde Mi perfil, el cliente puede pedir que se le reenvíe el correo de confirmación.</p>
    </section>
@endsection
