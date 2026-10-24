@extends('errors.layout')

@section('title', 'Sign in required | ' . config('app.name', 'Vyapar'))
@section('code', '401')
@section('heading', 'Sign in required')
@section('message', 'Please sign in to your account before accessing this page.')
