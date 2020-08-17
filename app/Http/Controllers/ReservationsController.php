<?php

namespace App\Http\Controllers;

use App\experts;
use App\reservations;
use App\Http\Controllers\ExpertsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationsController extends Controller
{
    protected $ExpertsController;
    public function __construct(ExpertsController $ExpertsController)
    {
        $this->ExpertsController = $ExpertsController;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reserv = new reservations;
        return response()->json(['data' => $reserv->all(), 'code' => 200]);
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
        $clietTzIndex =  $request->input('TZIndex');
        $expOffset = 
        DB::Table('experts')->where('id', $request->input('ex_id'))->get()->all()[0]->timezone_offset;

        $tzlist =  timezone_identifiers_list();
        $clietTz =   $tzlist[$clietTzIndex];

        $clientOffset =    $this->ExpertsController->getOffsetFromTimezone($clietTz);



        $clientHours = $clientOffset / 3600;

        $finalMargin = (0 - ($expOffset) +  ($clientHours)) * 60;

        $finalMargin =  0 -  $finalMargin;

        $custFrom = date_create($request->input('start_time'));
        $custTo =   date_create($request->input('end_time'));
        date_add($custFrom, date_interval_create_from_date_string($finalMargin . " minute"));
        date_add($custTo, date_interval_create_from_date_string($finalMargin . " minute"));

        // return "Successful";
        $reserv = new reservations;
        $reserv->ex_id = $request->input('ex_id');
        $reserv->username = $request->input('username');
        $reserv->date =   date_create($request->input('date'));
        $reserv->duration = $request->input('duration');
        $reserv->start_time =  $custFrom; 
        $reserv->end_time =  $custTo;
        $reserv->save();
        return response()->json(['data' => 'success', 'code' => 200]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reserv = reservations::find($id);
        return response()->json(['data' => $reserv, 'code' => 200]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function edit(reservations $reservations)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $reserv = reservations::find($id);
        if ($reserv) {
            $reserv = new reservations;
            $reserv->ex_id = $request->input('ex_id');
            $reserv->username = $request->input('username');
            $reserv->date = $request->input('date');
            $reserv->duration = $request->input('duration');
            $reserv->start_time = $request->input('start_time');
            $reserv->end_time = $request->input('end_time');
            $reserv->save();
            return response()->json([
                'message' => 'Updated Successfully',
                'code' => 210
            ]);
        } else {
            return response()->json([
                'message' => 'Error not found',
                'code' => 201
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reserv = reservations::find($id);
        $reserv->delete();
        return response()->json(['data' => 'deleted', 'code' => 200]);
    }

    
}
