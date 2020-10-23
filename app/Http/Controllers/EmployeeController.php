<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Employee;
use Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Employee::latest()->get())
                    ->addColumn('action', function($data){
                        $button = '<button type="button" name="edit" id="'.$data->id.'" class="edit btn btn-primary btn-sm">Edit</button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button type="button" name="delete" id="'.$data->id.'" class="delete btn btn-danger btn-sm">Delete</button>';
                        return $button;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        $employees = Employee::all();
        return view('emp_index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array(
          'name' => 'required',
          'contact_number' => 'required|max:10|min:10|unique:employees,contact_number',
          'email' => 'required|email|unique:employees,email',
          'image' => 'required|image|max:2048',
          'hobbies' => 'required',
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $image = $request->file('image');

        $new_name = rand() . '.' . $image->getClientOriginalExtension();

        $image->move(public_path('images'), $new_name);

        $form_data = array(
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'image' => $new_name,
            'hobbies' => implode(", ", $request->hobbies),
        );

        $employee = Employee::create($form_data);
        $employee->parents()->attach($request->parent_id,['created_at'=>now(), 'updated_at'=>now()]);
        return response()->json(['success' => 'Data Added successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(request()->ajax())
        {
            $data = Employee::with('parents')->findOrFail($id);
            $employees = Employee::where('id','!=', $id)->get();
            return response()->json(['data' => $data, 'employees' => $employees]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $image_name = $request->hidden_image;
        $image = $request->file('image');
        if($image != '')
        {
            $rules = array(
                'name' => 'required',
                'contact_number' => 'required|max:10|min:10',
                'email' => 'required|email',
                'image' => 'required|image|max:2048',
                'hobbies' => 'required',
            );
            $error = Validator::make($request->all(), $rules);
            if($error->fails())
            {
                return response()->json(['errors' => $error->errors()->all()]);
            }

            $image_name = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $image_name);
        }
        else
        {
            $rules = array(
                'name' => 'required',
                'contact_number' => 'required|max:10|min:10',
                'email' => 'required|email',
                'hobbies' => 'required',
            );

            $error = Validator::make($request->all(), $rules);

            if($error->fails())
            {
                return response()->json(['errors' => $error->errors()->all()]);
            }
        }

        $form_data = array(
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'image' => $image_name,
            'hobbies' => implode(", ", $request->hobbies),
        );
        //$editEmployee = Employee::find($request->hidden_id);
        //$editEmployee->parents()->detach();
        $employee = Employee::whereId($request->hidden_id)->update($form_data);
        //$employee->parents()->attach($request->parent_id, ['created_at'=>now(), 'updated_at'=>now()]);

        return response()->json(['success' => 'Data is successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Employee::findOrFail($id);
        $data->children()->detach();
        $data->delete();
    }
}
