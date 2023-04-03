@if ($feed_items->count() > 0)
  <ul class="list-unstyled">
    @foreach ($feed_items as $status)
      @include('statuses._status', ['user' => $status->user])
    @endforeach
  </ul>
@else
  <p>没有数据！</p>
@endif
