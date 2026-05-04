<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Idea;
use App\Models\Projectrequest;
use App\Models\UniProject;

class ProjectrequestController extends Controller
{
     public function request(Request $request)
    {
        
         $validated = $request->validate([
            'projectid' => 'required|integer|exists:uni_Projects,id',
            'count'     => 'required|integer|min:1|max:3',
            'nameone'   => 'required|string|max:255',
            'oneid'     => 'required|integer',
            'nametwo'   => 'nullable|string|max:255',
            'twoid'     => 'nullable|integer',
            'namethree' => 'nullable|string|max:255',
            'threeid'   => 'nullable|integer',
        ], [
            'projectid.exists' => 'The selected project ID does not exist in our records.',
        ]);

        $check = UniProject::where('id',$request->projectid)->first();
        if($check->taken==1){
             return redirect()->back()->with('faild', ' project already taken');
        }
        $checkone = Projectrequest::where('oneid', $request->oneid)
              ->orWhere('twoid', $request->oneid)
              ->orWhere('threeid', $request->oneid)->first();
        $checktwo = Projectrequest::where('oneid', $request->onetwo)
              ->orWhere('twoid', $request->onetwo)
              ->orWhere('threeid', $request->onetwo)->first();
        $checkthree = Projectrequest::where('onethree', $request->onethree)
              ->orWhere('twoid', $request->onethree)
              ->orWhere('threeid', $request->onethree)->first();
        // if(!empty($checkone)&&$checkone->accepted==1|| !empty($checktwo)&&$checktwo->accepted==1 ||  !empty($checkthree)&&$checkthree->accepted==1)
        // {
        //     return redirect()->back()->with('faild', 'Your already have a project');
        // }
        if(!empty($request->oneid) &&!empty($request->onetwo)&&!empty($request->onethree)){
            if(!empty($checkone)&&$checkone->accepted==1|| !empty($checktwo)&&$checktwo->accepted==1 ||  !empty($checkthree)&&$checkthree->accepted==1)
            {
                return redirect()->back()->with('faild2', 'Your already have a project');
            }
        }elseif(!empty($request->oneid) &&!empty($request->onetwo)){
              if(!empty($checkone)&&$checkone->accepted==1|| !empty($checktwo)&&$checktwo->accepted==1)
            {
                return redirect()->back()->with('faild2', 'Your already have a project');
            }
        }else{
              if(!empty($checkone)&&$checkone->accepted==1)
            {
                return redirect()->back()->with('faild2', 'Your already have a project');
            }
        }
         
        // Create the record
        Projectrequest::create([
            'projectid'        => $validated['projectid'],
            'count'     => $validated['count'],
            'nameone'           => $validated['nameone'],
            'oneid'             => $validated['oneid'],
            'nametwo'           => $validated['nametwo'] ?? null,
            'twoid'             => $validated['twoid'] ?? null,
            'namethree'      => $validated['namethree'] ?? null,
            'threeid'        => $validated['threeid'] ?? null,
        ]);

        // Redirect back with success message (or to a thank you page)
        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

    public function accept()
    {
        return view('student.acceptance',['requests'=> Projectrequest::All()]);
    }

    public function idea(Request $request)
    {
        
         $validated = $request->validate([
            'projectname' => 'required|string',
            'count'     => 'required|integer|min:1|max:3',
            'supname'   => 'required|string',
            'nameone'   => 'required|string|max:255',
            'oneid'     => 'required|integer',
            'nametwo'   => 'nullable|string|max:255',
            'twoid'     => 'nullable|integer',
            'namethree' => 'nullable|string|max:255',
            'threeid'   => 'nullable|integer',
        ]);
        
        $checkone = Projectrequest::where('oneid', $request->oneid)
              ->orWhere('twoid', $request->oneid)
              ->orWhere('threeid', $request->oneid)->first();
        $checktwo = Projectrequest::where('oneid', $request->onetwo)
              ->orWhere('twoid', $request->onetwo)
              ->orWhere('threeid', $request->onetwo)->first();
        $checkthree = Projectrequest::where('onethree', $request->onethree)
              ->orWhere('twoid', $request->onethree)
              ->orWhere('threeid', $request->onethree)->first();
        if(!empty($request->oneid) &&!empty($request->onetwo)&&!empty($request->onethree)){
            if(!empty($checkone)&&$checkone->accepted==1|| !empty($checktwo)&&$checktwo->accepted==1 ||  !empty($checkthree)&&$checkthree->accepted==1)
            {
                return redirect()->back()->with('faild2', 'Your already have a project');
            }
        }elseif(!empty($request->oneid) &&!empty($request->onetwo)){
              if(!empty($checkone)&&$checkone->accepted==1|| !empty($checktwo)&&$checktwo->accepted==1)
            {
                return redirect()->back()->with('faild2', 'Your already have a project');
            }
        }else{
              if(!empty($checkone)&&$checkone->accepted==1)
            {
                return redirect()->back()->with('faild2', 'Your already have a project');
            }
        }

         
        // Create the record
        Idea::create([
            'projectname'        => $validated['projectname'],
            'count'              => $validated['count'],
            'supname'            => $validated['supname'],
            'nameone'           => $validated['nameone'],
            'oneid'             => $validated['oneid'],
            'nametwo'           => $validated['nametwo'] ?? null,
            'twoid'             => $validated['twoid'] ?? null,
            'namethree'      => $validated['namethree'] ?? null,
            'threeid'        => $validated['threeid'] ?? null,
        ]);

        // Redirect back with success message (or to a thank you page)
        return redirect()->back()->with('success', 'Your request has been submitted successfully.');
    }

     public function acceptidea()
    {
        return view('student.acceptanceidea',['requests'=> Idea::All()]);
    }

}
