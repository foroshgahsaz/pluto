@extends('public.layout')

@section('title', $form->title.' — '.$branding['title'])

@push('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        .form-panel {
            max-width: 720px;
            background: rgba(255,255,255,.72);
            border: 1px solid var(--line);
            border-radius: .75rem;
            padding: clamp(1.2rem, 3vw, 2rem);
            box-shadow: 0 18px 50px rgba(7,23,20,.08);
        }
        .form-panel .field { margin-bottom: 1.05rem; }
        .form-panel label { display: block; font-weight: 700; margin-bottom: .35rem; }
        .form-panel .help { color: var(--muted); font-size: .85rem; margin-top: .25rem; }
        .form-panel input[type="text"],
        .form-panel input[type="number"],
        .form-panel input[type="date"],
        .form-panel input[type="file"],
        .form-panel select,
        .form-panel textarea {
            width: 100%;
            border: 1px solid var(--line);
            background: #fff;
            border-radius: .4rem;
            padding: .7rem .8rem;
            font: inherit;
        }
        .form-panel textarea { min-height: 7rem; resize: vertical; }
        .form-panel .error { color: #b91c1c; font-size: .85rem; margin-top: .3rem; }
        .form-actions { display: flex; flex-wrap: wrap; gap: .7rem; margin-top: 1.4rem; }
        .form-actions button, .form-actions .ghost {
            border: 0; border-radius: .35rem; padding: .75rem 1.1rem; font: inherit; cursor: pointer;
        }
        .form-actions button.primary { background: var(--accent); color: #1a1203; font-weight: 700; }
        .form-actions .ghost { background: transparent; border: 1px solid var(--line); }
        .success-box {
            border: 1px solid color-mix(in srgb, var(--primary) 35%, var(--line));
            background: color-mix(in srgb, var(--primary) 10%, #fff);
            border-radius: .6rem; padding: 1rem 1.1rem; margin-bottom: 1rem;
        }
        .step-dots { display: flex; gap: .4rem; margin-bottom: 1rem; }
        .step-dots span {
            width: .7rem; height: .7rem; border-radius: 999px;
            background: color-mix(in srgb, var(--primary) 25%, #fff);
        }
        .step-dots span.active { background: var(--accent); }
        .check-row { display: flex; flex-wrap: wrap; gap: .6rem 1rem; }
        .check-row label { font-weight: 500; display: inline-flex; gap: .35rem; align-items: center; }
    </style>
@endpush

@section('content')
    <h1 class="page-title">{{ $form->title }}</h1>
    @if ($form->description)
        <p class="meta" style="margin-bottom:1.2rem;">{{ $form->description }}</p>
    @endif

    @if ($submitted)
        <div class="form-panel">
            <div class="success-box">
                {{ $form->success_message ?: 'فرم با موفقیت ثبت شد.' }}
            </div>
            <a class="cta" href="{{ route('public.home', $site) }}">بازگشت به صفحه اصلی</a>
        </div>
    @else
        @php
            $fields = $form->fields;
            $steps = $fields->pluck('step')->unique()->sort()->values();
            $maxStep = max(1, (int) ($steps->max() ?: 1));
            $visibilityMap = $fields->mapWithKeys(fn ($field) => [
                $field->name => $field->visibility_rules_json ?: null,
            ]);
            $initial = [];
            foreach ($fields as $field) {
                $initial[$field->name] = old($field->name, $field->type->value === 'multiselect' ? [] : '');
            }
        @endphp

        <div
            class="form-panel"
            x-data="plutoForm({
                multi: {{ $form->is_multi_step ? 'true' : 'false' }},
                maxStep: {{ $maxStep }},
                values: {{ Js::from($initial) }},
                visibility: {{ Js::from($visibilityMap) }},
                fieldSteps: {{ Js::from($fields->mapWithKeys(fn ($f) => [$f->name => (int) $f->step])) }}
            })"
        >
            @if ($errors->any())
                <div class="error" style="margin-bottom:1rem;">لطفاً خطاهای فرم را بررسی کنید.</div>
            @endif

            @if ($form->is_multi_step)
                <div class="step-dots" aria-hidden="true">
                    @for ($i = 1; $i <= $maxStep; $i++)
                        <span :class="step === {{ $i }} && 'active'"></span>
                    @endfor
                </div>
            @endif

            <form method="post" action="{{ route('public.form.submit', [$site, $form->slug]) }}" enctype="multipart/form-data">
                @csrf

                @foreach ($fields as $field)
                    <div
                        class="field"
                        x-show="isVisible(@js($field->name)) && (!multi || step === {{ (int) $field->step }})"
                        x-cloak
                    >
                        <label for="field-{{ $field->name }}">
                            {{ $field->label }}
                            @if ($field->is_required)<span style="color:#b91c1c">*</span>@endif
                        </label>

                        @switch ($field->type->value)
                            @case('textarea')
                                <textarea id="field-{{ $field->name }}" name="{{ $field->name }}" placeholder="{{ $field->placeholder }}" x-model="values['{{ $field->name }}']">{{ old($field->name) }}</textarea>
                                @break
                            @case('number')
                                <input id="field-{{ $field->name }}" type="number" name="{{ $field->name }}" value="{{ old($field->name) }}" placeholder="{{ $field->placeholder }}" x-model="values['{{ $field->name }}']">
                                @break
                            @case('date')
                                <input id="field-{{ $field->name }}" type="date" name="{{ $field->name }}" value="{{ old($field->name) }}" x-model="values['{{ $field->name }}']">
                                @break
                            @case('select')
                                <select id="field-{{ $field->name }}" name="{{ $field->name }}" x-model="values['{{ $field->name }}']">
                                    <option value="">انتخاب کنید</option>
                                    @foreach (($field->options_json ?? []) as $option)
                                        <option value="{{ $option['value'] ?? '' }}" @selected(old($field->name) == ($option['value'] ?? ''))>
                                            {{ $option['label'] ?? ($option['value'] ?? '') }}
                                        </option>
                                    @endforeach
                                </select>
                                @break
                            @case('multiselect')
                                <div class="check-row">
                                    @foreach (($field->options_json ?? []) as $option)
                                        @php $val = $option['value'] ?? ''; @endphp
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="{{ $field->name }}[]"
                                                value="{{ $val }}"
                                                @checked(in_array($val, old($field->name, []), true))
                                                x-model="values['{{ $field->name }}']"
                                            >
                                            {{ $option['label'] ?? $val }}
                                        </label>
                                    @endforeach
                                </div>
                                @break
                            @case('file')
                            @case('image')
                                <input id="field-{{ $field->name }}" type="file" name="{{ $field->name }}" @if($field->type->value === 'image') accept="image/*" @endif>
                                @break
                            @default
                                <input id="field-{{ $field->name }}" type="text" name="{{ $field->name }}" value="{{ old($field->name) }}" placeholder="{{ $field->placeholder }}" x-model="values['{{ $field->name }}']">
                        @endswitch

                        @if ($field->help_text)
                            <div class="help">{{ $field->help_text }}</div>
                        @endif
                        @error($field->name)
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                @error('form')
                    <div class="error">{{ $message }}</div>
                @enderror

                <div class="form-actions">
                    <template x-if="multi && step > 1">
                        <button type="button" class="ghost" @click="step--">قبلی</button>
                    </template>
                    <template x-if="multi && step < maxStep">
                        <button type="button" class="primary" @click="step++">بعدی</button>
                    </template>
                    <template x-if="!multi || step === maxStep">
                        <button type="submit" class="primary">ارسال فرم</button>
                    </template>
                </div>
            </form>
        </div>

        <script>
            function plutoForm(config) {
                return {
                    multi: config.multi,
                    maxStep: config.maxStep,
                    step: 1,
                    values: config.values,
                    visibility: config.visibility,
                    fieldSteps: config.fieldSteps,
                    isVisible(name) {
                        const rules = this.visibility[name];
                        if (!rules) return true;
                        const normalized = Array.isArray(rules)
                            ? (rules.length && rules[0] && typeof rules[0] === 'object' && 'field' in rules[0] ? rules : [rules])
                            : [rules];

                        for (const rule of normalized) {
                            if (!rule || typeof rule !== 'object' || !('field' in rule)) continue;
                            const actual = this.values[rule.field];
                            const expected = rule.value;
                            const op = rule.operator || 'equals';
                            let ok = true;
                            if (op === 'equals') ok = String(actual ?? '') === String(expected ?? '');
                            else if (op === 'not_equals') ok = String(actual ?? '') !== String(expected ?? '');
                            else if (op === 'filled') ok = !(actual === null || actual === undefined || actual === '' || (Array.isArray(actual) && !actual.length));
                            else if (op === 'empty') ok = (actual === null || actual === undefined || actual === '' || (Array.isArray(actual) && !actual.length));
                            else if (op === 'in') ok = (Array.isArray(expected) ? expected : [expected]).map(String).includes(String(actual ?? ''));
                            if (!ok) return false;
                        }
                        return true;
                    }
                }
            }
        </script>
        <style>[x-cloak]{display:none!important}</style>
    @endif
@endsection
