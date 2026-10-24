@extends('errors.layout')

@section('title', 'Page expired | ' . config('app.name', 'Vyapar'))
@section('code', '419')
@section('heading', 'Your session has expired')
@section('message', 'For your security, this page expired after a period of inactivity. Return home and try again.')
