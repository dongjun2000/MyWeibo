@foreach (['danger', 'warning', 'success', 'info'] as $msg)
  @if (session($msg))
    <div class="flash-message">
      <p class="alert alert-{{ $msg }}">
        {{ session($msg) }}
      </p>
    </div>
  @endif
@endforeach
