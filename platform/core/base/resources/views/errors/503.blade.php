@extends('core/base::errors.master')

@section('title', __('Maintenance mode'))

@section('message')
    <h1>{{ __('Maintenance mode') }}</h1>
    <p>{{ __('Sorry, we are doing some maintenance. Please check back soon.') }}</p>
    <small>{!! BaseHelper::clean(__("If you are the administrator and you can't access your site after enabling maintenance mode, just need to delete file <strong>storage/framework/down</strong> to turn-off maintenance mode.")) !!}</small>
@stop
