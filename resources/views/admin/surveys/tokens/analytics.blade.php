@extends('layouts.admin')

@section('title', 'Analíticas de Tokens - ' . $survey->title)

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 class="h2 fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-bar-chart-fill"></i> Analíticas de Tokens
            </h1>
            <p class="text-muted mb-0">{{ $survey->title }}</p>
        </div>
        <a href="{{ route('admin.surveys.tokens.index', $survey) }}" class="btn btn-sm"
           style="background: #f1f5f9; color: #64748b; border: none; padding: 0.5rem 0.875rem; border-radius: 8px; font-weight: 500;">
            <i class="bi bi-arrow-left"></i> Volver a Tokens
        </a>
    </div>

    <!-- Tokens por Fuente y Estado -->
    <div class="modern-card mb-4">
        <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold" style="color: #667eea;">
                <i class="bi bi-pie-chart-fill"></i> Distribución por Fuente y Estado
            </h5>
        </div>
        <div style="padding: 1.5rem;">
            @if($tokensBySource->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Fuente</th>
                                <th class="text-center" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Estado</th>
                                <th class="text-center" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Cantidad</th>
                                <th class="text-center" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalTokens = $tokensBySource->sum('count');
                                $grouped = $tokensBySource->groupBy('source');
                            @endphp
                            @foreach($grouped as $source => $tokens)
                                @php
                                    $sourceTotal = $tokens->sum('count');
                                @endphp
                                @foreach($tokens as $token)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        @if($loop->first)
                                            <td rowspan="{{ $tokens->count() }}" style="padding: 1rem;">
                                                <strong style="color: #667eea;">{{ $source }}</strong>
                                                <br>
                                                <small class="text-muted">Total: {{ number_format($sourceTotal) }}</small>
                                            </td>
                                        @endif
                                        <td class="text-center" style="padding: 1rem;">
                                            @if($token->status === 'pending')
                                                <span class="badge" style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">
                                                    <i class="bi bi-clock"></i> Pendiente
                                                </span>
                                            @elseif($token->status === 'used')
                                                <span class="badge" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">
                                                    <i class="bi bi-check-circle"></i> Usado
                                                </span>
                                            @else
                                                <span class="badge" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">
                                                    <i class="bi bi-x-circle"></i> Expirado
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center" style="padding: 1rem;">
                                            <strong style="color: #1e293b;">{{ number_format($token->count) }}</strong>
                                        </td>
                                        <td class="text-center" style="padding: 1rem;">
                                            @php
                                                $percentage = $totalTokens > 0 ? ($token->count / $totalTokens) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 25px; background: #f1f5f9; border-radius: 8px;">
                                                <div class="progress-bar"
                                                     role="progressbar"
                                                     style="width: {{ $percentage }}%; background: @if($token->status === 'pending') linear-gradient(135deg, #f093fb 0%, #f5576c 100%) @elseif($token->status === 'used') linear-gradient(135deg, #11998e 0%, #38ef7d 100%) @else linear-gradient(135deg, #ee0979 0%, #ff6a00 100%) @endif; border-radius: 8px; color: white; font-weight: 600;">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-graph-up" style="font-size: 2.5rem; color: #667eea;"></i>
                        </div>
                    </div>
                    <p class="text-muted">No hay datos de tokens disponibles.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Votos por Opción (Gráficos) -->
    <div class="modern-card mb-4">
        <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem; background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);">
            <h5 class="mb-0 fw-bold" style="color: #667eea;">
                <i class="bi bi-graph-up-arrow"></i> Distribución de Votos por Opción
            </h5>
        </div>
        <div style="padding: 1.5rem;">
            <p class="text-muted mb-4">Visualiza qué opciones votaron los tokens de esta encuesta</p>

            @if(count($votesByOption) > 0)
                @foreach($votesByOption as $questionData)
                    <div class="mb-5">
                        <h6 class="fw-bold mb-3" style="color: #1e293b;">{{ $questionData['question_text'] }}</h6>

                        @php
                            $totalVotes = collect($questionData['options'])->sum('votes');
                        @endphp

                        @if($totalVotes > 0)
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Gráfico de barras -->
                                    @foreach($questionData['options'] as $option)
                                        @php
                                            $percentage = $totalVotes > 0 ? ($option['votes'] / $totalVotes) * 100 : 0;
                                        @endphp
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-medium" style="color: #1e293b;">{{ $option['option_text'] }}</span>
                                                <span class="badge rounded-pill" style="background-color: {{ $option['color'] }}; padding: 0.375rem 0.75rem; font-weight: 600; color: white;">
                                                    {{ number_format($option['votes']) }} votos ({{ number_format($percentage, 1) }}%)
                                                </span>
                                            </div>
                                            <div class="progress" style="height: 30px; background: #f1f5f9; border-radius: 8px;">
                                                <div class="progress-bar fw-bold text-white"
                                                     role="progressbar"
                                                     style="width: {{ $percentage }}%; background-color: {{ $option['color'] }}; border-radius: 8px;"
                                                     aria-valuenow="{{ $percentage }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    @if($percentage > 5)
                                                        {{ number_format($percentage, 1) }}%
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-md-4">
                                    <!-- Gráfico circular con Canvas -->
                                    <canvas id="pieChart{{ $loop->index }}" width="250" height="250"></canvas>
                                </div>
                            </div>
                        @else
                            <div style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.05) 0%, rgba(0, 242, 254, 0.05) 100%); border: 1px solid rgba(79, 172, 254, 0.2); border-radius: 12px; padding: 1rem;">
                                <i class="bi bi-info-circle" style="color: #4facfe;"></i> Esta pregunta aún no tiene votos con tokens.
                            </div>
                        @endif

                        @if(!$loop->last)
                            <hr class="my-4">
                        @endif
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-bar-chart" style="font-size: 2.5rem; color: #667eea;"></i>
                        </div>
                    </div>
                    <p class="text-muted">No hay preguntas en esta encuesta.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Tokens Sospechosos (Múltiples Intentos) -->
    <div class="modern-card mb-4" style="border: 2px solid rgba(238, 9, 121, 0.3);">
        <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem; background: linear-gradient(135deg, rgba(238, 9, 121, 0.05) 0%, rgba(255, 106, 0, 0.05) 100%);">
            <h5 class="mb-0 fw-bold" style="color: #ee0979;">
                <i class="bi bi-exclamation-triangle-fill"></i> Tokens Sospechosos
                <span class="badge ms-2" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">{{ $suspiciousTokens->count() }}</span>
            </h5>
        </div>
        <div style="padding: 1.5rem;">
            <p class="text-muted">Tokens con múltiples intentos de votación (posible compartición o fraude)</p>

            @if($suspiciousTokens->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th style="width: 300px; color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Token</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Fuente</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Campaign ID</th>
                                <th class="text-center" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Intentos</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Estado</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Primer Uso</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Último Intento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suspiciousTokens as $token)
                                <tr style="border-bottom: 1px solid #f1f5f9; @if($token->vote_attempts > 5) background: rgba(238, 9, 121, 0.05); @elseif($token->vote_attempts > 3) background: rgba(240, 147, 251, 0.05); @endif">
                                    <td style="padding: 1rem;">
                                        <code class="small" style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; color: #667eea;">{{ Str::limit($token->token, 20) }}</code>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 0.375rem 0.75rem; font-weight: 600;">{{ $token->source }}</span>
                                    </td>
                                    <td style="padding: 1rem;">
                                        @if($token->campaign_id)
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.15) 0%, rgba(0, 242, 254, 0.15) 100%); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">{{ $token->campaign_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="padding: 1rem;">
                                        <span class="badge" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.5rem 0.75rem; font-weight: 600; font-size: 0.875rem;">
                                            {{ $token->vote_attempts }}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem;">
                                        @if($token->status === 'used')
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">Usado</span>
                                        @elseif($token->status === 'pending')
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">Pendiente</span>
                                        @else
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">Expirado</span>
                                        @endif
                                    </td>
                                    <td style="padding: 1rem;">
                                        @if($token->used_at)
                                            <small class="text-muted">{{ $token->used_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td style="padding: 1rem;">
                                        @if($token->last_attempt_at)
                                            <small class="text-muted">{{ $token->last_attempt_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3" style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.05) 0%, rgba(245, 87, 108, 0.05) 100%); border: 1px solid rgba(240, 147, 251, 0.2); border-radius: 12px; padding: 1.25rem;">
                    <i class="bi bi-info-circle" style="color: #f093fb;"></i>
                    <strong style="color: #1e293b;">Interpretación:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Rosa:</strong> Tokens con 3-5 intentos (posible compartición)</li>
                        <li><strong>Rojo:</strong> Tokens con más de 5 intentos (muy sospechoso)</li>
                    </ul>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-shield-check" style="font-size: 2.5rem; color: #11998e;"></i>
                        </div>
                    </div>
                    <p style="color: #11998e; font-weight: 500;">¡Excelente! No hay tokens sospechosos detectados.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="modern-card mb-4">
        <div style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold" style="color: #667eea;">
                <i class="bi bi-clock-history"></i> Actividad Reciente
            </h5>
        </div>
        <div style="padding: 1.5rem;">
            <p class="text-muted">Últimos 50 tokens que han intentado votar</p>

            @if($recentActivity->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th style="width: 250px; color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Token</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Fuente</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Campaign ID</th>
                                <th class="text-center" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Estado</th>
                                <th class="text-center" style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Intentos</th>
                                <th style="color: #475569; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem;">Último Intento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivity as $token)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem;">
                                        <code class="small" style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; color: #667eea;">{{ Str::limit($token->token, 18) }}</code>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 0.375rem 0.75rem; font-weight: 600;">{{ $token->source }}</span>
                                    </td>
                                    <td style="padding: 1rem;">
                                        @if($token->campaign_id)
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(79, 172, 254, 0.15) 0%, rgba(0, 242, 254, 0.15) 100%); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">{{ $token->campaign_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="padding: 1rem;">
                                        @if($token->status === 'used')
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); color: #11998e; border: 1px solid rgba(17, 153, 142, 0.3); padding: 0.375rem 0.5rem; font-weight: 600;">
                                                <i class="bi bi-check-circle"></i>
                                            </span>
                                        @elseif($token->status === 'pending')
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.375rem 0.5rem; font-weight: 600;">
                                                <i class="bi bi-clock"></i>
                                            </span>
                                        @else
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.15) 0%, rgba(255, 106, 0, 0.15) 100%); color: #ee0979; border: 1px solid rgba(238, 9, 121, 0.3); padding: 0.375rem 0.5rem; font-weight: 600;">
                                                <i class="bi bi-x-circle"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="padding: 1rem;">
                                        @if($token->vote_attempts > 1)
                                            <span class="badge" style="background: linear-gradient(135deg, rgba(240, 147, 251, 0.15) 0%, rgba(245, 87, 108, 0.15) 100%); color: #f093fb; border: 1px solid rgba(240, 147, 251, 0.3); padding: 0.375rem 0.75rem; font-weight: 600;">{{ $token->vote_attempts }}</span>
                                        @else
                                            <span class="text-muted">{{ $token->vote_attempts }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 1rem;">
                                        <small class="text-muted">
                                            {{ $token->last_attempt_at->diffForHumans() }}
                                        </small>
                                        <br>
                                        <small class="text-muted">{{ $token->last_attempt_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-activity" style="font-size: 2.5rem; color: #667eea;"></i>
                        </div>
                    </div>
                    <p class="text-muted">No hay actividad reciente.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Datos de los gráficos
const votesData = @json($votesByOption);

// Generar un gráfico circular para cada pregunta
votesData.forEach((question, index) => {
    const totalVotes = question.options.reduce((sum, opt) => sum + opt.votes, 0);

    // Solo crear gráfico si hay votos
    if (totalVotes > 0) {
        const ctx = document.getElementById('pieChart' + index);

        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: question.options.map(opt => opt.option_text),
                    datasets: [{
                        data: question.options.map(opt => opt.votes),
                        backgroundColor: question.options.map(opt => opt.color),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                },
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map((label, i) => {
                                            const value = data.datasets[0].data[i];
                                            const percentage = ((value / totalVotes) * 100).toFixed(1);
                                            return {
                                                text: `${label}: ${value} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                hidden: false,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const percentage = ((value / totalVotes) * 100).toFixed(1);
                                    return `${label}: ${value} votos (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});
</script>
@endsection
