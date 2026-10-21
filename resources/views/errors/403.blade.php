@extends('errors.layout')

@section('title', 'Access denied | ' . config('app.name', 'Billora'))
@section('code', '403')
@section('heading', 'You can’t access this page')
@section('message', 'You do not have permission to view this page. If you think this is a mistake, contact your administrator.')
