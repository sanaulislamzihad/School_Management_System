@extends('layouts.dashboard')
@section('title', 'Class routine')
@section('content')
<x-page-head title="Class routine" :subtitle="$me->schoolClass->name.' '.($me->section->name ?? '')" />
<x-week-grid :periods="$periods" />
@endsection
