/* -------------------------------------------------------------------------- */
/*                                  Utilities                                 */
/* -------------------------------------------------------------------------- */

Route::get('/clear-cache', function () {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    return redirect()->to('/');
});

Route::get('/php-info', function(){
    return phpinfo();
});

/* -------------------------------------------------------------------------- */
/*                                    Route                                   */
/* -------------------------------------------------------------------------- */

Route::match(['GET', 'POST'], '/complain', 'Front\HomeController@complainStepOne')->name('complain');

Route::namespace ('Admin')->prefix('admin')->as('admin.')->group(function () {
    Route::group(['middleware' => 'auth:admin'], function () {
        Route::get('/home', 'AdminController@index')->name('home');

        Route::resources([
            'employees' => 'EmployeeController',
            'notices' => 'NoticeController',
            'projects' => 'ProjectController',
            'album' => 'GalleryController',
        ]);

        Route::resource('/publication', 'PublicationController', ['names' => [
            'index' => 'publication.index',
            'create' => 'publication.create',
            'store' => 'publication.store',
            'edit' => 'publication.edit',
            'update' => 'publication.update',
            'destroy' => 'publication.delete',
        ]]);

    });
});

/* -------------------------------------------------------------------------- */
/*                                    Model                                   */
/* -------------------------------------------------------------------------- */

protected $guarded = [];

public function user()
{
    return $this->belongsTo(User::class);
}

public function job()
{
    return $this->hasMany(Job::class);
}

/* -------------------------------------------------------------------------- */
/*                                 Controller                                 */
/* -------------------------------------------------------------------------- */

/* ---------------------------------- Store --------------------------------- */
public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'name' => 'required',
            'phone' => 'numeric',
            'photo' => 'max:5120|mimes:jpg,png,jpeg',
        ]);

        if ($request->hasFile('photo')) {
            $extension = $request->photo->getClientOriginalExtension();
            $photo = 'user-profile_'.rand(0, 99999).'_'.date("Y-m-d").'.'.$extension;
            $request->photo->move(public_path("media/photos/profile_photos"), $photo);
        } else {
            $photo = NULL;
        }

        User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'photo' => $photo,
        ]);

        return  redirect(route('admin.user.index'))->with('success', 'Account created successfully');
    }

/* --------------------------------- Update --------------------------------- */
public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:150',
            'email' => 'required|max:150|email',
            'phone' => 'required|numeric|digits:11',
            'photo' => 'nullable|mimes:png,jpg,jpeg|max:5120',
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('photo')) {
            $extension = $request->photo->getClientOriginalExtension();
            $photo = 'user-profile_'.rand(0, 99999).'_'.date("Y-m-d").'.'.$extension;
            $request->photo->move(public_path("media/photos/profile_photos"), $photo);
            $user->photo = $photo;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        $user->save();

        return redirect(route('admin.user.index'))->with('success', 'User has been updated.');
    }


/* --------------------------------- Delete --------------------------------- */
public function destroy($id)
    {
        $user = User::findOrFail($id);
        if (File::exists(public_path("media/photos/profile_photos/$user->photo"))) {
            File::delete(public_path("media/photos/profile_photos/$user->photo"));
        }
        User::findOrFail($id)->delete();
        return redirect(route('admin.user.index'))->with('success', 'User has been deleted.');
    }


/* -------------------------- Join Multiple Tables -------------------------- */
public function index()
    {
        DB::table('applications')
            ->select('applications.payment_status', 'jobs.job_subtitle', 'job_companies.job_category')
            ->join('jobs', 'jobs.id', '=', 'applications.job_id')
            ->join('job_companies', 'job_companies.id', '=', 'jobs.company_id')
            ->where(['job_category' => 'govt', 'payment_status' => 'pending'])
            ->count();
        return view('web.backend.admin.sections.dashboard');
    }

/* -------------------------------------------------------------------------- */
/*                               Api Controller                               */
/* -------------------------------------------------------------------------- */

public function expired()
    {

        $expireds = Job::where('last_date', '<', date('Y/m/d ', time()))->orderBy('created_at', 'DESC')->get();

        if (is_null($expireds)) {

            return response()->json([
                'error' => true,
                'additional' => 'Happy Coding',
                'message' => 'No data found',
            ], 401);

        } else {
            return response()->json([
                'error' => false,
                'data' => $expireds,
                'additional' => 'Happy Coding',
                'message' => 'Data retrieved successfully',
            ], 200);
        }

    }



/* -------------------------------------------------------------------------- */
/*                                    View                                    */
/* -------------------------------------------------------------------------- */

/* --------------------------------- Create --------------------------------- */

