@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid">
    <div class="row">
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="heading1 margin_0 d-flex">
             <h2>Deposit List</h2>
             {{-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter" style="margin-left:620px;">Add Work Name</button> --}}
          </div>
       </div>
       <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
             <table id="example" class="table table-striped" style="width:100%">
                <thead class="thead-dark">
                   <tr>
                      <th>Id</th>
					   <th>User Id</th>
                      <th>User Name</th>
					  <th>Mobile</th>
                      <th>Order Id</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Date</th>
                   </tr>
                </thead>
                <tbody>
                  @foreach($deposits as $item)
                   <tr>
                      <td>{{$item->id}}</td>
					  <td>{{$item->userid}}</td>
                      <td>{{$item->uname}}</td>
					  <td>{{$item->mobile}}</td>
                      <td>{{$item->order_id}}</td>
                      <td>{{$item->cash}}</td>
					   @if($item->status=='2')
                      <td><div class="btn btn-success btn-sm">Success<div></td>
					   @elseif($item->status=='3')
					   <td><div class="btn btn-danger btn-sm">Reject<div></td>
					   @elseif($item->status=='1')
					   <td><div class="btn btn-warning btn-sm">Pending<div></td>
					   @else
						  <td></td>
					   @endif
                      <td>{{$item->created_at}}</td>
                     
                      {{-- <td>
                        <i class="fa fa-edit mt-1" data-toggle="modal" data-target="#exampleModalCenterupdate{{$item->id}}" style="font-size:30px"></i>
                        <a href="{{route('work_name.delete',$item->id)}}"><i class="fa fa-trash mt-1 ml-1" style="font-size:30px;color:red;" ></i></a>
            
                      </td> --}}
                      {{-- <div class="modal fade" id="exampleModalCenterupdate{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLongTitle">Edit Work Name</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <form action="{{route('work_name.update',$item->id)}}" method="POST" enctype="multipart/form-data">
                              @csrf
                            <div class="modal-body">
                              <div class="container-fluid">
                                <div class="row">
                                  <div class="form-group col-md-12">
                                    <label for="fieldname">Field Name</label>
                                    <input type="text" class="form-control" id="fieldname" value="{{$item->fieldname}}" name="fieldname" placeholder="Enter fieldname">
                                  </div>
                                  
                                </div>
                               
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                            </form>
                            
                          </div>
                        </div>
                      </div> --}}
                    
                   </tr>
                  @endforeach
                </tbody>
             </table>
          </div>
       </div>
    </div>
 </div>
</div>
</div> 
{{-- popup modal form --}}
<!-- Modal -->
{{-- <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Add Work Name</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{route('work_name.store')}}" method="POST" enctype="multipart/form-data">
          @csrf
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="form-group col-md-12">
                <label for="fieldname">Field Name</label>
                <input type="text" class="form-control" id="fieldname" name="fieldname" placeholder="Enter fieldname">
              </div>
              
            </div>
           
        </div>
      
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </div>
  </div>


<script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
})
</script> --}}
 @endsection