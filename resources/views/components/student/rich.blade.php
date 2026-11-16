@props(['html'])

{{-- Question HTML through the allow-list (Sprint 3.2 plan § 2, rule 11). --}}
<div {{ $attributes->class('question-html') }}>{!! \App\Support\QuestionHtml::clean($html) !!}</div>
