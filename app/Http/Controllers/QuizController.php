<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use DB;

class QuizController extends Controller
{
   /** 
    * Show a form of questions
    *
    * @return \Illuminate\Http\Response 
    */
    public function index()
    {
        $questions = Question::inRandomOrder()->get();

        return view('welcome', compact('questions'));
    }

   /** 
    * Store a newly created resource in storage.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response 
    */
    public function processAnswer(Request $request)
    {
        $request->validate([
            'option' => 'required|array',
            'option.*' => 'required|integer|exists:options,id'
        ]);

        $data = [];

        foreach($request->option as $questionId => $optionId) {
            $data[] = [
                'question_id' => $questionId,
                'option_id' => $optionId,
                'created_at' => \Carbon\Carbon::now()
            ];
        }

        DB::beginTransaction();

        try {
            Answer::insert($data);
            DB::commit();

            return redirect('/')->with(['message' => 'Your answer is submitted', 'type' => 'success']);
        }catch(\Exception $ex) {
            DB::rollBack();
            
            return redirect('/')->with(['message' => $ex->getMessage(), 'type' => 'danger']);
        }
    }
}
