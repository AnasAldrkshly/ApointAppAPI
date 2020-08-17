<?php

namespace App\Http\Controllers;

use App\experts;
use App\reservations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class ExpertsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $experts = new experts;
        return response()->json(['data' => $experts->all(), 'code' => 200]);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\experts  $experts
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $expert = experts::find($id);
        return response()->json(['data' => $expert, 'code' => 200]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\experts  $experts
     * @return \Illuminate\Http\Response
     */
    public function edit(experts $experts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\experts  $experts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, experts $experts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\experts  $experts
     * @return \Illuminate\Http\Response
     */
    public function destroy(experts $experts)
    {
        //
    }

    public function getTimezonesAll()
    {
        $tzlist =  timezone_identifiers_list();

        return $tzlist;
    }

    public function getOffsetFromTimezone($clietTZ)
    {

        $tz = timezone_open($clietTZ);
        $dateTimeOslo = date_create("now", timezone_open($clietTZ));

        $result =  timezone_offset_get($tz, $dateTimeOslo);

        return $result;
    }

    public function timeTransfere($sentArray, $expOffset, $clientOffset)
    {
        $clientHours = $clientOffset / 3600;

        $finalMargin = (0 - ($expOffset) +  ($clientHours)) * 60;

        $orgFrom =  date_create($sentArray['from']);
        $orgto =  date_create($sentArray['to']);

        $custFrom =  $orgFrom;
        $custTo = $orgto;

        date_add($custFrom, date_interval_create_from_date_string($finalMargin . " minute"));
        date_add($custTo, date_interval_create_from_date_string($finalMargin . " minute"));
        $custFrom = date_format($custFrom, "h:i:s A");
        $custTo = date_format($custTo, "h:i:s A");
        $custlArray = array("from" =>  $custFrom, "to" => $custTo);

        return $custlArray;
    }

    public function getSlices($id, $duration, $dat, $clietTzIndex)
    {

        $tzlist =  timezone_identifiers_list();
        $clietTz =   $tzlist[$clietTzIndex];
        $expert = experts::find($id);
        //$fromGMT = $expert->work_from_gmt; /*Not used Just for testing purposes*/
        //$toGMT = $expert->work_to_gmt;    /*Not used Just for testing purposes*/
        $expOffset = $expert->timezone_offset;
        $slices = collect();
        $v_conti = true;
        $v_count = 0;
        $from_time =  $expert->work_from;
        $to_time =  $expert->work_to;
        $res_date =   date_create($dat);
        $cr_date = date_create($from_time);
        $from_time = date_create($from_time);
        $to_time = date_create($to_time);


        $clientOffset = $this->getOffsetFromTimezone($clietTz);

        while ($v_conti) {

            $tempFrom = date_format($cr_date, "h:i:s A");
            $sendTempFrom = date_format($cr_date, "H:i:s ");
            date_add($cr_date, date_interval_create_from_date_string($duration . " minute"));
            $tempTo = date_format($cr_date, "h:i:s A");
            $sendTempTo = date_format($cr_date, "H:i:s ");
            $myArray = array("from" => $tempFrom, "to" => $tempTo);
            $exclArray = array("from" => $sendTempFrom, "to" => $sendTempTo);


            $execRes = $this->resExclude($id, $exclArray, $res_date);


            $v_count++;

            if (date_format($cr_date, "H:i:s ") > date_format($to_time, "H:i:s ")) {

                $v_conti = false;
            } else {

                if ($execRes == 0) {

                    $OffsetHours = $this->timeTransfere($exclArray, $expOffset, $clientOffset);
                    $slices->push($OffsetHours);
                }
            }
        }

        return $slices;
    }


    public function resExclude($id, $sentArray, $res_date)
    {

        /*------------------------------------------------------*/



        $finalResult = 0;

        $sliceStart =  date_create($sentArray['from']);
        $sliceEnd =  date_create($sentArray['to']);

        $reservation =  DB::Table('reservations')->where([
            ['ex_id', '=', $id],
            ['date', '=', $res_date]
        ])
            ->get()->toArray();


        foreach ($reservation as $reserv) {
            $resStart =   date_create($reserv->start_time);
            $resEnd   =   date_create($reserv->end_time);



            if (
                $sliceStart == $resStart
            ) {

                $finalResult = 1;
            }
            if (
                $sliceEnd == $resEnd
            ) {

                $finalResult = 2;
            }
            if (
                $sliceStart > $resStart && $sliceStart <   $resEnd
            ) {

                $finalResult = 3;
            }
            if (
                $sliceEnd < $resEnd  && $sliceEnd > $resStart
            ) {
                $finalResult = 4;
            }
            if (
                $sliceStart < $resStart &&     $sliceEnd > $resEnd
            ) {

                $finalResult = 5;
            }
        }

        return  $finalResult;

        /*------------------------------------------------------*/
    }

    public function getDefaultTZbyIP(Request $request)
    {
        //  Request::ip();
        $ip = request()->ip(); //Request::ip(); //"162.158.12.0";  //$_SERVER['REMOTE_ADDR']
        $ip = ($ip = "127.0.0.1" ? "162.158.12.0" : $ip);
        $ipInfo = file_get_contents('http://ip-api.com/json/' . $ip);
        $ipInfo = json_decode($ipInfo);
        $timezone = $ipInfo->timezone;

        return $myArray = array("TZ" => $timezone);
    }
}
