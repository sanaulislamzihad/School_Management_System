@extends('layouts.dashboard')
@section('title', 'My timetable')
@section('content')
<x-page-head title="My timetable" subtitle="Your classes for the week." />
<x-week-grid :periods="$periods" :show-teacher="false" :show-class="true" />
@endsection
