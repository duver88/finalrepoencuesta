@extends('layouts.app')

@section('title', $survey->title)

@section('meta_description', $survey->description ?? 'Participa en esta encuesta y comparte tu opinión')

@section('og_image_full', $survey->og_image ? asset('storage/' . $survey->og_image) : ($survey->banner ? asset('storage/' . $survey->banner) : url('images/default-survey-preview.jpg')))

@section('og_title', $survey->title)
@section('og_description', $survey->description ?? 'Participa en esta encuesta y comparte tu opinión')

@section('content')
<div class="min-vh-100 survey-main-container" style="background: #1a1a1a; padding: 3rem 0;">
    <div class="container" style="max-width: 740px;">

        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 12px; border-radius: 8px;">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Header Card -->
        <div class="form-card" style="border-top: 10px solid #fdd71a; margin-bottom: 12px;">
            @if($survey->banner)
                <div style="width: 100%; height: auto; overflow: hidden; border-radius: 8px 8px 0 0; aspect-ratio: 856/200;">
                    <img src="{{ asset('storage/' . $survey->banner) }}" alt="{{ $survey->title }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>
            @endif
            <div style="padding: 32px 24px 24px 24px;">
                <h1 style="font-size: 32px; font-weight: 400; color: #fdd71a; margin: 0 0 8px 0; line-height: 1.2;">{{ $survey->title }}</h1>
                @if($survey->description)
                    <p style="font-size: 14px; color: #e8eaed; margin: 0; line-height: 1.6;">{{ $survey->description }}</p>
                @endif
            </div>
        </div>

        @if(session('no_tokens'))
            <div class="form-card text-center" style="padding: 48px 24px;">
                <i class="bi bi-exclamation-circle" style="font-size: 4rem; color: #fdd71a; margin-bottom: 24px; display: block;"></i>
                <h2 style="font-size: 24px; font-weight: 400; color: #ffffff; margin-bottom: 12px;">Encuesta No Disponible</h2>
                <p style="color: #9aa0a6;">Esta encuesta ha alcanzado su límite de participantes.</p>
            </div>
        @elseif($hasVoted)
            <div class="form-card text-center" style="padding: 48px 24px;">
                <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: #34a853; margin-bottom: 24px; display: block;"></i>
                <h2 style="font-size: 24px; font-weight: 400; color: #ffffff; margin-bottom: 12px;">¡Gracias por participar!</h2>
                <p style="color: #9aa0a6;">Ya has votado en esta encuesta anteriormente.</p>
            </div>
        @else
            <form method="POST" action="{{ route('surveys.vote', $survey->public_slug) }}" id="voteForm">
                @csrf
                <input type="hidden" name="fingerprint" id="fingerprint">

                @if(isset($token) && $token)
                    <input type="hidden" name="token" value="{{ $token }}">
                @endif

                <input type="hidden" name="device_data[user_agent]" id="device_user_agent">
                <input type="hidden" name="device_data[platform]" id="device_platform">
                <input type="hidden" name="device_data[screen_resolution]" id="device_resolution">
                <input type="hidden" name="device_data[hardware_concurrency]" id="device_cpu">

                <input type="text" name="website" id="website" style="position:absolute;left:-9999px;width:1px;height:1px;" tabindex="-1" autocomplete="off">
                <input type="text" name="url_field" id="url_field" style="position:absolute;left:-9999px;width:1px;height:1px;" tabindex="-1" autocomplete="off">

                @foreach($survey->questions as $question)
                    <div class="form-card" style="margin-bottom: 12px;">
                        <div style="padding: 24px;">
                            <h3 style="font-size: 16px; font-weight: 400; color: #ffffff; margin: 0 0 20px 0; line-height: 1.5;">
                                {{ $question->question_text }}
                                @if($question->question_type === 'single_choice')
                                    <span style="color: #d93025; margin-left: 4px;">*</span>
                                @endif
                            </h3>

                            @if($question->question_type === 'single_choice')
                                @php $hasImages = $question->options->contains(fn($opt) => !empty($opt->image)); @endphp

                                @if($hasImages)
                                    <div class="options-grid">
                                        @foreach($question->options->shuffle() as $option)
                                            <label class="image-option" for="option{{ $option->id }}">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" id="option{{ $option->id }}" required>
                                                @if($option->image)
                                                    <div class="option-image-wrapper">
                                                        <img src="{{ asset('storage/' . $option->image) }}" alt="{{ $option->option_text }}">
                                                    </div>
                                                @endif
                                                <div class="option-label">{{ $option->option_text }}</div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    @foreach($question->options->shuffle() as $option)
                                        <label class="radio-option">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" id="option{{ $option->id }}" required>
                                            <span class="radio-circle"></span>
                                            <span class="radio-label">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                @endif
                            @else
                                @php $hasImages = $question->options->contains(fn($opt) => !empty($opt->image)); @endphp

                                @if($hasImages)
                                    <div class="options-grid">
                                        @foreach($question->options as $option)
                                            <label class="image-option" for="option{{ $option->id }}">
                                                <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" id="option{{ $option->id }}">
                                                @if($option->image)
                                                    <div class="option-image-wrapper">
                                                        <img src="{{ asset('storage/' . $option->image) }}" alt="{{ $option->option_text }}">
                                                    </div>
                                                @endif
                                                <div class="option-label">{{ $option->option_text }}</div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    @foreach($question->options as $option)
                                        <label class="checkbox-option">
                                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" id="option{{ $option->id }}">
                                            <span class="checkbox-box"></span>
                                            <span class="checkbox-label">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="form-card submit-card" style="padding: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <button type="submit" class="submit-btn">Enviar</button>
                    <div class="protected-text" style="font-size: 12px; color: #9aa0a6;">
                        <i class="bi bi-shield-lock-fill"></i> Respuestas protegidas
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
// Canvas Fingerprinting
function getCanvasFingerprint() {
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 200;
        canvas.height = 50;
        ctx.textBaseline = 'top';
        ctx.font = '14px "Arial"';
        ctx.textBaseline = 'alphabetic';
        ctx.fillStyle = '#f60';
        ctx.fillRect(125, 1, 62, 20);
        ctx.fillStyle = '#069';
        ctx.fillText('Survey Fingerprint 🎨', 2, 15);
        ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
        ctx.fillText('Canvas FP', 4, 17);
        return canvas.toDataURL();
    } catch (e) {
        return 'canvas-not-supported';
    }
}

