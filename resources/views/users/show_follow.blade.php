@extends('layouts.default')

@section("title", $title)

@section('content')
  <div class="offset-md-2 col-md-8">
    <h2 class="mb-4 text-center">{{ $title }}</h2>
  </div>

  <div class="list-group list-group-flush">
    @foreach ($users as $user)
      <div class="list-group-item">
        <img width="32" src="{{ $user->gravatar() }}" alt="{{ $user->name }}" class="me-3">
        <a href="{{ route('users.show', $user) }}" class="text-decoration-none">
          {{ $user->name }}
        </a>
      </div>
    @endforeach
  </div>

  <div class="mt-3">
    {!! $users->render() !!}
  </div>
@endsection
