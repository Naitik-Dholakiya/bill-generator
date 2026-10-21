@extends('errors.layout')

@section('title', 'Temporarily unavailable | ' . config('app.name', 'Billora'))
@section('code', '503')
@section('heading', 'We’ll be back shortly')
@section('message', 'The service is temporarily unavailable, possibly while maintenance is underway. Please try again soon.')
