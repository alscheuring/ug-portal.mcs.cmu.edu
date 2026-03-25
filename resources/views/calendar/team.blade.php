@extends('layouts.app')

@section('title', $team->name . ' Events')

@section('content')
    <livewire:team-calendar :team="$team" />
@endsection