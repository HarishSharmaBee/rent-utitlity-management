@extends('layouts/contentNavbarLayout')

@section('content')
<!-- Users -->
<div class="card">   
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-header">Buildings</h5>
  </div>
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">
      {{ session('success') }}
      </div>
    @endif
    <div class="table-responsive text-nowrap">
      <table class="table table-bordered" id="buildings-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Address</th>
                <th>No. of Flats</th>
                <th>Owner</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!--/ Bordered Table -->
@endsection
@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function() {
      $('#buildings-table').DataTable({
          processing: true,
          serverSide: true,
          ajax: '{{ route("buildings.index") }}',
          columns: [
            {
              data: 'id',
              name: 'id',
              orderable: false,
              searchable: false,
              width: "5%"
            },
            { data: 'name' },
            { data: 'address' },
            {data:'no_of_flats'},
            {data:'user_name'},
            { data: 'action', 
              orderable: false,
              searchable: false
            }
          ]
      });
  });
</script>
@endsection