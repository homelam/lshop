<div class="form-group {!! !$errors->has($label) ?: 'has-error' !!}">

    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <script id="{{$id}}" name="{{$id}}" type="text/plain" style="width:100%; height:300px">{!! old($column, $value) !!}</script>

    </div>
</div>