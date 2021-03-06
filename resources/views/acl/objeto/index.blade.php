@extends('layouts.app')
@section('header')
@include('templates.nav')
@endsection
@section('content')
@include('templates.menu')

@if($url == 'lista')
	@include('acl.objeto.lista')
@elseif($url == 'view')
    @include('acl.objeto.view')
@elseif($url == 'edit')
	@include('acl.objeto.edit')
@elseif($url == 'create')
	@include('acl.objeto.inserir')
@elseif($url == 'inserirobj')
	@include('acl.objeto.objeto')
@elseif($url == 'ficha')
	@include('acl.objeto.ficha')
@endif
@endsection

@section('scripts')
@parent
@if (notify()->ready())
<script type="text/javascript">
  swal({
    title:"{!! notify()->message() !!}",
    text: "{!! notify()->option('text') !!}",
    type: "{!! notify()->type() !!}",
    @if(notify()->option('timer'))
    timer: "{!! notify()->option('timer') !!}",
    showConfirmButton: false
    @endif
});
</script>
@endif
@stop