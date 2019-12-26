<div style="border: solid 1px #ccc; width: 90%; padding: 0 5%;">
<h2>
  【予定通知】{{ $options["summary"] }}
</h2>
<p>
  <span style="color: #aaa;">時間：</span>{{ $options["start_date"]->format('Y/m/d H:i') }} ～ {{ $options["end_date"]->format('Y/m/d H:i') }}
</p>
<p>
  <span style="color: #aaa;">場所：</span>{{ $options["location"] }}
</p>
<hr>
<p>
  {!! nl2br(e($options["description"])) !!}
</p>
</div>