<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Survey;
use App\Models\Vote;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::where('status', true)->get();
        return response()->json($surveys);
    }

    public function getById($id)
    {
        $survey = Survey::find($id);

        if(!$survey) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }
        $survey->choices;
        foreach ($survey->choices as $key => $value) {
            $survey->choices[$key]->votes;
        }

        return response()->json($survey);
    }

    public function getByName($name)
    {
        $surveys = Survey::where('description', 'ilike', "%$name%")->orderBy('created_at')->get();

        if(!$surveys || sizeof($surveys) == 0) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }

        return response()->json($surveys);
    }

    public function create(Request $request)
    {
        $rules = [
            'description' => 'required',
            'status'    => 'required',
            'code' => 'required',
            'choices' => 'required|array|min:1',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = ["message" => $validator->errors()];
                return response()->json($response);
       }

        $input = $request->only('description', 'status', 'code');

        $user = Auth::user();

        try {
            DB::beginTransaction();

            $survey = new Survey($input);
            $survey->author()->associate($user);
            $survey->save();

            $choices = [];
            foreach ($request->choices as $desc_choice) {
                $choice = new Choice ($desc_choice);
                $choices[] = $choice;
            }

            $survey->choices()->saveMany($choices);
            
            if($survey) {
                DB::commit();
                return response()->json($survey, 200);
            } else {
                DB::rollBack();
                $response = ["message" => "It was not possible to insert the survey in the database"];
                return response()->json($response, 400);
            }
        } catch (Exception $e) {
            DB::rollBack();
            $response = ["message" => "It was not possible to insert the survey in the database", "message_excpt" => $e->getMessage()];
            return response()->json($response, 500);
        }

    }

    public function enable($id)
    {
        $survey = Survey::find($id);

        if(!$survey) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }

        $survey->status = !$survey->status;
        $survey->save();

        return response()->json($survey, 200);
    }

    public function vote(Request $request, $id)
    {
        $survey = Survey::find($id);

        if(!$survey) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }

        $rules = [
            'choice_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response = ["message" => $validator->errors()];
                return response()->json($response);
       }

        $input = $request->only('choice_id');
        $user = Auth::user();

        try {
            $vote = new Vote($input);
            $vote->user()->associate($user);
            $survey->votes()->save($vote);
            return response()->json($vote, 200);
        } catch (Exception $e) {
            $response = ["message" => "It was not possible to vote", "message_excpt" => $e->getMessage()];
            return response()->json($response, 500);
        }
    }
}
