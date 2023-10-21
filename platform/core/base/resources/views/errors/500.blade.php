@extends('core/base::errors.master')

@section('title', __('An Error Occurred: Internal Server Error'))

@section('message')
    <h1>{{ __('Oops! An Error Occurred') }}</h1>
    <h2>{{ __('The server returned a "500 Internal Server Error".') }}</h2>

    <p>
        {{ __('Something is broken. Please let us know what you were doing when this error occurred. We will fix it as soon as possible. Sorry for any inconvenience caused.') }}
    </p>
@stop
