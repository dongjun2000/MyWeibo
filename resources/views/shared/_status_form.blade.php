<form action="{{ route('statuses.store') }}" method="post">
  @include('shared._errors')
  @csrf

  <textarea name="content" id="content" rows="3" placeholder="聊聊新鲜事儿..." class="form-control">{{ old('content') }}</textarea>
  <div class="text-end">
    <button type="submit" class="btn btn-primary mt-3">发布</button>
  </div>
</form>