// WebGL Fingerprinting
function getWebGLFingerprint() {
    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (!gl) return 'webgl-not-supported';
        const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
        return JSON.stringify({
            vendor: gl.getParameter(gl.VENDOR),
            renderer: gl.getParameter(gl.RENDERER),
            version: gl.getParameter(gl.VERSION),
            shadingLanguageVersion: gl.getParameter(gl.SHADING_LANGUAGE_VERSION),
            unmaskedVendor: debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'unknown',
            unmaskedRenderer: debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'unknown'
        });
    } catch (e) {
        return 'webgl-error';
    }
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Strict";
}

function hashString(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return Math.abs(hash).toString(36);
}

function generateFingerprint() {
    const nav = window.navigator;
    const screen = window.screen;

    const hardwareData = [
        screen.width + 'x' + screen.height,
        screen.availWidth + 'x' + screen.availHeight,
        screen.colorDepth,
        screen.pixelDepth,
        window.devicePixelRatio || 1,
        nav.hardwareConcurrency || 'unknown',
        nav.deviceMemory || 'unknown',
        nav.maxTouchPoints || 0,
        nav.platform,
        new Date().getTimezoneOffset(),
        nav.language,
        nav.languages ? nav.languages[0] : ''
    ].join('|');

    const canvasHash = hashString(getCanvasFingerprint());
    const webglHash = hashString(getWebGLFingerprint());
    const combinedData = hardwareData + '|' + canvasHash + '|' + webglHash;
    const hardwareFingerprint = 'hw_' + hashString(combinedData);

    let persistentId = getCookie('device_fingerprint') || localStorage.getItem('survey_fingerprint');

    if (persistentId && persistentId.includes(hardwareFingerprint)) {
        return persistentId;
    }

    const uniqueId = hardwareFingerprint + '_' + Date.now().toString(36);

    try {
        localStorage.setItem('survey_fingerprint', uniqueId);
    } catch (e) {
        console.log('LocalStorage no disponible');
    }

    setCookie('device_fingerprint', uniqueId, 365);
    setCookie('survey_{{ $survey->id }}_fp', uniqueId, 365);

    return uniqueId;
}

