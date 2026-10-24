@extends('errors.layout')

@section('title', 'Request error | ' . config('app.name', 'Vyapar'))
@section('code', $exception->getStatusCode())
@section('heading', 'We couldn’t complete that request')
@section('message', 'The request could not be processed. Check the address or try again from the home page.')
