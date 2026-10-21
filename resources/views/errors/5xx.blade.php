@extends('errors.layout')

@section('title', 'Service error | ' . config('app.name', 'Billora'))
@section('code', $exception->getStatusCode())
@section('heading', 'Something went wrong')
@section('message', 'The service ran into an unexpected problem. Please try again in a little while.')