async function checkIfAlreadyVoted(fingerprint) {
    try {
        const response = await fetch('{{ route('surveys.check-vote', $survey->public_slug) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ fingerprint: fingerprint })
        });

        const data = await response.json();

        if (data.has_voted) {
            window.location.href = '{{ route('surveys.thanks', $survey->public_slug) }}';
        }
    } catch (error) {
        console.error('Error verificando voto:', error);
    }
}

function validateBeforeSubmit(event) {
    const surveyVoteCookie = getCookie('survey_{{ $survey->id }}_voted');

    if (surveyVoteCookie) {
        event.preventDefault();
        alert('Ya has votado en esta encuesta anteriormente. Solo se permite un voto por dispositivo.');
        window.location.href = '{{ route('surveys.thanks', $survey->public_slug) }}';
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const fingerprint = generateFingerprint();
    const fingerprintInput = document.getElementById('fingerprint');

    if (fingerprintInput) {
        fingerprintInput.value = fingerprint;

        const nav = window.navigator;
        const screen = window.screen;

        document.getElementById('device_user_agent').value = nav.userAgent || '';
        document.getElementById('device_platform').value = nav.platform || '';
        document.getElementById('device_resolution').value = screen.width + 'x' + screen.height;
        document.getElementById('device_cpu').value = nav.hardwareConcurrency || 0;

        checkIfAlreadyVoted(fingerprint);

        const voteForm = document.getElementById('voteForm');
        if (voteForm) {
            voteForm.addEventListener('submit', validateBeforeSubmit);
        }
    }
});
</script>

<style>
/* Form Card - Estilo Google Forms */
.form-card {
    background: #2d2d2d;
    border: 1px solid #3c4043;
    border-radius: 8px;
    box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.3), 0 1px 3px 1px rgba(60, 64, 67, 0.15);
}

/* Radio Options */
.radio-option {
    display: flex;
    align-items: center;
    padding: 12px 0;
    cursor: pointer;
    transition: background-color 0.2s;
    border-radius: 4px;
    margin-bottom: 4px;
}

.radio-option:hover {
    background-color: rgba(253, 215, 26, 0.08);
}

.radio-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.radio-circle {
    width: 20px;
    height: 20px;
    border: 2px solid #5f6368;
    border-radius: 50%;
    margin-right: 12px;
    flex-shrink: 0;
    position: relative;
    transition: border-color 0.2s;
}

.radio-option:hover .radio-circle {
    border-color: #fdd71a;
}

.radio-option input[type="radio"]:checked ~ .radio-circle {
    border-color: #fdd71a;
    background-color: #fdd71a;
}

.radio-option input[type="radio"]:checked ~ .radio-circle::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #000000;
}

.radio-label {
    font-size: 14px;
    color: #e8eaed;
    line-height: 1.5;
}

/* Checkbox Options */
.checkbox-option {
    display: flex;
    align-items: center;
    padding: 12px 0;
    cursor: pointer;
    transition: background-color 0.2s;
    border-radius: 4px;
    margin-bottom: 4px;
}

.checkbox-option:hover {
    background-color: rgba(253, 215, 26, 0.08);
}

