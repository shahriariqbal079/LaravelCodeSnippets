<?php

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

   