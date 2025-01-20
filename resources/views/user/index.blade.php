@extends('admin.body.adminmaster')

@section('admin')

<style>
    .btn-group .btn {
    margin-right: 5px; /* Add spacing between buttons */
}

.btn-group .btn:last-child {
    margin-right: 0; /* Remove right margin from the last button */
}

.btn-group {
    display: flex; /* Flexbox for alignment */
    justify-content: center; /* Center buttons if needed */
    align-items: center;
}

</style>

<div class="container-fluid">
    <div class="row">
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="heading1 margin_0 d-flex">
             <h2>User List</h2>
             
             {{-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter" style="margin-left:750px;">Add User</button> --}}
          </div>
       </div>
       <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
            {{-- <form action="" method="post">
              <input type="hidden" name="_token" value="mxanMQCY0Peqj7fCBeqZaqDaJnZTo1EZgtRhJekH" autocomplete="off">
              <div class="card-body row">
                  
                  <div class="col-md-2">
                      <div class="form-group">
                          <label> From Date:</label> 
                          
                          <input type="date" class="form-control" name="fdate" value="2023-07-12"> 
                          <span class="help-block"></span>
                      </div>
                  </div>
                  <div class="col-md-2">
                      <div class="form-group">
                          <label> To Date:</label> 
                          <input type="date" class="form-control" name="tdate" value="2024-01-11"> 
                          <span class="help-block"></span>
                      </div>
                  </div>
              
                  <div class="col-md-2" style="margin-top: 27px;">
                  <button class="btn btn-success" type="submit">Apply Filter</button>
                  </div>
                    
              </div>
          </form> --}}
             <table id="example" class="table table-striped" style="width:100%">
                <thead class="thead-dark">
                   <tr>
                      <th>Id</th>
					   <th>user_id</th>
                      <th>User_name</th>
                      <th>Email</th>
                      <th>Mobile</th>
					  <th>Sponser</th>
                      <th>Wallet</th>
                      <th>Winning_Wallet</th>
                      <th>Commission</th>
                      
					   
					   
                      <th>Bonus</th>
                      <th>Turn Over</th>
                      <th>Today TurnOver</th>
					   <th>Password</th>
                      <th>Date</th>
                      <th>Status</th>
                      <th>Action</th>
                   </tr>
                </thead>
                <tbody>
                  @foreach ($users as $item )
                   <tr>
                      <td>{{$item->id}}</td>
                      <td>{{$item->u_id}}</td>
                      <td>{{$item->username}}</td>
                      <td>{{$item->email}}</td>
                      <td>{{$item->mobile}}</td>
					   <td>{{$item->sname}}</td>
         <!--             <td>{{$item->wallet}}-->
         <!--             <div>-->
					    <!--<div class="btn btn-info btn-sm"> <i class="fa fa-plus" data-toggle="modal" data-target="#exampleModalCenter{{$item->id}}" style="font-size:20px"></i></div>-->
					    <!--<div class="btn btn-danger btn-sm"> <i class="fa fa-minus" data-toggle="modal" data-target="#subtractWalletModal{{$item->id}}" style="font-size:20px"></i></div>-->
					    <!--</div>-->
					    <td>{{$item->wallet}}
    <div class="btn-group" role="group" aria-label="Wallet actions">
        <!-- Add Funds Button -->
        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#exampleModalCenter{{$item->id}}" title="Add Funds">
            <i class="fa fa-plus" style="font-size:20px"></i>
        </button>
        
        <!-- Subtract Funds Button -->
        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#subtractWalletModal{{$item->id}}" title="Subtract Funds">
            <i class="fa fa-minus" style="font-size:20px"></i>
        </button>
    </div>
						  
						   <div class="modal fade" id="exampleModalCenter{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Add Wallet</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{route('wallet.store',$item->id)}}" method="post" enctype="multipart/form-data">
          @csrf
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="form-group col-md-6">
                <label for="wallet">Wallet Amount</label>
                <input type="text" class="form-control" id="wallet" name="wallet" value="" placeholder="Enter Amount">
                @error('wallet')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
              </div>
              
              
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
	</div>
	
							  
						  <!-- Subtract Wallet Modal -->
                    <div class="modal fade" id="subtractWalletModal{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="subtractWalletModalTitle" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="subtractWalletModalTitle">Subtract Wallet</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <form action="{{route('wallet.subtract', $item->id)}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                              <div class="container-fluid">
                                <div class="row">
                                  <div class="form-group col-md-12">
                                    <label for="wallet">Wallet Amount</label>
                                    <input type="text" class="form-control" id="wallet" name="wallet" value="" placeholder="Enter Amount">
                                    @error('wallet')
                                      <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                  </div>
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
                    </div>

					   </td>
                      <td>{{$item->winning_wallet}}</td>
                      <td>{{$item->commission}}</td>
					   
                      <td>{{$item->bonus}}</td>
                      <td>{{$item->turnover}}</td>
                      <td>{{$item->today_turnover}}</td>
                      <td class="d-flex">{{$item->password}}
					   <i class="fa fa-edit mt-1 ml-3" data-toggle="modal" data-target="#exampleModalCenterupdate1{{$item->id}}" style="font-size:20px"></i>
					   </td>
                      <td>{{$item->created_at}}</td>
                      @if($item->status==1)  
                      <td><a href="{{route('user.inactive',$item->id)}}" title="click me for order Disable"><i class="fa fa-check-square-o green_color" aria-hidden="true" style="font-size:30px"></i></a></td>
                     @elseif($item->status==0)
                     <td><a href="{{route('user.active',$item->id)}}" title="click me for order Enable"><i class="fa fa-ban red_color" aria-hidden="true" style="font-size:30px"></i></a>
						 
						 </td>
                      @else
                      <td> </td>
                      @endif
                      <td class="d-flex">
						  
                         
                        <a href="{{route('userdetail',$item->id)}}" class=""><i class="fa fa-eye mt-1 ml-2" style="font-size:30px"></i></a>
						  <!--<a href="{{route('user.mlm',$item->id)}}" title=""><i class="fa fa-users red_color" aria-hidden="true" style="font-size:30px"></i></a>-->
                      </td>
				
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
	
	

	
	
	
	

  <!-- DataTables JS -->
  
 

  

 <script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
})
</script> 

 @endsection