.checkbox-option input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.checkbox-box {
    width: 20px;
    height: 20px;
    border: 2px solid #5f6368;
    border-radius: 2px;
    margin-right: 12px;
    flex-shrink: 0;
    position: relative;
    transition: all 0.2s;
}

.checkbox-option:hover .checkbox-box {
    border-color: #fdd71a;
}

.checkbox-option input[type="checkbox"]:checked ~ .checkbox-box {
    border-color: #fdd71a;
    background-color: #fdd71a;
}

.checkbox-option input[type="checkbox"]:checked ~ .checkbox-box::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #000000;
    font-size: 14px;
    font-weight: bold;
}

.checkbox-label {
    font-size: 14px;
    color: #e8eaed;
    line-height: 1.5;
}

/* Image Options Grid */
.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
}

.image-option {
    border: 2px solid #5f6368;
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.image-option:hover {
    border-color: #fdd71a;
    background-color: rgba(253, 215, 26, 0.05);
}

.image-option input {
    display: none;
}

.image-option input:checked ~ .option-image-wrapper,
.image-option input:checked ~ .option-label {
    border-color: #fdd71a;
}

.image-option input:checked {
    & ~ * {
        color: #fdd71a;
    }
}

.image-option:has(input:checked) {
    border-color: #fdd71a;
    background-color: rgba(253, 215, 26, 0.1);
}

.option-image-wrapper {
    width: 100%;
    height: 100px;
    overflow: hidden;
    border-radius: 4px;
    margin-bottom: 12px;
}

.option-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #1a1a1a;
}

.option-label {
    font-size: 14px;
    color: #e8eaed;
    line-height: 1.5;
}

/* Submit Button */
.submit-btn {
    background-color: #fdd71a;
    color: #000000;
    border: none;
    border-radius: 4px;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s, box-shadow 0.2s;
    text-transform: none;
}

.submit-btn:hover {
    background-color: #e5c318;
    box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.3), 0 1px 3px 1px rgba(60, 64, 67, 0.15);
}

.submit-btn:active {
    background-color: #ccad15;
}

/* Responsive */
@media (max-width: 768px) {
    .survey-main-container {
        padding: 2rem 0 !important;
    }

    .container {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    .form-card {
        margin-left: 0;
        margin-right: 0;
    }

    .form-card > div[style*="padding: 32px"] {
        padding: 24px 20px 20px 20px !important;
    }

    .form-card > div[style*="padding: 24px"] {
        padding: 20px !important;
    }

    .form-card h1 {
        font-size: 24px !important;
    }

    .form-card h3 {
        font-size: 15px !important;
    }

    .form-card p {
        font-size: 13px !important;
    }

    .options-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .radio-option,
    .checkbox-option {
        padding: 10px 0;
    }

    .radio-circle,
    .checkbox-box {
        width: 18px;
        height: 18px;
    }

    .radio-label,
    .checkbox-label {
        font-size: 13px;
    }

    .image-option {
        padding: 12px;
    }

    .option-image-wrapper {
        height: 160px;
        margin-bottom: 10px;
    }

    .option-label {
        font-size: 13px;
    }

    .submit-btn {
        width: 100%;
        padding: 12px 24px;
        font-size: 15px;
    }

    .submit-card {
        flex-direction: column;
        align-items: stretch !important;
    }

    .protected-text {
        text-align: center;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .survey-main-container {
        padding: 1.5rem 0 !important;
    }

    .container {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .form-card > div[style*="padding: 32px"] {
        padding: 20px 16px 16px 16px !important;
    }

    .form-card > div[style*="padding: 24px"] {
        padding: 16px !important;
    }

    .form-card h1 {
        font-size: 22px !important;
        line-height: 1.3 !important;
    }

    .form-card h3 {
        font-size: 14px !important;
        margin-bottom: 16px !important;
    }

    .options-grid {
        gap: 10px;
    }

    .image-option {
        padding: 10px;
    }

    .option-image-wrapper {
        height: 140px;
    }
}
</style>
@endsection
