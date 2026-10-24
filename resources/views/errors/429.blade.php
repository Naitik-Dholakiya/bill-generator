@extends('errors.layout')

@section('title', 'Too many requests | ' . config('app.name', 'Vyapar'))
@section('code', '429')
@section('heading', 'Please slow down')
@section('message', 'There have been too many requests in a short time. Wait a moment, then try again.')