<form class="js-validation" action="{{ route('admin.disease.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h2 class="content-heading">Rice Disease and Its Managment</h2>
            <div class="block block-rounded">

                <div class="block-header">
                    <h3 class="block-title">Write Title</h3>
                </div>
                <div class="block-content block-content-full ">
                    <div class="form-group">
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="" name="title"
                            placeholder="Write Title" required>
                        @error('title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="block-header">
                    <h3 class="block-title">Add Cover Photo</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="form-group">
                        <input type="file" id="example-file-input" name="cover_photo"
                            class="@error('cover_photo') is-invalid @enderror">
                        @error('cover_photo')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>


                <div class="block-header">
                    <h3 class="block-title">Write Description</h3>
                </div>
                <div class="block-content block-content-full">
                    <!-- Summernote Container -->
                    <textarea type="text" name="description"
                        class="js-summernote  @error('description') is-invalid @enderror " required>

                                            Write your description here..

                                        </textarea>
                    @error('description')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

            </div>

            <div class="row items-push">
                <div class="col-lg-7 ">
                    <button data-toggle="click-ripple" type="submit" class="btn btn-success">Publish</button>
                </div>
            </div>

        </form>

/* ---------------------------------- Edit ---------------------------------- */
<form class="js-validation" action="{{ route('admin.disease.update', $disease->id) }}" method="post"
            enctype="multipart/form-data">
            @csrf
            @method('PATCH')
</form>


/* ---------------------------------- Index --------------------------------- */
@section('content')
    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">Rice Disease and Its Managment
                </h1>
            </div>
        </div>
    </div>
    <!-- END Hero -->



    <!-- Page Content -->
    <div class="content">
        @if ($message = Session::get('success'))


            <div class=" alert alert-success alert-dismissable" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="alert-heading h4 my-2">Success</h3>
                <p class="mb-0">{{ $message }} {{-- <a class="alert-link"
                        href="javascript:void(0)">link</a>! --}}</p>
            </div>

        @endif

        <a draggable="false" href="{{ route('admin.disease.create') }}" class="mb-4 btn btn-info btn-lg btn3d">Add
            Method</a>

        <!-- Dynamic Table Full -->
        <div class="block block-rounded">

            <div class="block-content block-content-full">
                <table
                    class="table table-bordered table-hover  table-vcenter js-dataTable-buttons {{-- table-responsive --}}">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: ;">#</th>
                            <th class="d-none d-sm-table-cell" style="width: ;">Cover Photo</th>
                            <th class="d-none d-sm-table-cell" style="width: ;">Title</th>
                            <th class="d-none d-sm-table-cell" style="width: ;">Description</th>
                            {{-- <th style="width: ;">Created</th>
                            --}}
                            {{-- <th class="d-none d-sm-table-cell" style="width: ;"> Status
                            </th> --}}
                            <th class="text-center" style="width: ;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        @if (@count($diseases) > 0)
                            @foreach ($diseases as $key => $disease)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}.</td>
                                    <td class="d-none d-sm-table-cell">
                                        <img src="{{ asset('media/photos/rice_production_management/disease/' . $disease->cover_photo) }}"
                                            style="height: 70px; width: 100px; border-radius:10px;" alt="">
                                    </td>
                                    <td class="font-w600">
                                        <a
                                            href="{{ route('admin.disease.edit', $disease->id) }}">{{ Str::limit($disease->title, 50, $end = '........') }}</a>
                                    </td>

                                    <td class="d-none d-sm-table-cell">
                                        {{ Str::limit(strip_tags($disease->description), 150, $end = '.........') }}


                                    </td>
                                    {{-- <td>
                                        <em class="text-muted">{{ rand(2, 10) }} days ago</em>
                                    </td> --}}
                                    {{-- <td class="d-none d-sm-table-cell">
                                        <span class="badge badge-success">VIP</span>
                                    </td> --}}




                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.disease.edit', $disease->id) }}" type="button"
                                                class="btn btn-sm btn-light" data-toggle="tooltip" title="Edit Client">
                                                <i class="fa fa-fw fa-pencil-alt"></i>
                                            </a>


                                            <form action="{{ route('admin.disease.delete', $disease->id) }}" method="POST">
                                                <input name="_method" type="hidden" value="DELETE">
                                                {{ csrf_field() }}

                                                <button type="submit" class="btn btn-sm btn-light " data-toggle="tooltip"
                                                    title="Remove Client">
                                                    <i class="fa fa-fw fa-times"></i>
                                                </button>
                                            </form>


                                        </div>
                                    </td>

                                </tr>
                            @endforeach

                        @else
                            <tr>
                                <td colspan="7" class="text-center text-danger"><strong><i class="fas fa-ban"></i>
                                        Not Found &#x1F625;</strong>
                                </td>
                            </tr>
                        @endif


                    </tbody>
                </table>
            </div>
        </div>


    </div>
@endsection


/* -------------------------------------------------------------------------- */
/*                                 Pagination                                 */
/* -------------------------------------------------------------------------- */

<div class="row m-5">
                <div class="col-sm-6 col-sm-offset-5"></div>
                {{ $users->render() }}
</div>


/* -------------------------------------------------------------------------- */
/*                                    other                                   */
/* -------------------------------------------------------------------------- */

<td class="text-center">
    @if (isset($application['job']['company']))
        {{ $application['job']['company']->company_name }}

    @else
        <small class="text-warning">
            << something wrong>>
        </small>
    @endif
</td>


Password:
$2y$10$WZ09gv2u2k0UROI64trKSe.Lc5XnOuU7oaawlM6yXBJC9FipzKS5m
