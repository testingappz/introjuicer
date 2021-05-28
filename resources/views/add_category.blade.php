@extends('layouts.app')

@section('content')
 <!-- Begin Page Content -->
 <div class="container-fluid">

<!-- DataTales Example -->
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary float-left">Add Category</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
        <tbody>
		<form action="{{ url('/adding_qs')}}" method="POST" >
		@csrf
			<div class="form-group">
				<input type="text" class="form-control" aria-describedby="emailHelp" placeholder="Add category" name="qs">
				<input type="hidden" name="add_category">
			</div>
			<button type="submit" class="btn btn-primary" name="btnsubmit">Submit</button>
		</form>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
@endsection