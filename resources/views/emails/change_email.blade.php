<h3>
  <a href="{{ config('app.client_url') }}">{{ config('app.name') }}</a>
</h3>
<h2>
  {{ __('最後のステップです') }}
</h2>
<p>
  {{ __('メールアドレスを変更するには以下のボタンをクリックして、メールアドレスの確認を行ってください。') }}<br>
</p>
<p>
  {{ $actionText }}: <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
</p>
