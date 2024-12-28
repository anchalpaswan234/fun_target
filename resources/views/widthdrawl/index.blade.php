@extends('admin.body.adminmaster')

@section('admin')
    <div class="page-wrapper">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <div style="background-color: #f8f9fa; padding: 10px 15px; border-radius: 5px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="ft mb-0 text-muted" style="font-size: 20px; font-weight: bold; margin: 0;">
                        <i class="mdi mdi-apple-keyboard-command title_icon"></i>
                            Students
                        </p>
                    </div>
                </div>
            </nav>

            <form action="" method="" style="padding: 8px; background-color: #fff; border-radius: 10px; max-width: 1100px; margin: 20px auto; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                @csrf
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <label style="display: flex; align-items: center;">
                        Show
                        <select name="basic-datatable_length" aria-controls="basic-datatable" 
                                style="margin: 0 8px; padding: 4px; font-size: 14px;" class="text-muted">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        entries
                    </label>
                    <label style="display: flex; align-items: center;">
                        Search:
                        <input type="search" style="margin-left: 8px; padding: 4px; font-size: 14px;" 
                               class="form-control form-control-sm" placeholder="" aria-controls="basic-datatable">
                    </label>
                </div>

                <div class="table-container text-muted">
                    <table id="zero_config" class="table table-striped table-bordered">
                        <thead class="text-muted">
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>E-mail</th>
                                <th>Phone</th>
                                <th>Enrolled Courses</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $key => $user)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <img src="{{ $user->image ?? 'default_image.png' }}" alt="User Photo" width="50" height="50" style="border-radius: 50%;">
                                </td>
                                <td>{{ $user->f_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->mobile }}</td>
                                <td>{{ $user->enrolled_courses ?? '0' }}</td>

                                <td>
                                    <!-- Status Button -->
                                    <button id="statusButton_{{ $user->id }}" style="padding: 8px 12px; background: linear-gradient(135deg, #6c757d, #495057); color: #fff; border: none; border-radius: 50px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.4); transition: all 0.3s ease;" data-user-id="{{ $user->id }}" data-status="{{ $user->status }}" onclick="toggleStatus(this)">
                                        <i class="fas fa-circle" style="font-size: 16px;"></i>
                                        <span style="font-size: 14px;">
                                            {{ $user->status == 1 ? 'Pending' : ($user->status == 2 ? 'Success' : 'Reject') }}
                                        </span>
                                    </button>
                                </td>

                                @if($user->status == 1)
                                    <!-- Dropdown for Pending Status -->
                                    <td>
                                        <div class="dropdown">
                                            <button class="dropbtn" style="font-size:13px;">Pending</button>
                                            <div class="dropdown-content" style="font-size:12px;">
                                                <a href="{{ route('widthdrawl.success', $user->id) }}">Success</a>
                                                <a data-toggle="modal" data-target="#exampleModalCenter{{$user->id}}" class="reject-button">Reject</a>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="exampleModalCenter{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLongTitle">Reject Withdrawal</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('widthdrawl.reject', ['id' => $user->id]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="container-fluid">
                                                                <div class="row">
                                                                    <div class="form-group col-md-12">
                                                                        <label for="msg">Reason for rejection</label>
                                                                        <textarea class="form-control" id="msg" name="msg" rows="3" placeholder="Enter reason for rejection" required></textarea>
                                                                        @error('msg')
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
                                @elseif($user->status == 2)
                                    <td><button class="btn btn-success">Success</button></td>
                                @elseif($user->status == 3)
                                    <td><button class="btn btn-danger">Reject</button></td>
                                @else
                                    <td>
                                        <!-- You can add another dropdown or button here for other statuses -->
                                    </td>
                                @endif

                                <!-- Status Message -->
                                <td>
                                    @if ($user->status == 3)
                                        {{ $user->rejectmsg }}
                                    @elseif ($user->status == 1)
                                        Pending
                                    @elseif ($user->status == 2)
                                        Success
                                    @endif
                                </td>

                                <!-- Created At -->
                                <td>{{ $user->created_at }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <hr>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Showing 0 to 0 of 0 entries</span>
                    <div class="pagination" style="display: flex; gap: 10px;">
                        <button>Previous</button>
                        <button>Next</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleStatus(button) {
            // Get the user ID and the current status
            let userId = button.getAttribute('data-user-id');
            let currentStatus = parseInt(button.getAttribute('data-status'));

            // Toggle the status between Active (1) and Inactive (2)
            let newStatus = currentStatus === 1 ? 2 : 1;

            // Set the button's new status and appearance
            let iconClass = newStatus === 1 ? 'fas fa-check-circle' : 'fas fa-times-circle';
            let statusText = newStatus === 1 ? 'Active' : 'Inactive';
            let buttonBackground = newStatus === 1 ? 'linear-gradient(135deg, #28a745, #1e7e34)' : 'linear-gradient(135deg, #6c757d, #495057)';

            // Update the button UI immediately
            button.setAttribute('data-status', newStatus);  
            button.style.background = buttonBackground;
            button.children[0].className = iconClass;
            button.children[1].textContent = statusText;

            // Send the new status to the backend using AJAX
            updateStatusInDatabase(userId, newStatus);
        }

        function updateStatusInDatabase(userId, newStatus) {
            $.ajax({
                url: '/update-status',
                method: 'POST',
                data: {
                    user_id: userId,
                    status: newStatus,
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    console.log('Status updated successfully');
                },
                error: function(xhr, status, error) {
                    console.log('Error updating status: ', error);
                }
            });
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection
