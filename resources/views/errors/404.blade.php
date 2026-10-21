@extends('errors.layout')

@section('title', 'Page not found | ' . config('app.name', 'Billora'))
@section('code', '404')
@section('heading', 'We couldn’t find that page')
@section('message', 'The page may have moved, or the address may be incorrect. Check the link and try again.')
