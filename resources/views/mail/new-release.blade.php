@component('mail::message')
# {{ $release->repository->full_name }}:{{ $release->tag_name }}

{{ $release->body }}

@component('mail::button', ['url' => $release->url])
View release
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
