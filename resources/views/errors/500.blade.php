@extends('errors.layout')

@section('title', 'Something went wrong | ' . config('app.name', 'Billora'))
@section('code', '500')
@section('heading', 'Something went wrong')
@section('message', 'An unexpected error occurred on our side. Please try again in a little while.')
